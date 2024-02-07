<?php
namespace BooklyPro\Lib\Notifications\Assets\NewWpUser\Staff;

use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Notifications\Assets\Base;
use Bookly\Lib\Utils\Common;

/**
 * @property string $staff_email
 * @property string $staff_info
 * @property string $staff_name
 * @property string $staff_phone
 * @property string $site_address
 */
class Codes extends Base\Codes
{
    // Core
    public $new_username;
    public $new_password;

    /** @var Staff */
    protected $staff;

    /**
     * Constructor.
     *
     * @param Staff $staff
     * @param string $username
     * @param string $password
     */
    public function __construct( Staff $staff, $username, $password )
    {
        $this->staff = $staff;
        $this->new_username = $username;
        $this->new_password = $password;
    }

    /**
     * @inheritDoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );
        $staff_photo = $this->staff->getImageUrl();
        if ( $format === 'html' ) {
            $staff_photo = Common::getImageTag( $staff_photo, $this->staff->getFullName() );
        }

        // Add replace codes.
        $replace_codes += array(
            'new_password' => $this->new_password,
            'new_username' => $this->new_username,
            'site_address' => site_url(),
            'staff_email' => $this->staff->getEmail(),
            'staff_info' => $format == 'html' ? nl2br( $this->staff->getInfo() ) : $this->staff->getInfo(),
            'staff_name' => $this->staff->getFullName(),
            'staff_phone' => $this->staff->getPhone(),
            'staff_photo' => $staff_photo,
        );

        return $replace_codes;
    }

}