<?php
namespace BooklyPro\Frontend\Modules\StaffForm;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Modules;
use Bookly\Frontend\Modules\ModernBookingForm\Proxy;
use BooklyPro\Lib;
use BooklyPro\Backend\Modules\Appearance;
use BooklyPro\Frontend\Modules\ModernBookingForm;

class ShortCode extends BooklyLib\Base\ShortCode
{
    public static $code = 'bookly-staff-form';

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
                'js/staff-form.js' => array( 'bookly-modern-booking-form.js' ),
            ),
        ) );

        $staff_categories = array();
        $rows = BooklyLib\Entities\Staff::query( 'st' )
            ->select( 'sc.id, sc.name, sc.attachment_id, sc.info, GROUP_CONCAT(st.id) AS staff_ids' )
            ->leftJoin( 'StaffCategory', 'sc', 'sc.id = st.category_id', '\BooklyPro\Lib\Entities' )
            ->sortBy( 'sc.position' )
            ->groupBy( 'sc.id' )
            ->fetchArray();
        foreach ( $rows as $row ) {
            $staff_categories[] = array(
                'id' => $row['id'] ?: '-1',
                'title' => $row['id'] ? BooklyLib\Utils\Common::getTranslatedString( 'staff_category_' . $row['id'], $row['name'] ) : __( 'Uncategorized', 'bookly' ),
                'staff_ids' => $row['staff_ids'],
                'info' => $row['info'],
                'img' => BooklyLib\Utils\Common::getAttachmentUrl( $row['attachment_id'] ),
            );
        }

        wp_localize_script( 'bookly-staff-form.js', 'BooklyL10nStaffForm', array(
            'staff_categories' => $staff_categories,
        ) );
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

        $appearance = Appearance\ProxyProviders\Local::getAppearance( Lib\Entities\Form::TYPE_STAFF_FORM, is_array( $attr ) ? current( $attr ) : null );
        if ( isset( $appearance['token'] ) ) {
            $form_type = 'bookly-staff-form-' . $appearance['token'];
        } else {
            $form_type = 'bookly-staff-form';
        }

        $form_id = uniqid( $form_type . '-', false );

        Proxy\Shared::renderForm( $form_id );

        return self::renderTemplate( 'short_code', compact( 'ajaxurl', 'form_id', 'form_type', 'appearance' ), false );
    }
}