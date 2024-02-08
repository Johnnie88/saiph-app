<?php
namespace BooklyPro\Backend\Components\Dialogs\GiftCard\Type;

use Bookly\Backend\Modules\Settings\Codes;
use Bookly\Lib as BooklyLib;
use BooklyPro\Lib\Entities;

class Dialog extends BooklyLib\Base\Component
{
    /**
     * Render gift card type dialog.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'backend' => array( 'css/fontawesome-all.min.css' => array( 'bookly-backend-globals' ), ),
            'bookly' => array( 'backend/components/ace/resources/css/ace.css', ),
        ) );

        self::enqueueScripts( array(
            'bookly' => array(
                'backend/components/ace/resources/js/ace.js' => array(),
                'backend/components/ace/resources/js/ext-language_tools.js' => array(),
                'backend/components/ace/resources/js/mode-bookly.js' => array(),
                'backend/components/ace/resources/js/editor.js' => array(),
            ),
            'module' => array( 'js/gift-card-type-dialog.js' => array( 'bookly-backend-globals' ) ),
        ) );

        $staff_members = BooklyLib\Entities\Staff::query()
            ->select( 'id, full_name AS title, COALESCE(category_id,0) AS category' )
            ->whereNot( 'visibility', 'archive' )
            ->fetchArray();
        $categories = BooklyLib\Entities\Category::query()->select( 'id, name AS title' )->fetchArray();
        $categories[] = array( 'id' => 0, 'title' => __( 'Uncategorized', 'bookly' ) );

        $staff_categories = Entities\StaffCategory::query()->select( 'id, name AS title' )->fetchArray();
        $staff_categories[] = array( 'id' => 0, 'title' => __( 'Uncategorized', 'bookly' ) );
        $wc = array(
            'enabled' => (int) ( get_option( 'bookly_wc_enabled' ) && get_option( 'bookly_wc_product' ) ),
        );
        if ( $wc['enabled'] ) {
            global $wpdb;

            $query = 'SELECT ID AS id, post_title AS name FROM ' . $wpdb->posts . ' WHERE post_type = \'product\' AND post_status = \'publish\' ORDER BY post_title';

            $wc['collection'] = array_merge( array( array( 'id' => '0', 'name' => __( 'Default', 'bookly' ) ) ), $wpdb->get_results( $query, ARRAY_A ) );
            $wc['codes'] = Codes::getJson( 'woocommerce' );
        }

        wp_localize_script( 'bookly-gift-card-type-dialog.js', 'BooklyL10nGiftCardTypeDialog', array(
            'datePicker' => BooklyLib\Utils\DateTime::datePickerOptions(),
            'staff' => array(
                'collection' => $staff_members,
                'categories' => $staff_categories,
            ),
            'services' => array(
                'collection' => BooklyLib\Entities\Service::query()->select( 'id, title, COALESCE(category_id,0) AS category' )->fetchArray(),
                'categories' => $categories,
            ),
            'wc' => $wc,
            'l10n' => array(
                'title' => __( 'Title', 'bookly' ),
                'no_limit' => __( 'No limit', 'bookly' ),
                'date_limit' => __( 'Date limit (from and to)', 'bookly' ),
                'appointment_limit' => __( 'Limit appointments in cart (min and max)', 'bookly' ),
                'noa_limit' => __( 'Specify minimum and maximum (optional) number of services of the same type required to apply a gift card.', 'bookly' ),
                'amount' => __( 'Amount', 'bookly' ),
                'clear_field' => __( 'Clear field', 'bookly' ),
                'info' => __( 'Info', 'bookly' ),
                'info_help' => sprintf( __( 'This text can be inserted into notifications with %s code.', 'bookly' ), '{service_info}' ),
                'entity' => array(
                    'new' => __( 'New gift card type', 'bookly' ),
                    'edit' => __( 'Edit gift card type', 'bookly' )
                ),
                'services' => array(
                    'label' => __( 'Services', 'bookly' ),
                    'selectAll' => __( 'All services', 'bookly' ),
                    'allSelected' => __( 'All services', 'bookly' ),
                    'nothingSelected' => __( 'No service selected', 'bookly' ),
                ),
                'staff' => array(
                    'label' => __( 'Providers', 'bookly' ),
                    'selectAll' => __( 'All staff', 'bookly' ),
                    'allSelected' => __( 'All staff', 'bookly' ),
                    'nothingSelected' => __( 'No staff selected', 'bookly' ),
                ),
                'wc' => array(
                    'product' => 'WooCommerce ' . __( 'Booking product', 'bookly' ),
                    'item_name' => __( 'Cart item data', 'bookly' ),
                    'help' => sprintf( __( 'Start typing "{" to see the available codes. For more information, see the <a href="%s" target="_blank">documentation</a> page', 'bookly' ), 'https://api.booking-wp-plugin.com/go/bookly-cloud-gift-cards' ),
                ),
                'link_gc_with_buyer_label' => __( 'Automatically link a gift card to a customer', 'bookly' ),
                'link_gc_with_buyer_help' => __( 'If enabled, the card will be automatically associated with the customer who purchases it via the frontend booking form', 'bookly' ),
                'enabled' => __( 'Enabled', 'bookly' ),
                'disabled' =>  __( 'Disabled', 'bookly' ),
                'save' => __( 'Save', 'bookly' ),
                'cancel' => __( 'Cancel', 'bookly' ),
            ),
        ) );

        print '<div id="bookly-gift-card-type-dialog"></div>';
    }
}