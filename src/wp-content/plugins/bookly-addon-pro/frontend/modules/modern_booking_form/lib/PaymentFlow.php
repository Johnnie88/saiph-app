<?php
namespace BooklyPro\Frontend\Modules\ModernBookingForm\Lib;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Config;
use Bookly\Lib\Entities\Payment;
use Bookly\Frontend\Modules\Booking;
use Bookly\Frontend\Modules\ModernBookingForm\Proxy as ModernBookingFormProxy;
use BooklyPro\Lib\Entities\GiftCard;

class PaymentFlow extends BooklyLib\Base\Component
{
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /** @var array */
    protected static $support_gateways = array(
        Payment::TYPE_2CHECKOUT,
        Payment::TYPE_CLOUD_SQUARE,
        Payment::TYPE_CLOUD_STRIPE,
        Payment::TYPE_LOCAL,
        Payment::TYPE_MOLLIE,
        Payment::TYPE_PAYPAL,
        Payment::TYPE_PAYSON,
        Payment::TYPE_PAYUBIZ,
        Payment::TYPE_PAYULATAM,
    );

    /**
     * Get data for step done
     *
     * @param string $status
     * @param string $bookly_order
     * @return array
     */
    public static function getBookingResultFromOrder( $status, $bookly_order )
    {
        $data = compact( 'bookly_order' );
        if ( $bookly_order ) {
            switch ( $status ) {
                case self::STATUS_PROCESSING:
                case self::STATUS_COMPLETED:
                $records = BooklyLib\Entities\Appointment::query( 'a' )
                    ->select( 'a.*, s.id AS service_id, MIN(a.start_date) AS start_date, MAX(a.end_date) AS end_date, s.final_step_url' )
                    ->leftJoin( 'CustomerAppointment', 'ca', 'a.id = ca.appointment_id' )
                    ->leftJoin( 'Service', 's', 's.id = COALESCE(ca.compound_service_id, ca.collaborative_service_id, a.service_id)' )
                    ->leftJoin( 'Order', 'o', 'o.id = ca.order_id', '\Bookly\Lib\Entities' )
                    ->where( 'o.token', $bookly_order )
                    ->groupBy( 'COALESCE(ca.compound_token, ca.collaborative_token, ca.id)' )
                    ->fetchArray();
                $final_step_url = get_option( 'bookly_url_final_step_url', '' );
                if ( $records ) {
                    $service_final_step_url = $records[0]['final_step_url'];
                    foreach ( $records as $record ) {
                        if ( $record['final_step_url'] !== $service_final_step_url ) {
                            $service_final_step_url = '';
                            break;
                        }
                    }
                    if ( $service_final_step_url !== '' ) {
                        $final_step_url = $service_final_step_url;
                    }

                    if ( $final_step_url === '' ) {
                        if ( count( $records ) === 1 ) {
                            $appointment = new BooklyLib\Entities\Appointment( $records[0] );
                            $data['qr'] = self::getQr( $appointment );
                        }
                        $data['appointments'] = true;
                    }
                }
                if ( $final_step_url !== '' ) {
                    $data['final_step_url'] = $final_step_url;
                }
                $gift_cards = GiftCard::query( 'gc' )
                    ->leftJoin( 'Order', 'o', 'o.id = gc.order_id', '\Bookly\Lib\Entities' )
                    ->where( 'o.token', $bookly_order )
                    ->fetchCol( 'code' );
                if ( $gift_cards ) {
                    $data['gift_cards'] = $gift_cards;
                }
                $data = ModernBookingFormProxy\Shared::prepareBookingResults( $data, $bookly_order );
            }
        }

        return $data;
    }

    /**
     * @param BooklyLib\Entities\Appointment $appointment
     * @return string
     */
    private static function getQr( $appointment )
    {
        $service = BooklyLib\Entities\Service::find( $appointment->getServiceId() );
        $ics = new BooklyLib\Utils\Ics\Feed();
        $description = BooklyLib\Utils\Codes::replace( BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_ics_customer_template' ), BooklyLib\Utils\Codes::getAppointmentCodes( $appointment ), false );
        $ics->addEvent( $appointment->getStartDate(), $appointment->getEndDate(), $service->getTranslatedTitle(), $description, self::parameter( 'location_id' ) );

        return add_query_arg(
            array(
                'cht' => 'qr',
                'chs' => '298x298',
                'chl' => urlencode( $ics->render() ),
            ),
            'https://chart.googleapis.com/chart'
        );
    }

    /**
     * @param BooklyLib\UserBookingData $userData
     * @return array
     */
    public static function getAllowedGateways( $userData )
    {
        if ( $userData === null ) {
            return self::getSupportedGateways();
        }
        $gateways = array();
        foreach ( self::getSupportedGateways() as $gateway ) {
            if ( Booking\Proxy\CustomerGroups::allowedGateway( $gateway, $userData ) !== false ) {
                switch ( $gateway ) {
                    case Payment::TYPE_LOCAL:
                    case Payment::TYPE_PAYPAL:
                        $gateways[ $gateway ] = true;
                        break;
                    default:
                        if ( get_option( 'bookly_' . $gateway . '_enabled' ) ) {
                            $product = Payment::typeToProduct( $gateway );
                            if ( $product ) {
                                if ( BooklyLib\Cloud\API::getInstance()->account->productActive( $product ) ) {
                                    $gateways[ $gateway ] = true;
                                }
                            } else {
                                $gateways[ $gateway ] = true;
                            }
                        }
                }
            }
        }

        return array_keys( Booking\Proxy\Pro::filterGateways( $gateways, $userData ) );
    }

    /**
     * @return array
     */
    public static function getSupportedGateways()
    {
        $gateways = array();
        if ( BooklyLib\Config::payLocallyEnabled() ) {
            $gateways[] = Payment::TYPE_LOCAL;
        }
        $products = array(
            BooklyLib\Cloud\Account::PRODUCT_STRIPE => Payment::TYPE_CLOUD_STRIPE,
            BooklyLib\Cloud\Account::PRODUCT_SQUARE => Payment::TYPE_CLOUD_SQUARE,
        );
        foreach ( $products as $product => $gateway ) {
            $pay_cloud_gateway = BooklyLib\Cloud\API::getInstance()->account->productActive( $product ) && get_option( 'bookly_' . $gateway . '_enabled' );
            if ( $pay_cloud_gateway ) {
                $gateways[] = $gateway;
            }
        }

        switch ( get_option( 'bookly_paypal_enabled' ) ) {
            case \BooklyPro\Lib\Payment\PayPalGateway::TYPE_PAYMENTS_STANDARD:
                if ( Config::paypalPaymentsStandardActive() ) {
                    $gateways[] = Payment::TYPE_PAYPAL;
                }
                break;
            case \BooklyPro\Lib\Payment\PayPalGateway::TYPE_CHECKOUT:
                if ( Config::paypalCheckoutActive() ) {
                    $gateways[] = Payment::TYPE_PAYPAL;
                }
                break;
            case \BooklyPro\Lib\Payment\PayPalGateway::TYPE_EXPRESS_CHECKOUT:
                $gateways[] = Payment::TYPE_PAYPAL;
                break;
        }

        $embedded = array(
            Payment::TYPE_LOCAL,
            Payment::TYPE_CLOUD_STRIPE,
            Payment::TYPE_CLOUD_SQUARE,
            Payment::TYPE_PAYPAL,
        );

        foreach ( self::$support_gateways as $gateway ) {
            if ( ! in_array( $gateway, $embedded ) && get_option( 'bookly_' . $gateway . '_enabled' ) ) {
                $allowed = false;
                switch ( $gateway ) {
                    case Payment::TYPE_STRIPE:
                        $allowed = Config::stripeActive();
                        break;
                    case Payment::TYPE_2CHECKOUT:
                        $allowed = Config::twoCheckoutActive();
                        break;
                    case Payment::TYPE_PAYUBIZ:
                        $allowed = Config::payuBizActive();
                        break;
                    case Payment::TYPE_PAYULATAM:
                        $allowed = Config::payuLatamActive();
                        break;
                    case Payment::TYPE_PAYSON:
                        $allowed = Config::paysonActive();
                        break;
                    case Payment::TYPE_MOLLIE:
                        $allowed = Config::mollieActive();
                        break;
                }
                if ( $allowed ) {
                    $gateways[] = $gateway;
                }
            }
        }

        return $gateways;
    }

    /**
     * @param array $gateways
     * @return array
     */
    public static function orderGateways( array $gateways )
    {
        $order = BooklyLib\Config::getGatewaysPreference();
        $ordered = array();
        if ( $order ) {
            foreach ( $order as $payment_system ) {
                if ( in_array( $payment_system, $gateways ) ) {
                    $ordered[] = $payment_system;
                }
            }
        }
        foreach ( $gateways as $payment_system ) {
            if ( ! in_array( $payment_system, $ordered ) ) {
                $ordered[] = $payment_system;
            }
        }

        $list = array();
        foreach ( $ordered as $gateway ) {
            if ( self::isSupported( $gateway ) ) {
                $list[] = $gateway;
            }
        }

        return $list;
    }

    /**
     * @param string $gateway
     * @return bool
     */
    protected static function isSupported( $gateway )
    {
        return in_array( $gateway, self::$support_gateways );
    }
}