<?php
namespace BooklyPro\Backend\Components\Dialogs\GiftCard\Settings;

use Bookly\Lib as BooklyLib;

class Dialog extends BooklyLib\Base\Component
{
    /**
     * Render gift card type dialog.
     */
    public static function render()
    {
        self::enqueueScripts( array(
            'module' => array( 'js/gift-card-settings-dialog.js' => array( 'bookly-backend-globals' ) ),
        ) );


        self::renderTemplate( 'dialog' );
    }
}