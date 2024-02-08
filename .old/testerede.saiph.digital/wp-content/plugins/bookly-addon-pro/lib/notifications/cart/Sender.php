<?php
namespace BooklyPro\Lib\Notifications\Cart;

use Bookly\Lib\DataHolders\Booking\Item;
use Bookly\Lib\DataHolders\Booking\Order;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Notifications\Assets\Item\Attachments;
use Bookly\Lib\Notifications\Base;
use BooklyPro\Lib\Notifications\Assets\Combined\Codes;

abstract class Sender extends Base\Sender
{
    /**
     * Send combined notifications to client.
     *
     * @param Order      $order
     * @param array|bool $queue
     */
    public static function sendCombined( Order $order, &$queue = false )
    {
        $items = $order->getItems();
        /** @var Item $item */
        $item = current( $items );
        $ca = $item->getCA();

        if ( $ca && $ca->isJustCreated() && ! in_array( $ca->getStatus(), array( CustomerAppointment::STATUS_CANCELLED, CustomerAppointment::STATUS_REJECTED ) ) ) {
            $has_correct_appointments = false;
            foreach ( $items as $item ) {
                $ca = $item->getCA();
                if ( $ca && ! in_array( $ca->getStatus(), array( CustomerAppointment::STATUS_CANCELLED, CustomerAppointment::STATUS_REJECTED, CustomerAppointment::STATUS_WAITLISTED ) ) ) {
                    $has_correct_appointments = true;
                    break;
                }
            }
            if ( $has_correct_appointments ) {
                $codes = new Codes( $order );
                $notifications = static::getNotifications( 'new_booking_combined' );

                $attachments = new Attachments( $codes );
                // Notify client.
                foreach ( $notifications['client'] as $notification ) {
                    static::sendToClient( $order->getCustomer(), $notification, $codes, $attachments, $queue );
                }
                // Reply to customer.
                $reply_to = null;
                if ( get_option( 'bookly_email_reply_to_customers' ) ) {
                    $customer = $order->getCustomer();
                    $reply_to = array( 'email' => $customer->getEmail(), 'name' => $customer->getFullName() );
                }
                // Notify custom.
                foreach ( $notifications['staff'] as $notification ) {
                    static::sendToCustom( $notification, $codes, $attachments, $reply_to, $queue );
                }
                if ( $queue === false ) {
                    $attachments->clear();
                }
            }
        }
    }
}