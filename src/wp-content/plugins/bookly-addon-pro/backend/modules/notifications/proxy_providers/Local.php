<?php
namespace BooklyPro\Backend\Modules\Notifications\ProxyProviders;

use Bookly\Backend\Modules\Notifications\Proxy;
use Bookly\Backend\Modules\Notifications;
use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function renderLogsTab( $tab )
    {
        printf( '<li class="nav-item text-center"><a class="nav-link%s" href="%s" data-toggle="bookly-tab" data-tab="logs">%s</a></li>', $tab === 'logs' ? ' active' : '', add_query_arg( array( 'page' => Notifications\Page::pageSlug(), 'tab' => 'logs' ), admin_url( 'admin.php' ) ), __( 'Email logs', 'bookly' ) );
    }

    /**
     * @inheritDoc
     */
    public static function renderLogs()
    {
        $datatables = BooklyLib\Utils\Tables::getSettings( BooklyLib\Utils\Tables::EMAIL_LOGS );

        return self::renderTemplate( 'logs', compact( 'datatables' ), false );
    }
}