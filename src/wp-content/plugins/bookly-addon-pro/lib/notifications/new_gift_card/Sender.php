<?php
namespace BooklyPro\Lib\Notifications\NewGiftCard;

use Bookly\Lib\Entities\Customer;
use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Notifications\Base;
use BooklyPro\Lib\Entities\GiftCard;
use BooklyPro\Lib\Notifications\Assets\NewGiftCard;

abstract class Sender extends Base\Sender
{
    /**
     * Send notification about gift card.
     *
     * @param GiftCard $gift_card
     * @param int $customer_id
     * @param $queue
     * @return void
     */
    public static function send( GiftCard $gift_card, $customer_id = null, &$queue = false )
    {
        $codes = new NewGiftCard\Client\Codes( $gift_card, $customer_id );
        $attachments = null;
        $notifications = static::getNotifications( 'new_gift_card' );
        $reply_to = null;
        if ( $customer_id ) {
            $customer = Customer::find( $customer_id );
            if ( $customer ) {
                foreach ( $notifications['client'] as $notification ) {
                    static::sendToClient( $customer, $notification, $codes, $attachments, $queue );
                }

                if ( get_option( 'bookly_email_reply_to_customers' ) ) {
                    $reply_to = array( 'email' => $customer->getEmail(), 'name' => $customer->getFullName() );
                }
            }
        }

        if ( $notifications['staff'] ) {
            $staff_list = Staff::query( 's' )
                ->leftJoin( 'GiftCardTypeStaff', 'gct', 'gct.staff_id = s.id', 'BooklyPro\Lib\Entities' )
                ->leftJoin( 'GiftCardType', 'gc', 'gc.id = gct.gift_card_type_id', 'BooklyPro\Lib\Entities' )
                ->where( 'gc.id', $gift_card->getId() )
                ->find();
            foreach ( $notifications['staff'] as $notification ) {
                foreach ( $staff_list as $staff ) {
                    static::sendToStaff( $staff, $notification, $codes, $attachments, $reply_to, $queue );
                }
                static::sendToAdmins( $notification, $codes, $attachments, $reply_to, $queue );
                static::sendToCustom( $notification, $codes, $attachments, $reply_to, $queue );
            }
        }
    }
}