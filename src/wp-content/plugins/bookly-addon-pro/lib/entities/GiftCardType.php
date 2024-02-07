<?php
namespace BooklyPro\Lib\Entities;

use Bookly\Lib as BooklyLib;

class GiftCardType extends BooklyLib\Base\Entity
{
    /** @var string */
    protected $title = '';
    /** @var float */
    protected $amount = 0;
    /** @var string */
    protected $start_date;
    /** @var string */
    protected $end_date;
    /** @var int */
    protected $min_appointments = 1;
    /** @var int */
    protected $max_appointments;
    /** @var int */
    protected $link_with_buyer = 0;
    /** @var string */
    protected $info;
    /** @var int */
    protected $wc_product_id = 0;
    /** @var string */
    protected $wc_cart_info_name;
    /** @var string */
    protected $wc_cart_info;

    protected static $table = 'bookly_gift_card_types';

    protected static $schema = array(
        'id' => array( 'format' => '%d' ),
        'title' => array( 'format' => '%s' ),
        'amount' => array( 'format' => '%f' ),
        'start_date' => array( 'format' => '%s' ),
        'end_date' => array( 'format' => '%s' ),
        'min_appointments' => array( 'format' => '%d' ),
        'max_appointments' => array( 'format' => '%d' ),
        'link_with_buyer' => array( 'format' => '%d' ),
        'info' => array( 'format' => '%s' ),
        'wc_product_id' => array( 'format' => '%d' ),
        'wc_cart_info_name' => array( 'format' => '%s' ),
        'wc_cart_info' => array( 'format' => '%s' ),
    );

    /**
     * Check if gift has started.
     *
     * @return bool
     */
    public function started()
    {
        if ( $this->start_date ) {
            $today = BooklyLib\Slots\DatePoint::now()->format( 'Y-m-d' );
            if ( $today < $this->start_date ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if gift is expired.
     *
     * @return bool
     */
    public function expired()
    {
        if ( $this->end_date ) {
            $today = BooklyLib\Slots\DatePoint::now()->format( 'Y-m-d' );
            if ( $today > $this->end_date ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if gift is valid for given cart item.
     *
     * @param BooklyLib\CartItem $cart_item
     * @return bool
     */
    public function validForCartItem( BooklyLib\CartItem $cart_item )
    {
        $gs = new GiftCardTypeService();
        if ( $gs->loadBy( array( 'gift_card_type_id' => $this->id, 'service_id' => $cart_item->getServiceId() ) ) ) {
            $gst = new GiftCardTypeStaff();

            return $gst->loadBy( array( 'gift_card_type_id' => $this->id, 'staff_id' => $cart_item->getStaffId() ) );
        }

        return false;
    }

    /**
     * Check if gift is valid for given cart.
     *
     * @param BooklyLib\Cart $cart
     * @return bool
     */
    public function validForCart( BooklyLib\Cart $cart )
    {
        $valid = false;

        $services = array();
        $cart_items = $cart->getItems();
        foreach ( $cart_items as $item ) {
            if ( $this->validForCartItem( $item ) ) {
                // Count each service in cart.
                $service_id = $item->getServiceId();
                if ( ! isset ( $services[ $service_id ] ) ) {
                    $services[ $service_id ] = 0;
                }
                ++$services[ $service_id ];
            }
        }

        if ( ! empty ( $services ) ) {
            // Find min and max count.
            $min_count = PHP_INT_MAX;
            $max_count = 0;
            foreach ( $services as $count ) {
                if ( $count < $min_count ) {
                    $min_count = $count;
                }
                if ( $count > $max_count ) {
                    $max_count = $count;
                }
            }
            if ( $min_count >= $this->min_appointments ) {
                if ( $this->max_appointments === null || $max_count <= $this->max_appointments ) {
                    $valid = true;
                }
            }
        }

        return $valid;
    }

    /**
     * Get translated title.
     *
     * @param string $locale
     * @return string
     */
    public function getTranslatedTitle( $locale = null )
    {
        return $this->getTitle() != ''
            ? BooklyLib\Utils\Common::getTranslatedString( 'gift_card_type_' . $this->getId(), $this->getTitle(), $locale )
            : '';
    }

    /**
     * Get translated Cart info
     *
     * @param string $locale
     * @return string
     */
    public function getTranslatedWCCartInfo( $locale = null )
    {
        return BooklyLib\Utils\Common::getTranslatedString( 'gift_card_type_' . $this->getId() . '_wc_cart_info', $this->getWCCartInfo(), $locale );
    }

    /**
     * Get translated info.
     *
     * @param string $locale
     * @return string
     */
    public function getTranslatedInfo( $locale = null )
    {
        return BooklyLib\Utils\Common::getTranslatedString( 'gift_card_type_' . $this->getId() . '_info', $this->getInfo(), $locale );
    }

    /**
     * Get translated Cart info name
     *
     * @param string $locale
     * @return string
     */
    public function getTranslatedWCCartInfoName( $locale = null )
    {
        return BooklyLib\Utils\Common::getTranslatedString( 'gift_card_type_' . $this->getId() . '_wc_cart_info_name', $this->getWCCartInfoName(), $locale );
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle( $title )
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setAmount( $amount )
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Gets start_date
     *
     * @return string
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Sets start_date
     *
     * @param string $start_date
     * @return $this
     */
    public function setStartDate( $start_date )
    {
        $this->start_date = $start_date;

        return $this;
    }

    /**
     * Gets end_date
     *
     * @return string
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * Sets end_date
     *
     * @param string $end_date
     * @return $this
     */
    public function setEndDate( $end_date )
    {
        $this->end_date = $end_date;

        return $this;
    }

    /**
     * Gets min_appointments
     *
     * @return int
     */
    public function getMinAppointments()
    {
        return $this->min_appointments;
    }

    /**
     * Sets min_appointments
     *
     * @param int $min_appointments
     * @return $this
     */
    public function setMinAppointments( $min_appointments )
    {
        $this->min_appointments = $min_appointments;

        return $this;
    }

    /**
     * Gets max_appointments
     *
     * @return int
     */
    public function getMaxNumberOfAppointments()
    {
        return $this->max_appointments;
    }

    /**
     * Sets max_appointments
     *
     * @param int $max_appointments
     * @return $this
     */
    public function setMaxNumberOfAppointments( $max_appointments )
    {
        $this->max_appointments = $max_appointments;

        return $this;
    }

    /**
     * @return int
     */
    public function getLinkWithBuyer()
    {
        return $this->link_with_buyer;
    }

    /**
     * @param int $link_with_buyer
     * @return $this
     */
    public function setLinkWithBuyer( $link_with_buyer )
    {
        $this->link_with_buyer = $link_with_buyer;

        return $this;
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param string $info
     * @return $this
     */
    public function setInfo( $info )
    {
        $this->info = $info;

        return $this;
    }

    /**
     * @return int
     */
    public function getWcProductId()
    {
        return $this->wc_product_id;
    }

    /**
     * @param int $wc_product_id
     * @return $this
     */
    public function setWcProductId( $wc_product_id )
    {
        $this->wc_product_id = $wc_product_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getWcCartInfoName()
    {
        return $this->wc_cart_info_name;
    }

    /**
     * @param string $wc_cart_info_name
     * @return $this
     */
    public function setWcCartInfoName( $wc_cart_info_name )
    {
        $this->wc_cart_info_name = $wc_cart_info_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getWcCartInfo()
    {
        return $this->wc_cart_info;
    }

    /**
     * @param string $wc_cart_info
     * @return $this
     */
    public function setWcCartInfo( $wc_cart_info )
    {
        $this->wc_cart_info = $wc_cart_info;

        return $this;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    /**
     * @inerhitDoc
     */
    public function save()
    {

        $return = parent::save();
        if ( $this->isLoaded() ) {
            // Register string for translate in WPML.
            do_action( 'wpml_register_single_string', 'bookly', 'gift_card_type_' . $this->getId(), $this->getTitle() );
            do_action( 'wpml_register_single_string', 'bookly', 'gift_card_type_' . $this->getId() . '_info', $this->getInfo() );
            do_action( 'wpml_register_single_string', 'bookly', 'gift_card_type_' . $this->getId() . '_wc_cart_info_name', $this->getWCCartInfoName() );
            do_action( 'wpml_register_single_string', 'bookly', 'gift_card_type_' . $this->getId() . '_wc_cart_info', $this->getWCCartInfo() );
        }

        return $return;
    }
}