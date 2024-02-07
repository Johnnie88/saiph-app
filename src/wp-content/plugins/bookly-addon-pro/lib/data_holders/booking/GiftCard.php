<?php
namespace BooklyPro\Lib\DataHolders\Booking;

use Bookly\Lib as BooklyLib;

class GiftCard extends BooklyLib\DataHolders\Booking\Item
{
    protected $type = BooklyLib\DataHolders\Booking\Item::TYPE_GIFT_CARD;
    /** @var int */
    protected $gift_card_type_id;
    /** @var string */
    protected $notes;

    /**
     * @param $gift_card_id
     * @return $this
     */
    public function setGiftCardType( $gift_card_id )
    {
        $this->gift_card_type_id = $gift_card_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getGiftCardTypeId()
    {
        return $this->gift_card_type_id;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     * @return GiftCard
     */
    public function setNotes( $notes )
    {
        $this->notes = $notes;

        return $this;
    }

    public function getAppointment()
    {
    }

    public function getCA()
    {
    }

    public function getDeposit()
    {
    }

    public function getExtras()
    {
    }

    public function getService()
    {
    }

    public function getServiceDuration()
    {
    }

    public function getServicePrice()
    {
    }

    public function getStaff()
    {
    }

    public function getTax()
    {
    }

    public function getServiceTax()
    {
    }

    public function getTotalEnd()
    {
    }

    public function getTotalPrice()
    {
    }

    public function getItems()
    {
    }

    public function setStatus( $status )
    {
    }
}