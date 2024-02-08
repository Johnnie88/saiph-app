<?php
namespace BooklyPro\Frontend\Modules\Calendar;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Modules;

class ShortCode extends BooklyLib\Base\ShortCode
{
    public static $code = 'bookly-calendar';

    /**
     * Init component.
     */
    public static function init()
    {
        if ( get_option( 'bookly_cal_frontend_enabled' ) ) {
            // Register short code.
            parent::init();
        }
    }

    /**
     * Link styles.
     */
    public static function linkStyles()
    {
        self::enqueueStyles( array(
            'bookly' => array( 'backend/modules/calendar/resources/css/event-calendar.min.css' => array( 'bookly-backend-globals' ) ),
            'module' => array( '/css/frontend-calendar.css' => array( 'bookly-event-calendar.min.css' ) ),
        ) );
    }

    /**
     * Link scripts.
     */
    public static function linkScripts()
    {
        self::enqueueScripts( array(
            'bookly' => array(
                'backend/modules/calendar/resources/js/event-calendar.min.js' => array( 'bookly-frontend-globals', 'bookly-daterangepicker.js' ),
                'backend/modules/calendar/resources/js/calendar-common.js' => array( 'bookly-event-calendar.min.js' ),
            ),
            'module' => array(
                'js/frontend-calendar.js' => array( 'bookly-calendar-common.js' ),
            ),
        ) );
        wp_localize_script( 'bookly-frontend-calendar.js', 'BooklyL10nFrontendCalendar', BooklyLib\Utils\Common::getCalendarSettings() );
    }

    /**
     * Render shortcode.
     *
     * @param array $attr
     * @return string
     */
    public static function render( $attr )
    {
        global $sitepress;

        // Disable caching.
        BooklyLib\Utils\Common::noCache();

        // Prepare URL for AJAX requests.
        $ajaxurl = admin_url( 'admin-ajax.php' );

        // Support WPML.
        if ( $sitepress instanceof \SitePress ) {
            $ajaxurl = add_query_arg( array( 'lang' => $sitepress->get_current_language() ), $ajaxurl );
        }

        $attributes = array();
        foreach ( array( 'location_id', 'staff_id', 'service_id' ) as $key ) {
            if ( isset( $attr[ $key ] ) ) {
                $attributes[ $key ] = (int) $attr[ $key ];
            }
        }

        $calendar_js = uniqid( 'bookly-js-calendar-' );
        return self::renderTemplate( 'short_code', compact( 'ajaxurl', 'attributes', 'calendar_js' ), false );
    }
}