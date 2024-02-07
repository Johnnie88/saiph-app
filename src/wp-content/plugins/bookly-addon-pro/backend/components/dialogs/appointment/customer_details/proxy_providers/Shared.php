<?php
namespace BooklyPro\Backend\Components\Dialogs\Appointment\CustomerDetails\ProxyProviders;

use Bookly\Backend\Components\Dialogs\Appointment\CustomerDetails\Proxy;
use Bookly\Lib as BooklyLib;

class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function prepareL10n( $localize )
    {
        $localize['timeZoneOptions'] = BooklyLib\Utils\DateTime::getTimeZoneOptions();
        $localize['l10n']['selectCity'] = __( 'Select a city' );

        return $localize;
    }
}