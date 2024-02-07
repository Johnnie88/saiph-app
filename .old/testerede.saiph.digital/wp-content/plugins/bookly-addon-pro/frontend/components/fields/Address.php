<?php
namespace BooklyPro\Frontend\Components\Fields;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

class Address extends BooklyLib\Base\Component
{
    /**
     * Render inputs for address fields on the frontend.
     *
     * @param BooklyLib\UserBookingData $userData
     */
    public static function render( BooklyLib\UserBookingData $userData )
    {
        $form_id = $userData->getFormId();
        $displayed_fields = Lib\Utils\Common::getDisplayedAddressFields();
        foreach ( $displayed_fields as $field_name => $field ) {
            $field_value = $userData->getAddressField( $field_name );
            self::renderTemplate( 'address',
                compact( 'field_name', 'field_value', 'form_id' )
            );
        }
        $hidden = true;
        foreach ( Lib\Utils\Common::getAddressFields() as $field_name => $field ) {
            if ( ! array_key_exists( $field_name, $displayed_fields ) ) {
                $field_value = $userData->getAddressField( $field_name );
                self::renderTemplate( 'address',
                    compact( 'field_name', 'field_value', 'hidden', 'form_id' )
                );
            }
        }
    }
}