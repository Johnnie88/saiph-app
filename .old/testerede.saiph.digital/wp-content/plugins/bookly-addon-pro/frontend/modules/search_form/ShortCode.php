<?php
namespace BooklyPro\Frontend\Modules\SearchForm;

use Bookly\Lib as BooklyLib;
use Bookly\Frontend\Modules\ModernBookingForm\Proxy;
use BooklyPro\Lib;
use Bookly\Backend\Modules;
use BooklyPro\Backend\Modules\Appearance;
use BooklyPro\Frontend\Modules\ModernBookingForm;

class ShortCode extends BooklyLib\Base\ShortCode
{
    public static $code = 'bookly-search-form';

    /**
     * Link styles.
     */
    public static function linkStyles()
    {
        self::enqueueStyles( array(
            'frontend' => array(
                'css/bootstrap-icons.min.css' => array(),
            ),
        ) );
    }

    /**
     * Link scripts.
     */
    public static function linkScripts()
    {
        ModernBookingForm\Form::render();

        self::enqueueScripts( array(
            'module' => array(
                'js/search-form.js' => array( 'bookly-modern-booking-form.js' ),
            ),
        ) );

        wp_localize_script( 'bookly-search-form.js', 'BooklyL10nSearchForm', array() );
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

        $appearance = Appearance\ProxyProviders\Local::getAppearance( Lib\Entities\Form::TYPE_SEARCH_FORM, is_array( $attr ) ? current( $attr ) : null );
        if ( isset( $appearance['token'] ) ) {
            $form_type = 'bookly-search-form-' . $appearance['token'];
        } else {
            $form_type = 'bookly-search-form';
        }

        $form_id = uniqid( $form_type . '-', false );

        Proxy\Shared::renderForm( $form_id );

        return self::renderTemplate( 'short_code', compact( 'ajaxurl', 'form_type', 'form_id', 'appearance' ), false );
    }
}