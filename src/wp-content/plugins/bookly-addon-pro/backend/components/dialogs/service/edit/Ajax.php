<?php
namespace BooklyPro\Backend\Components\Dialogs\Service\Edit;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib\Entities;

class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * Reorder staff preferences for service
     */
    public static function updateServiceStaffPreferenceOrders()
    {
        $service_id = self::parameter( 'service_id' );
        /** @var Entities\StaffPreferenceOrder[] $staff_preferences */
        $staff_preferences = Entities\StaffPreferenceOrder::query()
            ->where( 'service_id', $service_id )
            ->indexBy( 'staff_id' )
            ->find();

        foreach ( self::parameter( 'staff', array() ) as $position => $staff_id ) {
            if ( array_key_exists( $staff_id, $staff_preferences ) ) {
                $staff_preferences[ $staff_id ]->setPosition( $position )->save();
            } else {
                $preference = new Entities\StaffPreferenceOrder();
                $preference
                    ->setServiceId( $service_id )
                    ->setStaffId( $staff_id )
                    ->setPosition( $position )
                    ->save();
            }
        }

        wp_send_json_success();
    }
}