<?php
namespace BooklyPro\Backend\Components\Dialogs\GiftCard\Type;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib\Entities;

class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * Get gift card type data.
     */
    public static function getGiftCardTypeData()
    {
        $id = self::parameter( 'id' );
        $query = Entities\GiftCardType::query( 'g' )
            ->select( 'gs.service_id, gst.staff_id' )
            ->leftJoin( 'GiftCardTypeService', 'gs', 'gs.gift_card_type_id = g.id' )
            ->leftJoin( 'GiftCardTypeStaff', 'gst', 'gst.gift_card_type_id = g.id' )
            ->where( 'g.id', $id );
        $service_id = $staff_id = array();
        foreach ( $query->fetchArray() as $record ) {
            if ( $record['service_id'] ) {
                $service_id[] = $record['service_id'];
            }
            if ( $record['staff_id'] ) {
                $staff_id[] = $record['staff_id'];
            }
        }

        wp_send_json_success( array(
            'fields' => Entities\GiftCardType::find( $id )->getFields(),
            'service_id' => array_values( array_unique( $service_id ) ),
            'staff_id' => array_values( array_unique( $staff_id ) ),
        ) );
    }

    /**
     * Save Gift card type
     *
     * @return void
     */
    public static function saveGiftCardType()
    {
        $request = self::getRequest();
        if ( $request->get( 'min_appointments' ) < 1 ) {
            wp_send_json_error( array( 'message' => __( 'Min appointments should be greater than zero.', 'bookly' ) ) );
        } elseif ( $request->get( 'max_appointments', false ) && $request->get( 'max_appointments' ) < 1 ) {
            wp_send_json_error( array( 'message' => __( 'Max appointments should be greater than zero.', 'bookly' ) ) );
        }
        $gift_card_type_id = self::parameter( 'id' );
        $gift_card_type = new Entities\GiftCardType();
        if ( $gift_card_type_id ) {
            $gift_card_type->load( $gift_card_type_id );
        }
        $gift_card_type
            ->setTitle( $request->get( 'title' ) === null ? '' : $request->get( 'title' ) )
            ->setAmount( $request->get( 'amount', 0 ) )
            ->setStartDate( $request->get( 'start_date' ) )
            ->setEndDate( $request->get( 'end_date' ) )
            ->setMinAppointments( $request->get( 'min_appointments' ) )
            ->setMaxNumberOfAppointments( $request->get( 'max_appointments' ) )
            ->setLinkWithBuyer( $request->get( 'link_with_buyer' ) )
            ->setInfo( $request->get( 'info' ) );
        if ( get_option( 'bookly_wc_enabled' ) && get_option( 'bookly_wc_product' ) ) {
            $gift_card_type
                ->setWcProductId( $request->get( 'wc_product_id', '0' ) )
                ->setWcCartInfo( $request->get( 'wc_cart_info' ) )
                ->setWcCartInfoName( $request->get( 'wc_cart_info_name' ) );
        }
        $gift_card_type->save();

        $services = $request->get( 'services', array() );
        if ( empty ( $services ) ) {
            Entities\GiftCardTypeService::query()
                ->delete()
                ->where( 'gift_card_type_id', $gift_card_type->getId() )
                ->execute();
        } else {
            Entities\GiftCardTypeService::query()
                ->delete()
                ->where( 'gift_card_type_id', $gift_card_type->getId() )
                ->whereNotIn( 'service_id', $services )
                ->execute();
            $existing_services = Entities\GiftCardTypeService::query()
                ->select( 'service_id' )
                ->where( 'gift_card_type_id', $gift_card_type->getId() )
                ->indexBy( 'service_id' )
                ->fetchArray();
            foreach ( $services as $service_id ) {
                if ( ! isset ( $existing_services[ $service_id ] ) ) {
                    $gift_service = new Entities\GiftCardTypeService();
                    $gift_service
                        ->setGiftCardTypeId( $gift_card_type->getId() )
                        ->setServiceId( $service_id )
                        ->save();
                }
            }
        }
        // Staff.
        $staff = $request->get( 'staff', array() );
        if ( empty ( $staff ) ) {
            Entities\GiftCardTypeStaff::query()
                ->delete()
                ->where( 'gift_card_type_id', $gift_card_type->getId() )
                ->execute();
        } else {
            Entities\GiftCardTypeStaff::query()
                ->delete()
                ->where( 'gift_card_type_id', $gift_card_type->getId() )
                ->whereNotIn( 'staff_id', $staff )
                ->execute();
            $existing_staff = Entities\GiftCardTypeStaff::query()
                ->select( 'staff_id' )
                ->where( 'gift_card_type_id', $gift_card_type->getId() )
                ->indexBy( 'staff_id' )
                ->fetchArray();
            foreach ( $staff as $staff_id ) {
                if ( ! isset ( $existing_staff[ $staff_id ] ) ) {
                    $gift_staff = new Entities\GiftCardTypeStaff();
                    $gift_staff
                        ->setGiftCardTypeId( $gift_card_type->getId() )
                        ->setStaffId( $staff_id )
                        ->save();
                }
            }
        }
        wp_send_json_success();
    }
}