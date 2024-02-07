<?php
namespace BooklyPro\Frontend\Modules\CancellationConfirmation;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;
use BooklyPro\Backend\Modules\Appearance;

class ShortCode extends BooklyLib\Base\ShortCode
{
    public static $code = 'bookly-cancellation-confirmation';

    /**
     * Link styles.
     */
    public static function linkStyles()
    {
        self::enqueueStyles( array(
            'alias' => array( 'bookly-backend-globals' ),
        ) );
    }

    /**
     * Link scripts.
     */
    public static function linkScripts()
    {
    }

    /**
     * Render shortcode.
     *
     * @param array $attributes
     * @return string
     */
    public static function render( $attributes )
    {
        // Disable caching.
        BooklyLib\Utils\Common::noCache();

        self::enqueueStyles( array(
            'bookly' => array( 'backend/resources/css/fontawesome-all.min.css' => array( 'bookly-backend-globals' ) ),
        ) );

        // Prepare URL for AJAX requests.
        $ajax_url = admin_url( 'admin-ajax.php' );

        $token = self::parameter( 'bookly-appointment-token', '' );

        $appearance = Appearance\ProxyProviders\Local::getAppearance( Lib\Entities\Form::TYPE_CANCELLATION_FORM, is_array( $attributes ) ? current( $attributes ) : null );

        $error = false;

        $customer_appointment = new BooklyLib\Entities\CustomerAppointment();
        if ( ! $customer_appointment->loadBy( compact( 'token' ) ) ) {
            $error = $appearance['l10n']['error_not_found'];
        } elseif ( in_array( $customer_appointment->getStatus(), array( BooklyLib\Entities\CustomerAppointment::STATUS_CANCELLED, BooklyLib\Entities\CustomerAppointment::STATUS_REJECTED ) ) ) {
            $error = $appearance['l10n']['error_cancelled'];
        } elseif ( ! $customer_appointment->cancelAllowed() ) {
            $error = $appearance['l10n']['error_not_allowed'];
        }

        return self::renderTemplate( 'short_code', compact( 'ajax_url', 'token', 'attributes', 'appearance', 'error' ), false );
    }
}