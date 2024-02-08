<?php
namespace BooklyPro\Lib\Notifications\Assets\Combined;

use Bookly\Lib\Utils;

class ICS extends Utils\Ics\Base
{
    protected $data;

    /**
     * Constructor.
     *
     * @param Codes $codes
     * @param string $recipient
     */
    public function __construct( Codes $codes, $recipient = 'client' )
    {
        $description_template = $this->getDescriptionTemplate( $recipient );
        $this->data =
            "BEGIN:VCALENDAR\n"
            . "VERSION:2.0\n"
            . "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n"
            . "CALSCALE:GREGORIAN\n";
        foreach ( $codes->cart_info as $item ) {
            $description_codes = Utils\Codes::getICSCodes( $item['item'] );
            $this->data .= sprintf(
                "BEGIN:VEVENT\n"
                . "DTSTART:%s\n"
                . "DTEND:%s\n"
                . "SUMMARY:%s\n"
                . "DESCRIPTION:%s\n"
                . "LOCATION:%s\n"
                . "END:VEVENT\n",
                $this->formatDateTime( $item['appointment_start'] ),
                $this->formatDateTime( $item['appointment_end'] ),
                $this->escape( $item['service_name'] ),
                $this->escape( Utils\Codes::replace( $description_template, $description_codes, false ) ),
                $this->escape( sprintf( "%s", $item['location'] ) )
            );
        }
        $this->data .= 'END:VCALENDAR';
    }
}