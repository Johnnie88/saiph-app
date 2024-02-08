<?php
namespace BooklyPro\Backend\Modules\CloudGiftCards;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib\Entities;

class Page extends BooklyLib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        $tab = self::parameter( 'tab', 'cards' );
        self::enqueueStyles( array(
            'alias' => array( 'bookly-backend-globals' ),
        ) );

        self::enqueueScripts( array(
            'module' => array( 'js/gift-cards.js' => array( 'bookly-gift-card-dialog.js', 'bookly-gift-card-type-dialog.js', 'bookly-payment-details-dialog.js' ) ),
        ) );

        $services = BooklyLib\Entities\Service::query()
            ->select( 'id, title' )
            ->indexBy( 'id' )
            ->fetchArray();

        $staff_members = BooklyLib\Entities\Staff::query()
            ->select( 'id, full_name AS title' )
            ->indexBy( 'id' )
            ->whereNot( 'visibility', 'archive' )
            ->fetchArray();

        $card_types = Entities\GiftCardType::query()
            ->select( 'id, title' )
            ->fetchArray();

        $customers_count = BooklyLib\Entities\Customer::query()->count();
        if ( $customers_count < BooklyLib\Entities\Customer::REMOTE_LIMIT ) {
            $remote = false;
            $customers = BooklyLib\Entities\Customer::query()
                ->select( 'id, full_name, phone, email' )->sortBy( 'full_name' )->indexBy( 'id' )->fetchArray();
        } else {
            $customers = array();
            $remote = true;
        }

        $datatables = BooklyLib\Utils\Tables::getSettings( array(
            BooklyLib\Utils\Tables::GIFT_CARDS,
            BooklyLib\Utils\Tables::GIFT_CARD_TYPES,
        ) );

        wp_localize_script( 'bookly-gift-cards.js', 'BooklyGiftCardsL10n', array(
            'edit' => __( 'Edit', 'bookly' ),
            'cancel' => __( 'Cancel', 'bookly' ),
            'yes' => __( 'Yes', 'bookly' ),
            'zeroGiftCardTypesRecords' => __( 'No gift card types found.', 'bookly' ),
            'zeroGiftCardsRecords' => __( 'No gift cards found.', 'bookly' ),
            'processing' => __( 'Processing...', 'bookly' ),
            'areYouSure' => __( 'Are you sure?', 'bookly' ),
            'deletingGiftCardTypeInfo' => __( 'All related gift cards will be deleted', 'bookly' ),
            'delete' => __( 'Delete', 'bookly' ),
            'noResultFound' => __( 'No result found', 'bookly' ),
            'searching' => __( 'Searching', 'bookly' ),
            'clearField' => __( 'Clear field', 'bookly' ),
            'services' => array(
                'allSelected' => __( 'All services', 'bookly' ),
                'nothingSelected' => __( 'No service selected', 'bookly' ),
                'unknownSelected' => __( 'N/A', 'bookly' ),
                'collection' => $services,
                'count' => count( $services ),
            ),
            'staff' => array(
                'allSelected' => __( 'All staff', 'bookly' ),
                'nothingSelected' => __( 'No staff selected', 'bookly' ),
                'unknownSelected' => __( 'N/A', 'bookly' ),
                'collection' => $staff_members,
                'count' => count( $staff_members ),
            ),
            'customers' => array(
                'nothingSelected' => __( 'No limit', 'bookly' ),
                'collection' => $customers,
                'count' => $customers_count,
                'remote' => $remote,
            ),
            'datatables' => $datatables,
            'tab' => $tab,
        ) );

        $dropdown_data = array(
            'service' => BooklyLib\Utils\Common::getServiceDataForDropDown( 's.type <> "package"' ),
            'staff' => BooklyLib\Proxy\Pro::getStaffDataForDropDown(),
        );

        self::renderTemplate( 'index', compact( 'services', 'staff_members', 'customers', 'card_types', 'remote', 'dropdown_data', 'datatables' ) );
    }

    /**
     * Show 'Gift Cards' submenu inside Bookly Cloud main menu.
     *
     * @param array $product
     */
    public static function addBooklyCloudMenuItem( $product )
    {
        $title = $product['texts']['title'];

        add_submenu_page(
            'bookly-cloud-menu',
            $title,
            $title,
            BooklyLib\Utils\Common::getRequiredCapability(),
            self::pageSlug(),
            function () {
                Page::render();
            }
        );
    }
}