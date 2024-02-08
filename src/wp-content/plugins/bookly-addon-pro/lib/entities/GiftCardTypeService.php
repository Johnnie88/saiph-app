<?php
namespace BooklyPro\Lib\Entities;

use Bookly\Lib as BooklyLib;

class GiftCardTypeService extends BooklyLib\Base\Entity
{
    /** @var  int */
    protected $gift_card_type_id = 0;
    /** @var  int  */
    protected $service_id = 0;

    protected static $table = 'bookly_gift_card_type_services';

    protected static $schema = array(
        'id' => array( 'format' => '%d' ),
        'gift_card_type_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'GiftCardType' ) ),
        'service_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'Service', 'namespace' => '\Bookly\Lib\Entities' ) ),
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
     * Gets service
     *
     * @return int
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * Sets service
     *
     * @param int $service_id
     * @return $this
     */
    public function setServiceId( $service_id )
    {
        $this->service_id = $service_id;

        return $this;
    }

}
