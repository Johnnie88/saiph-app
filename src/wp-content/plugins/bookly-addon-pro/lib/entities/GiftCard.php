<?php
namespace BooklyPro\Lib\Entities;

use Bookly\Lib as BooklyLib;

class GiftCard extends BooklyLib\Base\Entity
{
    const VALIDATION_EXPIRED = 'expired';
    const VALIDATION_INVALID = 'invalid';
    const VALIDATION_LOW_BALANCE = 'low_balance';
    const VALIDATION_NOT_FOUND = 'not_found';

    /** @var string */
    protected $code = '';
    /** @var int */
    protected $gift_card_type_id;
    /** @var int|null */
    protected $owner_id;
    /** @var float */
    protected $balance = 0;
    /** @var int|null */
    protected $customer_id;
    /** @var int|null */
    protected $payment_id;
    /** @var int|null */
    protected $order_id;
    /** @var string */
    protected $notes;

    protected static $table = 'bookly_gift_cards';

    protected static $schema = array(
        'id' => array( 'format' => '%d' ),
        'gift_card_type_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'GiftCardType' ) ),
        'owner_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'Customer', 'namespace' => '\Bookly\Lib\Entities' ) ),
        'code' => array( 'format' => '%s' ),
        'balance' => array( 'format' => '%f' ),
        'customer_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'Customer', 'namespace' => '\Bookly\Lib\Entities' ) ),
        'payment_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'Payment', 'namespace' => '\Bookly\Lib\Entities' ) ),
        'order_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'Order', 'namespace' => '\Bookly\Lib\Entities' ) ),
        'notes' => array( 'format' => '%s' ),
    );

    /**
     * @param float $amount
     * @return $this
     */
    public function charge( $amount )
    {
        $this->setBalance( $this->balance - $amount );

        return $this;
    }

    /**
     * Check if gift is valid for given customer.
     *
     * @param BooklyLib\Entities\Customer $customer
     * @return bool
     */
    public function validForCustomer( BooklyLib\Entities\Customer $customer )
    {
        return ! ( $this->customer_id > 0 ) || $customer->getId() == $this->customer_id;
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets code
     *
     * @param string $code
     * @return $this
     */
    public function setCode( $code )
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get gift card type
     *
     * @return int
     */
    public function getGiftCardTypeId()
    {
        return $this->gift_card_type_id;
    }

    /**
     * Set gift card type
     *
     * @param int $gift_card_type_id
     * @return GiftCard
     */
    public function setGiftCardTypeId( $gift_card_type_id )
    {
        $this->gift_card_type_id = $gift_card_type_id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * @param int|null $owner_id
     * @return GiftCard
     */
    public function setOwnerId( $owner_id )
    {
        $this->owner_id = $owner_id;

        return $this;
    }

    /**
     * Get balance
     *
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Set balance
     *
     * @param float $balance
     * @return $this
     */
    public function setBalance( $balance )
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * @param int $customer_id
     * @return GiftCard
     */
    public function setCustomerId( $customer_id )
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPaymentId()
    {
        return $this->payment_id;
    }

    /**
     * @param int|null $payment_id
     * @return GiftCard
     */
    public function setPaymentId( $payment_id )
    {
        $this->payment_id = $payment_id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * @param int|null $order_id
     * @return GiftCard
     */
    public function setOrderId( $order_id )
    {
        $this->order_id = $order_id;

        return $this;
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

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

}