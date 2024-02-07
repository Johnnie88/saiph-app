<?php
namespace BooklyPro\Frontend\Modules\ModernBookingForm;

use BooklyPro\Lib;
use Bookly\Lib as BooklyLib;
use Bookly\Frontend\Modules\ModernBookingForm\Proxy;

class Form extends BooklyLib\Base\Component
{
    public static function render()
    {
        $tel_input_enabled = get_option( 'bookly_cst_phone_default_country' ) !== 'disabled';

        self::enqueueScripts( array(
            'backend' => array(
                'js/common.js' => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/bootstrap.bundle.min.js' => array( 'jquery' ),
            ),
            'module' => array(
                'js/modern-booking-form.js' => array( 'bookly-bootstrap.bundle.min.js', 'bookly-frontend-globals', 'bookly-daterangepicker.js' ),
            ),
        ) );
        self::enqueueStyles( array(
            'module' => array( 'css/modern-booking-form.css' ),
        ) );
        if ( $tel_input_enabled ) {
            self::enqueueStyles( array(
                'bookly' => array( 'frontend/resources/css/intlTelInput.css' ),
            ) );
            self::enqueueScripts( array(
                'bookly' => array( 'frontend/resources/js/intlTelInput.min.js' => array( 'jquery' ) ),
            ) );
        }

        $customer = new BooklyLib\Entities\Customer();
        if ( is_user_logged_in() ) {
            $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) );
            if ( ! $customer->getId() ) {
                $customer->setFirstName( wp_get_current_user()->first_name );
                $customer->setLastName( wp_get_current_user()->last_name );
                $customer->setFullName( wp_get_current_user()->display_name );
                $customer->setEmail( wp_get_current_user()->user_email );
            }
        } elseif ( get_option( 'bookly_cst_remember_in_cookie' ) ) {
            if ( isset( $_COOKIE['bookly-customer-email'] ) ) {
                $customer->setEmail( $_COOKIE['bookly-customer-email'] );
            }
            if ( isset( $_COOKIE['bookly-customer-phone'] ) ) {
                $customer->setPhone( $_COOKIE['bookly-customer-phone'] );
            }
            if ( isset( $_COOKIE['bookly-customer-full-name'] ) ) {
                $customer->setFullName( $_COOKIE['bookly-customer-full-name'] );
            }
            if ( isset( $_COOKIE['bookly-customer-first-name'] ) ) {
                $customer->setFirstName( $_COOKIE['bookly-customer-first-name'] );
            }
            if ( isset( $_COOKIE['bookly-customer-last-name'] ) ) {
                $customer->setLastName( $_COOKIE['bookly-customer-last-name'] );
            }
        }

        // Gift cards
        $gift_cards = Lib\Config::giftCardsActive()
            ? Lib\Entities\GiftCardType::query( 'g' )
                ->select( 'g.id, g.title, g.amount, GROUP_CONCAT(DISTINCT gs.id) AS service_ids, GROUP_CONCAT(DISTINCT gst.id) AS staff_ids' )
                ->leftJoin( 'GiftCardTypeService', 'gs', 'gs.gift_card_type_id = g.id' )
                ->leftJoin( 'GiftCardTypeStaff', 'gst', 'gst.gift_card_type_id = g.id' )
                ->whereGt( 'end_date', current_time( 'mysql' ) )
                ->where( 'end_date', null, 'OR' )
                ->groupBy( 'g.id' )
                ->fetchArray()
            : array();
        foreach ( $gift_cards as &$gift_card ) {
            $gift_card['title'] = BooklyLib\Utils\Common::getTranslatedString( 'gift_card_type_' . $gift_card['id'], $gift_card['title'] );
        }
        unset( $gift_card );

        wp_localize_script(
            'bookly-modern-booking-form.js', 'BooklyL10nModernBookingForm', Proxy\Shared::prepareFormOptions( array(
            'customer' => array(
                'first_name' => $customer->getFirstName(),
                'last_name' => $customer->getLastName(),
                'email' => $customer->getEmail(),
                'phone' => $customer->getPhone(),
                'country' => $customer->getCountry(),
                'state' => $customer->getState(),
                'postcode' => $customer->getPostcode(),
                'city' => $customer->getCity(),
                'street' => $customer->getStreet(),
                'street_number' => $customer->getStreetNumber(),
                'additional_address' => $customer->getAdditionalAddress(),
                'full_address' => $customer->getFullAddress(),
            ),
            'complex_services' => array(),
            'gift_cards' => $gift_cards,
            'format_price' => BooklyLib\Utils\Price::formatOptions(),
            'datePicker' => BooklyLib\Utils\DateTime::datePickerOptions(),
            'maxDaysForBooking' => BooklyLib\Config::getMaximumAvailableDaysForBooking(),
            'moment_format_date' => BooklyLib\Utils\DateTime::convertFormat( 'date', BooklyLib\Utils\DateTime::FORMAT_MOMENT_JS ),
            'moment_format_time' => BooklyLib\Utils\DateTime::convertFormat( 'time', BooklyLib\Utils\DateTime::FORMAT_MOMENT_JS ),
            'casest' => BooklyLib\Config::getCaSeSt(),
            'use_client_timezone' => BooklyLib\Config::useClientTimeZone(),
            'timezones' => BooklyLib\Utils\DateTime::getTimeZoneOptions(),
            'images' => plugins_url( 'frontend/resources/images/', BooklyLib\Plugin::getMainFile() ),
            'intlTelInput' => array(
                'enabled' => $tel_input_enabled,
                'utils' => plugins_url( 'intlTelInput.utils.js', BooklyLib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                'country' => get_option( 'bookly_cst_phone_default_country' ),
            ),
        ) )
        );
    }
}