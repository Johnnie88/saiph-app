<?php
namespace BooklyPro\Lib\DataHolders\Details;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\DataHolders\Booking\Item;
use Bookly\Lib\DataHolders\Details;
use BooklyPro\Lib\Entities;
use BooklyPro\Lib\CodeGenerator;
use Bookly\Lib\Entities\Payment;

class GiftCard extends Details\Base
{
    protected $type = Payment::ITEM_GIFT_CARD;

    protected $fields = array(
        'id',
        'code',
        'type_id',
        'title',
        'cost',
        'tax',
        'notes',
    );

    /**
     * @param \BooklyPro\Lib\DataHolders\Booking\GiftCard $item
     * @return void
     */
    protected function setItem( Item $item )
    {
        $card_type = Entities\GiftCardType::find( $item->getGiftCardTypeId() );
        $this->price = $card_type->getAmount();
        $this->deposit = 0;

        $this->setData( array(
            'type_id' => $card_type->getId(),
            'cost' => $card_type->getAmount(),
            'title' => $card_type->getTitle(),
            'tax' => 0,
            'notes' => $item->getNotes()
        ) );
    }

    /**
     * @param BooklyLib\Entities\Payment $payment
     * @return Entities\GiftCard|void
     */
    public function createGiftCard( BooklyLib\Entities\Payment $payment )
    {
        $type = Entities\GiftCardType::find( $this->getValue( 'type_id' ) );
        if ( $type ) {
            $gift_card = new Entities\GiftCard();
            $gift_card
                ->setGiftCardTypeId( $type->getId() )
                ->setOwnerId( $payment->getDetailsData()->getValue( 'customer_id' ) )
                ->setBalance( $type->getAmount() )
                ->setCode( CodeGenerator::generateUniqueCode( '\BooklyPro\Lib\Entities\GiftCard', get_option( 'bookly_cloud_gift_default_code_mask', 'GIFT-****' ) ) )
                ->setPaymentId( $payment->getId() )
                ->setOrderId( $payment->getOrderId() )
                ->setNotes( $this->getValue( 'notes' ) )
                ->setCustomerId( $type->getLinkWithBuyer() ? $payment->getDetailsData()->getValue( 'customer_id' ) : null )
                ->save();
            $this->setData( array(
                'id' => $gift_card->getId(),
                'code' => $gift_card->getCode(),
                'cost' => $type->getAmount(),
            ) );

            return $gift_card;
        }
    }
}