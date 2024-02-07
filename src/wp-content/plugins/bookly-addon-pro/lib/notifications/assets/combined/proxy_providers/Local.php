<?php
namespace BooklyPro\Lib\Notifications\Assets\Combined\ProxyProviders;

use Bookly\Lib\Notifications\Assets\Item\Proxy;
use BooklyPro\Lib\Notifications\Assets\Combined\Codes;
use BooklyPro\Lib\Notifications\Assets\Combined\ICS;

abstract class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function createICS( Codes $codes, $recipient )
    {
        return new ICS( $codes, $recipient );
    }
}