<?php
namespace BooklyPro\Lib\Bbb;

use Bookly\Lib\Entities\Customer;

class BigBlueButton
{
    /** @var string */
    protected static $end_point;
    /** @var string */
    protected static $secret;

    /** @var string */
    protected $meeting_id;
    /** @var array */
    protected $errors = array();

    /**
     * @param string $meeting_id
     */
    public function __construct( $meeting_id )
    {
        $this->meeting_id = $meeting_id;
        if ( self::$end_point === null ) {
            self::$end_point = trim( get_option( 'bookly_bbb_server_end_point' ), '/' );
            self::$secret = get_option( 'bookly_bbb_shared_secret' );
        }
    }

    /**
     * Create online meeting
     *
     * @param string $meeting_title
     * @param string $moderator_pw
     * @param string $attendee_pw
     * @return bool
     */
    public function create( $meeting_title, $moderator_pw, $attendee_pw )
    {
        $url = $this->buildActionUrl( 'create', array(
            'meetingID' => $this->meeting_id,
            'name' => $meeting_title,
            'moderatorPW' => $moderator_pw,
            'attendeePW' => $attendee_pw,
        ) );

        $response = wp_remote_retrieve_body( wp_remote_get( $url ) );
        if ( strpos( $response, '<returncode>SUCCESS</returncode>' ) !== false ) {
            return true;
        }
        if ( function_exists( 'simplexml_load_string' ) ) {
            $xml = simplexml_load_string( $response );
            if ( property_exists( $xml, 'message' ) ) {
                $this->errors[] = (string) $xml->message;

                return false;
            }
        }

        $this->errors[] = __( 'Failed to create an online meeting', 'bookly' );

        return false;
    }

    /**
     * Get the moderator's bookly-url to create a meeting
     *
     * @param array $data
     * @return string
     */
    public function getCreateMeetingStaffUrl( $data )
    {
        return add_query_arg( array( 'action' => 'bookly_pro_bbb', 'meeting_id' => $this->meeting_id, 'token' => $data['staff_pw'] ), admin_url( 'admin-ajax.php' ) );
    }

    /**
     * Get the customer's bookly-url for meeting
     *
     * @param Customer|null $customer
     * @return string
     */
    public function getJoinMeetingClientUrl( $customer )
    {
        $args = array( 'action' => 'bookly_pro_bbb', 'meeting_id' => $this->meeting_id );
        if ( $customer ) {
            $args['email'] = $customer->getEmail();
        }

        return add_query_arg( $args, admin_url( 'admin-ajax.php' ) );
    }

    /**
     * Generate join url on BigBlueButton server
     *
     * @param string $name
     * @return string
     */
    public function getJoinMeetingRedirectUrl( $name, $password )
    {
        return $this->buildActionUrl( 'join', array(
            'meetingID' => $this->meeting_id,
            'password' => $password,
            'fullName' => $name,
        ) );
    }

    /**
     * Gets errors
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Build url for BigBlueButtons requests
     *
     * @param string $action
     * @param array $params
     * @return string
     */
    protected function buildActionUrl( $action, array $params )
    {
        $query = http_build_query( $params );

        return self::$end_point . '/api/' . $action . '?' . $query . '&checksum=' . $this->getCheckSum( $action . $query );
    }

    /**
     * BigBlueButton checksum for request
     *
     * @param string $value
     * @return string
     */
    protected function getCheckSum( $value )
    {
        return sha1( $value . self::$secret );
    }
}