<?php
namespace BooklyPro\Frontend\Modules\Booking\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Customer;
use BooklyPro\Lib;
use BooklyPro\Lib\Config;
use Bookly\Frontend\Components\Booking\InfoText;
use Bookly\Frontend\Modules\Booking\Lib\Steps;
use Bookly\Frontend\Modules\Booking\Proxy;

class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function renderTimeZoneSwitcher()
    {
        if ( get_option( 'bookly_app_show_time_zone_switcher' ) ) {
            $time_zone = BooklyLib\Slots\DatePoint::$client_timezone;
            if ( $time_zone[0] == '+' || $time_zone[0] == '-' ) {
                $parts = explode( ':', $time_zone );
                $time_zone = sprintf(
                    'UTC%s%d%s',
                    $time_zone[0],
                    abs( $parts[0] ),
                    (int) $parts[1] ? '.' . rtrim( $parts[1] * 100 / 60, '0' ) : ''
                );
            }
            $time_zone_options = wp_timezone_choice( $time_zone, BooklyLib\Config::getLocale() );
            if ( strpos( $time_zone_options, 'selected' ) === false ) {
                $time_zone_options .= sprintf(
                    '<option selected="selected" value="%s">%s</option>',
                    esc_attr( $time_zone ),
                    esc_html( $time_zone )
                );
            }

            self::renderTemplate( 'time_zone_switcher', compact( 'time_zone_options' ) );
        }
    }

    /**
     * @inheritDoc
     */
    public static function renderFacebookButton()
    {
        if ( Lib\Config::showFacebookLoginButton() ) {
            self::renderTemplate( 'fb_button' );
        }
    }

    /**
     * @inheritDoc
     */
    public static function renderDetailsAddress( BooklyLib\UserBookingData $userData )
    {
        if ( get_option( 'bookly_app_show_address' ) ) {
            self::renderTemplate( 'address', compact( 'userData' ) );
        }
    }

    /**
     * @inheritDoc
     */
    public static function renderDetailsBirthday( BooklyLib\UserBookingData $userData )
    {
        if ( get_option( 'bookly_app_show_birthday' ) ) {
            self::renderTemplate( 'birthday', compact( 'userData' ) );
        }
    }

    /**
     * @inheritDoc
     */
    public static function filterGateways( $gateways, BooklyLib\UserBookingData $userData )
    {
        $default_gateways = array_keys( $gateways );
        $staff_list = array();
        $service_list = array();
        $staff_counter = array();
        $service_counter = array();
        foreach ( $userData->cart->getItems() as $cart_item ) {
            if ( $cart_item->getType() !== 'gift_card' ) {
                self::setCounter( $cart_item->getStaff(), $staff_counter, $staff_list, $default_gateways );
                self::setCounter( $cart_item->getService(), $service_counter, $service_list, $default_gateways );
            } else {
                foreach ( $default_gateways as $gateway_name ) {
                    if ( ! isset( $staff_counter[ $gateway_name ] ) ) {
                        $staff_counter[ $gateway_name ] = 0;
                    }
                    if ( ! isset( $service_counter[ $gateway_name ] ) ) {
                        $service_counter[ $gateway_name ] = 0;
                    }
                }
            }
        }

        $staff_count = count( $staff_list );
        $service_count = count( $service_list );
        foreach ( $gateways as $gateway_name => $data ) {
            if ( isset( $staff_counter[ $gateway_name ], $service_counter[ $gateway_name ] )
                && $staff_counter[ $gateway_name ] == $staff_count
                && $service_counter[ $gateway_name ] == $service_count
            ) {
                continue;
            }
            unset( $gateways[ $gateway_name ] );
        }

        return $gateways;
    }

    /**
     * @inheritDoc
     */
    public static function getHtmlPaymentImpossible( $progress_tracker, BooklyLib\UserBookingData $userData )
    {
        $info = InfoText::prepare( Steps::PAYMENT, BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_payment_step_without_intersected_gateways' ), $userData );

        return self::renderTemplate( 'payment_impossible', compact( 'progress_tracker', 'info' ), false );
    }

    /**
     * @inheritDoc
     */
    public static function renderPaymentStep( BooklyLib\UserBookingData $userData )
    {
        if ( get_option( 'bookly_cloud_gift_enabled' ) && Config::giftCardsActive() ) {
            self::renderTemplate( 'gift_cards', compact( 'userData' ) );
        }
        if ( get_option( 'bookly_app_show_tips' ) ) {
            self::renderTemplate( 'tips', compact( 'userData' ) );
        }
    }

    /**
     * @inheritDoc
     */
    public static function prepareHtmlContentDoneStep( $userData, $codes )
    {
        $content = '';
        if ( get_option( 'bookly_app_show_appointment_qr' ) && count( $userData->cart->getItems() ) === 1 ) {
            $ics = BooklyLib\Utils\Ics\Feed::createFromOrder( new BooklyLib\Entities\Order( array( 'id' => $userData->getOrderId() ) ) );
            if ( $ics->getEvents() ) {
                $content .= '<div class="bookly-js-qr bookly-image-box bookly-loading">
                <img src="' . add_query_arg( array(
                        'cht' => 'qr',
                        'chs' => '298x298',
                        'chl' => urlencode( $ics->render() ),
                    ), 'https://chart.googleapis.com/chart' ) . '"></div>';
            }
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    public static function findOneGiftCardByCode( $code )
    {
        return Lib\Entities\GiftCard::query()->where( 'code', $code )->findOne();
    }

    /**
     * @inheritDoc
     */
    public static function getCustomerByFacebookId( $facebook_id )
    {
        if ( $facebook_id && Config::showFacebookLoginButton() ) {
            // Try to find customer by Facebook ID.
            return Customer::query()
                ->where( 'facebook_id', $facebook_id )
                ->findOne() ?: false;
        }

        return false;
    }

    /**
     * @param BooklyLib\Entities\Service|BooklyLib\Entities\Staff $entity
     * @param array $counter
     * @param array $list
     * @param array $default
     * @return void
     */
    protected static function setCounter( $entity, &$counter, &$list, $default )
    {
        $id = $entity->getId();
        if ( ! in_array( $id, $list ) ) {
            $list[] = $id;
            $gateways = $entity->getGateways()
                ? json_decode( $entity->getGateways(), true )
                : $default;
            foreach ( $gateways as $gateway_name ) {
                if ( in_array( $gateway_name, $default ) ) {
                    if ( ! isset( $counter[ $gateway_name ] ) ) {
                        $counter[ $gateway_name ] = 0;
                    }
                    $counter[ $gateway_name ]++;
                }
            }
        }
    }
}