<?php
namespace BooklyPro\Backend\Components\Dialogs\Staff\Categories\ProxyProviders;

use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Dialogs\Staff\Categories\Proxy;
use Bookly\Lib as BooklyLib;

class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function renderDialog()
    {
        self::enqueueStyles( array(
            'backend' => array( 'css/fontawesome-all.min.css' => array( 'bookly-backend-globals' ), ),
        ) );

        self::enqueueScripts( array(
            'module' => array( 'js/staff-categories-dialog.js' => array( 'bookly-backend-globals', ) ),
        ) );

        self::renderTemplate( 'dialog' );
    }

    /**
     * @inheritDoc
     */
    public static function renderAdd()
    {
        print '<div class="col-12 col-sm-auto">';
        Buttons::renderDefault( null, 'w-100 mb-3', __( 'Categories', 'bookly' ), array( 'data-toggle' => 'bookly-modal', 'data-target' => '#bookly-staff-categories-modal', 'disabled' => 'disabled' ), true );
        print '</div>';
    }
}