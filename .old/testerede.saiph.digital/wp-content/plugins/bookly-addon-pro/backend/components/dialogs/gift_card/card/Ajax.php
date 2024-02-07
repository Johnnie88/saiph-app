<?php
namespace BooklyPro\Backend\Components\Dialogs\GiftCard\Card;

use Bookly\Lib\Entities\Payment;
use BooklyPro\Lib;
use Bookly\Lib as BooklyLib;
use BooklyPro\Lib\Entities;

class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * Get Gift card data
     */
    public static function getGiftCardData()
    {
        $gift_card_types = Entities\GiftCardType::query()
            ->select( 'id, title, amount' )
            ->fetchArray();
        $gift_card = null;
        $gift_card_id = self::parameter( 'id' );
        $customer = null;
        $payment = array(
            'payment_id' => null,
            'payment_type' => null,
            'payment_title' => ''
        );
        if ( $gift_card_id ) {
            $gift_card = Entities\GiftCard::find( $gift_card_id )->getFields();

            if ( self::parameter( 'required_customer_data' ) && $gift_card['customer_id'] ) {
                $customer = BooklyLib\Entities\Customer::query()
                    ->select( 'id, full_name AS text, email, phone' )
                    ->where( 'id', $gift_card['customer_id'] )
                    ->fetchRow();
                $name = $customer['text'];
                if ( $customer['email'] != '' || $customer['phone'] != '' ) {
                    $name .= ' (' . trim( $customer['email'] . ', ' . $customer['phone'], ', ' ) . ')';
                }
                $customer['name'] = $name;
            }
            if ( $gift_card['payment_id'] ) {
                $item = BooklyLib\Entities\Payment::find( $gift_card['payment_id'] );
                $payment['payment_id'] = $item->getId();
                $payment['payment_type'] = $item->getTotal() === $item->getPaid() ? 'full' : 'partial';
                $payment['payment_title'] = BooklyLib\Entities\Payment::paymentInfo(
                    $item->getPaid(),
                    $item->getTotal(),
                    $item->getType(),
                    $item->getStatus()
                );
            }
        } else {
            $gift_card_types = array_merge( array( array( 'id' => null, 'title' => __( 'Select card type', 'bookly' ), 'amount' => 0 ), ), $gift_card_types );
        }

        wp_send_json_success( compact( 'gift_card', 'gift_card_types', 'customer', 'payment' ) );
    }

    /**
     * Create/update gift card
     */
    public static function saveGiftCard()
    {
        $request = self::getRequest();
        $queue = array();
        $gift_card = new Entities\GiftCard();
        $customer_id = $request->get( 'customer_id' );

        if ( $request->get( 'id' ) && $gift_card->load( $request->get( 'id' ) ) ) {
            $gift_card->setBalance( $request->get( 'balance' ) );
        } else {
            $gift_card->setBalance( Entities\GiftCardType::query()->where( 'id', $request->get( 'gift_card_type_id' ) )->fetchVar( 'amount' ) );
        }

        $gift_card
            ->setGiftCardTypeId( $request->get( 'gift_card_type_id' ) )
            ->setCode( $request->get( 'code' ) )
            ->setCustomerId( $customer_id )
            ->setNotes( $request->get( 'notes' ) );

        $payment_collection = $request->getCollection( 'payment' );
        $gift_card->setPaymentId( $payment_collection->get( 'payment_id' ) )->save();

        if ( $customer_id && $payment_collection->get( 'payment_action' ) === 'create' ) {
            $price = $payment_collection->get( 'payment_price', 0 );
            $payment = new Payment();
            $payment
                ->setType( Payment::TYPE_LOCAL )
                ->setStatus( Payment::STATUS_PENDING )
                ->setTax( $payment_collection->get( 'payment_tax' ) ?: 0 )
                ->setTotal( get_option( 'bookly_taxes_in_price' ) === 'excluded' ? $price + $payment->getTax() : $price )
                ->setPaid( 0 );

            $gift_card_type = Entities\GiftCardType::find( $gift_card->getGiftCardTypeId() );
            $gift_card_details = new \BooklyPro\Lib\DataHolders\Details\GiftCard( array(
                'id' => $gift_card->getId(),
                'code' => $gift_card->getCode(),
                'type_id' => $gift_card_type->getId(),
                'title' => $gift_card_type->getTitle(),
                'cost' => $payment->getTotal(),
                'tax' => $payment->getTax(),
            ) );
            $payment
                ->getDetailsData()
                ->addDetails( $gift_card_details )
                ->setCustomer( BooklyLib\Entities\Customer::find( $customer_id ) );
            $payment->save();

            $gift_card->setPaymentId( $payment->getId() )->save();
        }

        $send_notifications = $request->get( 'send_notifications', false );
        if ( $send_notifications ) {
            Lib\Notifications\NewGiftCard\Sender::send( $gift_card, $gift_card->getCustomerId(), $queue );
        }

        update_user_meta( get_current_user_id(), 'bookly_gift_card_form_send_notifications', $send_notifications );

        $response = array();
        if ( $queue ) {
            $db_queue = new BooklyLib\Entities\NotificationQueue();
            $db_queue
                ->setData( json_encode( array( 'all' => $queue ) ) )
                ->save();

            $response['queue'] = array( 'token' => $db_queue->getToken(), 'all' => $queue );
        }

        wp_send_json_success( $response );
    }

    /**
     * Generate code.
     */
    public static function generateGiftCardCode()
    {
        $mask = self::parameter( 'mask' );

        if ( $mask == '' ) {
            $mask = get_option( 'bookly_cloud_gift_default_code_mask' );
        }
        if ( $mask == '' ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a non empty mask.', 'bookly' ) ) );
        }

        try {
            $code = Lib\CodeGenerator::generateUniqueCode( '\BooklyPro\Lib\Entities\GiftCard', $mask );
            wp_send_json_success( compact( 'code' ) );
        } catch ( \Exception $e ) {
            wp_send_json_error( array( 'message' => __( 'All possible codes have already been generated for this mask.', 'bookly' ) ) );
        }
    }
}