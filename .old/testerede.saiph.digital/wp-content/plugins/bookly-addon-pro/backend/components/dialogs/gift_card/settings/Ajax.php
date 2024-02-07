<?php
namespace BooklyPro\Backend\Components\Dialogs\GiftCard\Settings;

use Bookly\Lib as BooklyLib;

class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * Save gift cards settings.
     */
    public static function saveGiftCardsSettings()
    {
        update_option( 'bookly_gift_card_partial_payment', self::parameter( 'partial_payment' ) );
        update_option( 'bookly_cloud_gift_default_code_mask', self::parameter( 'mask' ) );

        wp_send_json_success();
    }
}