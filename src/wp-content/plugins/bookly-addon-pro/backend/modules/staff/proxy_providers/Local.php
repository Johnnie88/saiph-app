<?php
namespace BooklyPro\Backend\Modules\Staff\ProxyProviders;

use Bookly\Backend\Modules\Staff\Proxy;
use BooklyPro\Lib;

class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function getCategoriesList()
    {
        return Lib\Entities\StaffCategory::query()->sortBy( 'position' )->fetchArray();
    }
}