<?php
namespace BooklyPro\Frontend\Modules\ModernBookingForm\Lib;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Payment;
use Bookly\Frontend\Modules\Booking\Proxy as BookingProxy;
use Bookly\Frontend\Modules\ModernBookingForm\Proxy;
use BooklyPro\Backend\Modules\Appearance;
use BooklyPro\Frontend\Modules\WooCommerce;

class Request extends BooklyLib\Base\Component
{
    const BOOKING_STATUS_COMPLETED = 'completed';
    const BOOKING_STATUS_GROUP_SKIP_PAYMENT = 'group_skip_payment';
    const BOOKING_STATUS_PAYMENT_IMPOSSIBLE = 'payment_impossible';
    const BOOKING_STATUS_APPOINTMENTS_LIMIT_REACHED = 'appointments_limit_reached';

    /** @var array */
    protected $customer = array();
    /** @var array */
    protected $custom_fields = array();
    /** @var string */
    protected $form_id;
    /** @var array */
    protected $notices = array();
    /** @var string */
    protected $step = 'details';
    /** @var string self::BOOKING_STATUS_* */
    protected $booking_status;
    /** @var array */
    protected $data = array();
    /** @var BooklyLib\UserBookingData */
    protected $userData;
    /** @var BooklyLib\CartInfo */
    protected $cart_info;
    /** @var string */
    protected $type; // appointment, package, gift_card
    /** @var Payment */
    protected $payment;
    /** @var string */
    protected $gateway;

    public function __construct()
    {
        $this->customer = self::parameter( 'customer' );
        $this->form_id = self::parameter( 'form_id' );
        $custom_fields = array();
        foreach ( self::parameter( 'cart' ) as $index => $cart_item ) {
            if ( isset( $cart_item['custom_fields'] ) && $cart_item['custom_fields'] ) {
                $custom_fields[ $index ] = array_map( function ( $id, $value ) {
                    return compact( 'id', 'value' );
                }, array_keys( $cart_item['custom_fields'] ), $cart_item['custom_fields'] );
            }
        }
        $this->custom_fields = $custom_fields;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $this->notices = array();

        $appearance = $this->getSettings();
        $show = $appearance->get( 'details_fields_show' );
        if ( $appearance->get( 'email_required' ) && in_array( 'email', $show ) && ( $this->customer['email'] === '' || ! is_email( $this->customer['email'] ) ) ) {
            $this->notices['email'] = 'required';
        }

        foreach ( array( 'phone', 'first_name', 'last_name', 'full_name' ) as $field ) {
            if ( $appearance->get( $field . '_required' ) && in_array( $field, $show ) && $this->customer[ $field ] === '' ) {
                $this->notices[ $field ] = 'required';
            }
        }

        if ( $appearance->get( 'address_required' ) && in_array( 'address', $show ) ) {
            $address = $appearance->get( 'address' );
            if ( isset( $address['show'] ) ) {
                foreach ( $address['show'] as $address_field ) {
                    if ( ! array_key_exists( $address_field, $this->customer ) || $this->customer[ $address_field ] == '' ) {
                        $this->notices[ $address_field ] = 'required';
                    }
                }
            }
        }

        Proxy\Shared::validate( $this );

        if ( $this->notices ) {
            return false;
        }

        if ( ( $failed_key = $this->getUserData()->cart->getFailedKey() ) !== null ) {
            $this->step = BooklyLib\Config::cartActive() ? 'cart' : 'slots';
            $this->notices['slots'] = 'slot_not_available';
            $this->data = array(
                'failed_key' => $failed_key,
            );

            return false;
        }

        if ( $this->getGateway() == '' ) {
            if ( $this->isReachedAppointmentsLimit() ) {
                $this->step = 'done';
                $this->setBookingStatus( self::BOOKING_STATUS_APPOINTMENTS_LIMIT_REACHED );

                return false;
            }

            if ( BookingProxy\CustomerGroups::getSkipPayment( $this->getUserData()->getCustomer() ) ) {
                $this->step = 'done';
                $this->setBookingStatus( self::BOOKING_STATUS_GROUP_SKIP_PAYMENT );

                return true;
            }

            if ( BooklyLib\Config::paymentStepDisabled() || BooklyLib\Config::wooCommerceEnabled() ) {
                $this->step = 'done';
                $this->setBookingStatus( self::BOOKING_STATUS_COMPLETED );

                return true;
            }

            if ( $this->getPayNow() > 0 ) {
                $gateways = PaymentFlow::getAllowedGateways( $this->getUserData() );
                if ( $this->hasGiftCard() && ( $key = array_search( Payment::TYPE_LOCAL, $gateways, true ) ) !== false ) {
                    // Remove local payment for gift cards
                    unset( $gateways[ $key ] );
                }
                if ( $gateways ) {
                    $payment_gateways = array();
                    foreach ( PaymentFlow::orderGateways( $gateways ) as $type ) {
                        $payment_gateways[] = array( 'type' => $type, 'image' => Payment::typeToImage( $type ), 'discount' => -(float) get_option( 'bookly_' . $type . '_increase' ), 'deduction' => -(float) get_option( 'bookly_' . $type . '_addition' ) );
                    }
                    if ( count( $payment_gateways ) === 1 && $payment_gateways[0]['type'] === Payment::TYPE_LOCAL && ! BooklyLib\Config::couponsActive() ) {
                        $this->setGateway( Payment::TYPE_LOCAL );

                        return true;
                    }
                    $this->step = 'payment';
                    $this->data = compact( 'payment_gateways' );
                } else {
                    $this->step = 'done';
                    $this->setBookingStatus( self::BOOKING_STATUS_PAYMENT_IMPOSSIBLE );
                }

                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getError()
    {
        $result = array(
            'step' => $this->step,
            'data' => $this->data,
        );
        if ( $this->notices ) {
            $result['data']['notices'] = $this->notices;
        }
        if ( $this->booking_status ) {
            $result['status'] = $this->booking_status;
        }

        return $result;
    }

    /**
     * @param array $notice
     * @return void
     */
    public function addNotice( $notice )
    {
        $this->notices = array_merge( $this->notices, $notice );
    }

    /**
     * Get staff id
     *
     * @return int
     */
    public function getStaffId()
    {
        return self::parameter( 'staff_id' );
    }

    /**
     * Get location id
     *
     * @return int
     */
    public function getLocationId()
    {
        return self::parameter( 'location_id' );
    }

    /**
     * Get service id
     *
     * @return int
     */
    public function getServiceId()
    {
        return self::parameter( 'service_id' );
    }

    /**
     * Get customer data
     *
     * @return array
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Get custom fields
     *
     * @return array
     */
    public function getCustomFields()
    {
        return $this->custom_fields;
    }

    /**
     * Get form ID
     *
     * @return array
     */
    public function getFormId()
    {
        return $this->form_id;
    }

    /**
     * Process payment
     *
     * @return array
     * @throws \Exception
     */
    public function processPayment()
    {
        if ( BooklyLib\Config::wooCommerceEnabled() && ( get_option( 'bookly_wc_create_order_at_zero_cost' ) || $this->getCartInfo()->getTotal() > 0 ) ) {
            if ( ! BookingProxy\CustomerGroups::getSkipPayment( $this->getUserData()->getCustomer() ) ) {
                return $this->addToWooCommerceCart();
            }
        }

        try {
            $request = \Bookly\Frontend\Modules\Payment\Request::getInstance();
            $request->setGatewayName( $this->getGateway() );

            return $request->getGateway()->createCheckout();

        } catch ( \Exception $e ) {
            $this->step = 'payment';
            $this->data = array(
                'error' => $e->getMessage(),
            );
            throw $e;
        }
    }

    /**
     * @return float|int
     */
    public function getPayNow()
    {
//        if ( $this->isGiftCard() ) {
//            $price = 0;
//            foreach ( $this->gift_cards as $gift_card ) {
//                $price += $gift_card->getAmount();
//            }
//
//            return BooklyLib\Utils\Price::gatewayPrice( $price, $this->getGateway() );
//        }

        return $this->getCartInfo()->getPayNow();
    }

    /**
     * @return bool
     */
    public function hasGiftCard()
    {
        foreach ( self::parameter( 'cart' ) as $cart_item ) {
            if ( $cart_item['type'] === 'gift_card' ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return BooklyLib\UserBookingData
     */
    public function getUserData()
    {
        if ( $this->userData === null ) {
            $this->userData = new BooklyLib\UserBookingData( null );
            $coupon = self::parameter( 'coupon' );
            $gift_card = self::parameter( 'gift_card' );
            $customer = self::parameter( 'customer', array() );

            $this->userData
                ->setTimeZone( isset( $customer['time_zone'] ) ? $customer['time_zone'] : null )
                ->setTimeZoneOffset( isset( $customer['time_zone_offset'] ) ? $customer['time_zone_offset'] : null )
                ->setCouponCode( $coupon )
                ->setGiftCode( $gift_card )
                ->setFullAddress( isset( $customer['full_address'] ) && $customer['full_address'] !== '' ? $customer['full_address'] : null )
                ->setModernFormCustomer( $customer );

            $client_fields = array();

            if ( $this->getSettings()->get( 'show_address' ) ) {
                $address = $this->getSettings()->get( 'address' );
                if ( isset( $address['show'] ) ) {
                    $client_fields = array_merge( $client_fields, $address['show'] );
                }
            }
            if ( $this->getSettings()->get( 'show_notes' ) ) {
                $client_fields[] = 'notes';
            }
            foreach ( $client_fields as $field ) {
                if ( array_key_exists( $field, $this->customer ) ) {
                    $this->userData->fillData( array( $field => $this->customer[ $field ] ) );
                }
            }
            $slots = array();
            foreach ( self::parameter( 'cart' ) as $index => $item ) {
                $service_id = $item['service_id'];
                $staff_id = $item['staff_id'];
                $location_id = $item['location_id'];
                $nop = isset( $item['nop'] ) ? $item['nop'] : 1;
                $units = isset( $item['units'] ) ? $item['units'] : 1;
                $extras = isset( $item['extras'] ) ? $item['extras'] : array();
                $custom_fields = isset( $item['custom_fields'] ) ? $item['custom_fields'] : array();
                $custom_fields = array_map( function ( $id, $value ) {
                    return compact( 'id', 'value' );
                }, array_keys( $custom_fields ), $custom_fields );
                foreach ( array_keys( $extras, 0, false ) as $key ) {
                    unset( $extras[ $key ] );
                }
                $cart_item = new BooklyLib\CartItem();
                if ( $item['type'] === 'gift_card' ) {
                    $cart_item
                        ->setCartTypeId( $item['gift_card_type'] );
                } else {
                    $slot = $item['type'] === 'appointment'
                        ? $item['slot']['slot']
                        : array( array( $service_id, $staff_id, null, $location_id ?: null ) );
                    $slots[] = $slot;
                    $cart_item
                        ->setStaffIds( array( $staff_id ) )
                        ->setServiceId( $service_id )
                        ->setNumberOfPersons( $nop )
                        ->setLocationId( $location_id )
                        ->setUnits( $units )
                        ->setExtras( $extras )
                        ->setCustomFields( $custom_fields )
                        ->setSlots( $slot );
                }
                $cart_item->setType( $item['type'] );
                $this->userData->cart->add( $cart_item );
            }
            $this->userData->setSlots( $slots );
        }

        return $this->userData;
    }

    /**
     * @return BooklyLib\Utils\Collection
     */
    public function getSettings()
    {
        static $settings;
        if ( $settings === null ) {
            $settings = new BooklyLib\Utils\Collection( Appearance\ProxyProviders\Local::getAppearance( self::parameter( 'form_type' ), self::parameter( 'form_slug' ) ) );
        }

        return $settings;
    }

    /**
     * @return BooklyLib\CartInfo
     */
    protected function getCartInfo()
    {
        if ( $this->cart_info === null ) {
            $this->cart_info = $this->getUserData()->cart->getInfo( $this->getGateway() );
        }

        return $this->cart_info;
    }

    /**
     * Get payment system
     *
     * @return string
     */
    protected function getGateway()
    {
        return $this->gateway ?: self::parameter( 'gateway' );
    }

    /**
     * @param string $gateway
     * @return void
     */
    protected function setGateway( $gateway )
    {
        $this->gateway = $gateway;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function addToWooCommerceCart()
    {
        $session = WC()->session;
        /** @var \WC_Session_Handler $session */
        if ( $session instanceof \WC_Session_Handler && $session->get_session_cookie() === false ) {
            $session->set_customer_session_cookie( true );
        }
        $userData = $this->getUserData();
        $failed_key = WooCommerce\Controller::addToCart( $userData );
        if ( $failed_key === null ) {
            $status = PaymentFlow::STATUS_COMPLETED;
            $data = array( 'target_url' => wc_get_cart_url() );
        } else {
            $this->step = BooklyLib\Config::cartActive() ? 'cart' : 'slots';
            $this->notices['slots'] = 'slot_not_available';
            $this->data = compact( 'failed_key' );
            throw new \Exception();
        }

        return compact( 'status', 'data' );
    }

    /**
     * Set booking status
     *
     * @param string $status
     * @return void
     */
    protected function setBookingStatus( $status )
    {
        $this->booking_status = $status;
    }

    /**
     * Check if the client has reached the appointments limit
     *
     * @return bool
     */
    protected function isReachedAppointmentsLimit()
    {
        $data = array();
        foreach ( $this->getUserData()->cart->getItems() as $cart_item ) {
            if ( $cart_item->getType() === 'appointment' ) {
                if ( $cart_item->toBePutOnWaitingList() ) {
                    // Skip waiting list items.
                    continue;
                }
                $service = $cart_item->getService();
                if ( $service->getLimitPeriod() != 'off' ) {
                    $slots = $cart_item->getSlots();
                    $data[ $service->getId() ]['service'] = $service;
                    $data[ $service->getId() ]['dates'][] = $slots[0][2];
                }
            }
        }
        if ( $data ) {
            $customer = $this->getUserData()->getCustomer();
            foreach ( $data as $service_data ) {
                if ( $service_data['service']->appointmentsLimitReached( $customer->getId(), $service_data['dates'] ) ) {
                    return true;
                }
            }
        }

        return false;
    }
}