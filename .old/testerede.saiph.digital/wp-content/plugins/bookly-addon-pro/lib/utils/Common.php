<?php
namespace BooklyPro\Lib\Utils;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;
use BooklyPro\Lib\Entities\GiftCard;
use BooklyPro\Lib\Entities\GiftCardType;

abstract class Common
{
    /**
     * WPML translation
     *
     * @param array $appointments
     * @return array
     */
    public static function translateAppointments( array $appointments )
    {
        $postfix_any = sprintf( ' (%s)', BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_option_employee' ) );
        foreach ( $appointments as &$appointment ) {
            $category = new BooklyLib\Entities\Category( array( 'id' => $appointment['category_id'], 'name' => $appointment['category'] ) );
            $service = new BooklyLib\Entities\Service( array( 'id' => $appointment['service_id'], 'title' => $appointment['service'] ) );
            $staff = new BooklyLib\Entities\Staff( array( 'id' => $appointment['staff_id'], 'full_name' => $appointment['staff'] ) );
            $appointment['category'] = $category->getTranslatedName();
            $appointment['service'] = $service->getTranslatedTitle();
            $appointment['staff'] = $staff->getTranslatedName() . ( $appointment['staff_any'] ? $postfix_any : '' );
            // Prepare extras.
            $appointment['extras'] = (array) BooklyLib\Proxy\ServiceExtras::getCAInfo( json_decode( $appointment['ca_id'], true ), true );
        }

        return $appointments;
    }

    /**
     * @return array
     */
    public static function getAddressFields()
    {
        return array(
            'country' => get_option( 'bookly_l10n_label_country' ),
            'state' => get_option( 'bookly_l10n_label_state' ),
            'postcode' => get_option( 'bookly_l10n_label_postcode' ),
            'city' => get_option( 'bookly_l10n_label_city' ),
            'street' => get_option( 'bookly_l10n_label_street' ),
            'street_number' => get_option( 'bookly_l10n_label_street_number' ),
            'additional_address' => get_option( 'bookly_l10n_label_additional_address' ),
        );
    }

    /**
     * @return array
     */
    public static function getDisplayedAddressFields()
    {
        $fields = array();
        $address_show_fields = (array) get_option( 'bookly_cst_address_show_fields', array() );
        $address_fields = self::getAddressFields();

        foreach ( $address_show_fields as $field => $attributes ) {
            if ( array_key_exists( $field, $address_fields ) && array_key_exists( 'show', $attributes ) && $attributes['show'] ) {
                $fields[ $field ] = true;
            }
        }

        return $fields;
    }

    /**
     * @param array $data
     * @return string
     */
    public static function getFullAddressByCustomerData( array $data )
    {
        $fields = array();
        $address_empty = true;
        foreach ( self::getDisplayedAddressFields() as $field_name => $attributes ) {
            if ( array_key_exists( $field_name, $data ) ) {
                $fields[ $field_name ] = $data[ $field_name ];
                if ( $data[ $field_name ] != '' ) {
                    $address_empty = false;
                }
            } else {
                $fields[ $field_name ] = null;
            }
        }

        return $address_empty
            ? ( isset( $data['full_address'] ) && $data['full_address'] ? $data['full_address'] : '' )
            : BooklyLib\Utils\Codes::replace( BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_cst_address_template' ), $fields, false );
    }

    /**
     * Create day options.
     *
     * @return array
     */
    public static function dayOptions()
    {
        return array_combine( range( 1, 31 ), range( 1, 31 ) );
    }

    /**
     * Create month options.
     *
     * @return array
     */
    public static function monthOptions()
    {
        global $wp_locale;

        return array_combine( range( 1, 12 ), $wp_locale->month );
    }

    /**
     * Create year options.
     *
     * @param int $delta_from
     * @param int $delta_to
     *
     * @return array
     */
    public static function yearOptions( $delta_from = 0, $delta_to = -100 )
    {
        $year = (int) BooklyLib\Slots\DatePoint::now()->format( 'Y' );
        $range = range( $year + $delta_from, $year + $delta_to );

        return array_combine( $range, $range );
    }

    /**
     * Create WordPress user
     *
     * @param array $params expected ['first_name', 'last_name', 'full_name', 'email' ]
     * @param string $password
     * @param string $alt_base
     * @return \WP_User
     * @throws BooklyLib\Base\ValidationException
     */
    public static function createWPUser( array $params, &$password, $alt_base = 'client' )
    {
        if ( ! isset( $params['email'] ) || trim( $params['email'] ) === '' ) {
            throw new BooklyLib\Base\ValidationException( __( 'Email required', 'bookly' ), 'email' );
        }
        $use_full_name = ! ( isset( $params['first_name'], $params['last_name'] ) && trim( $params['first_name'] . $params['last_name'] ) !== '' );
        if ( $use_full_name ) {
            $base = sanitize_user( isset( $params['full_name'] ) ? $params['full_name'] : '', true );
        } else {
            $base = sanitize_user( sprintf( '%s %s', $params['first_name'], $params['last_name'] ), true );
        }
        $base = $base !== '' ? $base : $alt_base;
        $username = $base;
        $i = 1;
        while ( username_exists( $username ) ) {
            $username = $base . $i;
            ++$i;
        }
        // Generate password.
        $password = wp_generate_password( 6, true );
        // Create WordPress user.
        $wp_user_id = wp_create_user( $username, $password, $params['email'] );
        if ( is_wp_error( $wp_user_id ) ) {
            throw new BooklyLib\Base\ValidationException( implode( PHP_EOL, $wp_user_id->get_error_messages() ), 'wp_user' );
        }

        // Set first/last name for WordPress user.
        $user = get_user_by( 'id', $wp_user_id );
        if ( $user ) {
            $user->first_name = $params['first_name'];
            $user->last_name = $params['last_name'];
            wp_update_user( $user );
        }

        return $user;
    }

    /**
     * Create WooCommerce order
     *
     * @param int $service_id
     * @param float $price
     * @param float $tax
     * @param int|null $wp_user_id
     * @return \WC_Order
     */
    public static function createWCOrder( $service_id, $price, $tax, $wp_user_id )
    {
        $total = get_option( 'bookly_taxes_in_price' ) == 'excluded' ? $price + $tax : $price;
        $order_data = array(
            'status' => get_option( 'bookly_wc_default_order_status' ),
            'customer_id' => (int) $wp_user_id,
        );

        $product_id = get_option( 'bookly_wc_product' );
        if ( $service_id ) {
            $product_id = BooklyLib\Entities\Service::query()
                ->where( 'id', $service_id )->fetchVar( 'wc_product_id' ) ?: $product_id;
        }

        /** @var \WC_Order $order */
        $order = wc_create_order( $order_data );
        $product = wc_get_product( $product_id );
        if ( $product ) {
            $product->set_price( $total );
            $order->add_product( $product );
        }
        $order->set_cart_tax( $tax );
        $order->set_total( $total );
        $order->add_order_note( 'Created via Bookly backend', 0, true );
        $order->save();

        return $order;
    }

    /**
     * Gift card validation
     *
     * @param string $gift_code
     * @param BooklyLib\UserBookingData $userData
     * @return bool
     * @throw \LogicException
     */
    public static function validateGiftCard( $gift_code, BooklyLib\UserBookingData $userData )
    {
        if ( ! Lib\Config::giftCardsActive() ) {
            throw new \LogicException( GiftCard::VALIDATION_INVALID );
        }
        $gift_card = new GiftCard();
        if ( ! $gift_card->loadBy( array( 'code' => $gift_code, ) ) ) {
            throw new \LogicException( GiftCard::VALIDATION_NOT_FOUND );
        }
        $customer = $userData->getCustomer();
        // Check customer.
        if ( ! $gift_card->validForCustomer( $customer ) ) {
            throw new \LogicException( GiftCard::VALIDATION_NOT_FOUND );
        }
        $gift_card_type = GiftCardType::find( $gift_card->getGiftCardTypeId() );
        // Check start date.
        if ( ! $gift_card_type->started() ) {
            throw new \LogicException( GiftCard::VALIDATION_INVALID );
        }
        // Check end date.
        if ( $gift_card_type->expired() ) {
            throw new \LogicException( GiftCard::VALIDATION_EXPIRED );
        }
        if ( ! $gift_card_type->validForCart( $userData->cart ) ) {
            throw new \LogicException( GiftCard::VALIDATION_NOT_FOUND );
        }
        if ( $gift_card->getBalance() <= 0 ) {
            throw new \LogicException( GiftCard::VALIDATION_LOW_BALANCE );
        }
        if ( ! get_option( 'bookly_gift_card_partial_payment', false ) ) {
            $cart_info = $userData->cart->getInfo();
            if ( $gift_card->getBalance() < $cart_info->getPayNowWithoutGiftCard() ) {
                throw new \LogicException( GiftCard::VALIDATION_LOW_BALANCE );
            }
        }

        return true;
    }
}