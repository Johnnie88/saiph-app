<?php
namespace BooklyPro\Lib\Entities;

use Bookly\Lib as BooklyLib;

class GiftCardTypeStaff extends BooklyLib\Base\Entity
{
    /** @var  int */
    protected $gift_card_type_id = 0;
    /** @var  int  */
    protected $staff_id = 0;

    protected static $table = 'bookly_gift_card_type_staff';

    protected static $schema = array(
        'id' => array( 'format' => '%d' ),
        'gift_card_type_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'GiftCardType' ) ),
        'staff_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'Staff', 'namespace' => '\Bookly\Lib\Entities' ) ),
    );

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets gift card type
     *
     * @return int
     */
    public function getGiftCardTypeId()
    {
        return $this->gift_card_type_id;
    }

    /**
     * Sets gift card type
     *
     * @param int $gift_card_type_id
     * @return $this
     */
    public function setGiftCardTypeId( $gift_card_type_id )
    {
        $this->gift_card_type_id = $gift_card_type_id;

        return $this;
    }

    /**
     * Gets staff
     *
     * @return int
     */
    public function getStaffId()
    {
        return $this->staff_id;
    }

    /**
     * Sets staff
     *
     * @param int $staff_id
     * @return $this
     */
    public function setStaffId( $staff_id )
    {
        $this->staff_id = $staff_id;

        return $this;
    }

}
