<?php
namespace BooklyPro\Backend\Modules\Calendar\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Modules\Calendar\Proxy;
use BooklyPro\Lib;

class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function prepareAppointmentCodesData( array $codes, $appointment_data, $participants )
    {
        if ( $participants == 'one' ) {
            $codes['client_address'] = esc_html( Lib\Utils\Common::getFullAddressByCustomerData( $appointment_data ) );
            $codes['client_full_birthday'] = $codes['client_birthday'] ? BooklyLib\Utils\DateTime::formatDate( $codes['client_birthday'] ) : '';
            $codes['client_birthday'] = $codes['client_birthday'] ? date_i18n( 'F j', strtotime( $codes['client_birthday'] ) ) : '';
        }
        foreach ( $codes['participants'] as &$participant ) {
            $participant['client_full_birthday'] = $participant['client_birthday'] ? BooklyLib\Utils\DateTime::formatDate( $$participant['client_birthday'] ) : '';
            $participant['client_birthday'] = $participant['client_birthday'] ? date_i18n( 'F j', strtotime( $participant['client_birthday'] ) ) : '';
        }

        return $codes;
    }
}