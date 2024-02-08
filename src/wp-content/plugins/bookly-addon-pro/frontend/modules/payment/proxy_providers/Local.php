<?php
namespace BooklyPro\Frontend\Modules\Payment\ProxyProviders;

use Bookly\Lib\Entities;
use Bookly\Lib\Payment\Proxy;
use BooklyPro\Lib\Notifications;
use BooklyPro\Lib\DataHolders;

class Local extends Proxy\Pro
{
    /**
     * @inerhitDoc
     */
    public static function completeGiftCard( Entities\Payment $payment )
    {
        $details = $payment->getDetailsData();
        $items = array();
        $gift_cards = array();
        foreach ( $details->getItems() as $item ) {
            if ( $item['type'] === 'gift_card' ) {
                $gift_card_details = new DataHolders\Details\GiftCard( $item );
                $gift_card = $gift_card_details->createGiftCard( $payment );
                if ( $gift_card ) {
                    $items[] = $gift_card_details->getData();
                    $gift_cards[] = $gift_card;
                }
            } else {
                $items[] = $item;
            }
        }
        if ( $gift_cards ) {
            $details->setData( compact( 'items' ) );
            $payment->save();
            foreach ( $gift_cards as $gift_card ) {
                Notifications\NewGiftCard\Sender::send( $gift_card, $details->getValue( 'customer_id' ) );
            }
        }

        return $payment;
    }
}