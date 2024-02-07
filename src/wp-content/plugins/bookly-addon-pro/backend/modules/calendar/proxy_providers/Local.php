<?php
namespace BooklyPro\Backend\Modules\Calendar\ProxyProviders;

use Bookly\Backend\Modules\Calendar\Proxy;

class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function renderServicesFilterOption()
    {
        echo sprintf( '<li data-value="custom">%s</li>', esc_attr__( 'Custom', 'bookly' ) );
    }
}