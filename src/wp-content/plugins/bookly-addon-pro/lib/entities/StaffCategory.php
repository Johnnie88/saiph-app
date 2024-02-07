<?php
namespace BooklyPro\Lib\Entities;

use Bookly\Lib as BooklyLib;

class StaffCategory extends BooklyLib\Base\Entity
{
    /** @var  string */
    protected $name;
    /** @var  int */
    protected $attachment_id;
    /** @var  string */
    protected $info;
    /** @var  int */
    protected $position = 9999;

    protected static $table = 'bookly_staff_categories';

    protected static $schema = array(
        'id' => array( 'format' => '%d' ),
        'name' => array( 'format' => '%s' ),
        'attachment_id' => array( 'format' => '%d' ),
        'info' => array( 'format' => '%s' ),
        'position' => array( 'format' => '%d', 'sequent' => true ),
    );

    /**
     * @var BooklyLib\Entities\Staff[]
     */
    private $staff_list;

    /**
     * @param null $locale
     * @return string
     */
    public function getTranslatedName( $locale = null )
    {
        return BooklyLib\Utils\Common::getTranslatedString( 'staff_category_' . $this->getId(), $this->getName(), $locale );
    }

    /**
     * Get translated info.
     *
     * @param string $locale
     * @return string
     */
    public function getTranslatedInfo( $locale = null )
    {
        return BooklyLib\Utils\Common::getTranslatedString( 'staff_category_' . $this->getId() . '_info', $this->getInfo(), $locale );
    }

    /**
     * @param BooklyLib\Entities\Staff $staff
     */
    public function addStaff( BooklyLib\Entities\Staff $staff )
    {
        $this->staff_list[] = $staff;
    }

    /**
     * @return BooklyLib\Entities\Staff[]
     */
    public function getStaffList()
    {
        return $this->staff_list;
    }

    /**
     * Get image url
     * @param string $size
     *
     * @return string
     */
    public function getImageUrl( $size = 'full' )
    {
        return $this->attachment_id
            ? BooklyLib\Utils\Common::getAttachmentUrl( $this->attachment_id, $size )
            : '';
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets name
     *
     * @param string $name
     * @return $this
     */
    public function setName( $name )
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getAttachmentId()
    {
        return $this->attachment_id;
    }

    /**
     * @param int $attachment_id
     */
    public function setAttachmentId( $attachment_id )
    {
        $this->attachment_id = $attachment_id;

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
     * Gets position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition( $position )
    {
        $this->position = $position;

        return $this;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    public function save()
    {
        $return = parent::save();
        if ( $this->isLoaded() ) {
            // Register string for translate in WPML.
            do_action( 'wpml_register_single_string', 'bookly', 'staff_category_' . $this->getId(), $this->getName() );
            do_action( 'wpml_register_single_string', 'bookly', 'staff_category_' . $this->getId() . '_info', $this->getInfo() );
        }
        return $return;
    }

}
