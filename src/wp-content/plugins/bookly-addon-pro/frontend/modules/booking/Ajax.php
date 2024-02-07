<?php
namespace BooklyPro\Frontend\Modules\Booking;

use Bookly\Frontend\Components\Booking\InfoText;
use Bookly\Frontend\Modules\Booking\Lib\Steps;
use Bookly\Lib as BooklyLib;
use Bookly\Lib\UserBookingData;
use Bookly\Frontend\Modules\Booking\Lib\Errors;
use BooklyPro\Lib\Bbb\BigBlueButton;
use BooklyPro\Lib\Utils\Common;

class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'anonymous' );
    }

    /**
     * Apply tips
     */
    public static function applyTips()
    {
        $userData = new UserBookingData( self::parameter( 'form_id' ) );

        if ( $userData->load() && get_option( 'bookly_app_show_tips' ) ) {

            $tips = self::parameter( 'tips' );
            if ( $tips >= 0 ) {
                $userData->setTips( $tips );
                $response = array( 'success' => true );
            } else {
                $response = array(
                    'success' => false,
                    'error' => BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_tips_error' ),
                );
            }
            $userData->sessionSave();

            // Output JSON response.
            wp_send_json( $response );
        }

        Errors::sendSessionError();
    }

    /**
     * Log in with Facebook.
     */
    public static function facebookLogin()
    {
        if ( get_current_user_id() ) {
            // Do nothing for logged in users.
            wp_send_json( array( 'success' => false, 'error' => Errors::ALREADY_LOGGED_IN ) );
        }

        $facebook_id = self::parameter( 'id' );

        $userData = new BooklyLib\UserBookingData( self::parameter( 'form_id' ) );

        if ( $userData->load() ) {
            $customer = new BooklyLib\Entities\Customer();
            if ( $customer->loadBy( array( 'facebook_id' => $facebook_id ) ) ) {
                $user_info = array(
                    'email' => $customer->getEmail(),
                    'full_name' => $customer->getFullName(),
                    'first_name' => $customer->getFirstName(),
                    'last_name' => $customer->getLastName(),
                    'phone' => $customer->getPhone(),
                    'country' => $customer->getCountry(),
                    'state' => $customer->getState(),
                    'postcode' => $customer->getPostcode(),
                    'city' => $customer->getCity(),
                    'street' => $customer->getStreet(),
                    'street_number' => $customer->getStreetNumber(),
                    'additional_address' => $customer->getAdditionalAddress(),
                    'birthday' => $customer->getBirthday(),
                    'info_fields' => json_decode( $customer->getInfoFields() ),
                );
            } else {
                $user_info = array(
                    'email' => self::parameter( 'email' ),
                    'full_name' => self::parameter( 'name' ),
                    'first_name' => self::parameter( 'first_name' ),
                    'last_name' => self::parameter( 'last_name' ),
                );
            }
            $userData->fillData( $user_info + array( 'facebook_id' => $facebook_id ) );
            $response = array(
                'success' => true,
                'data' => $user_info,
            );
            $userData->sessionSave();

            // Output JSON response.
            wp_send_json( $response );
        }

        Errors::sendSessionError();
    }

    /**
     * Cancel appointments using token.
     */
    public static function cancelAppointments()
    {
        $token = self::parameter( 'token' );
        $succeed = false;
        if ( $token !== null ) {
            $customer_appointments = BooklyLib\Entities\CustomerAppointment::query( 'ca' )
                ->leftJoin( 'Order', 'o', 'o.id = ca.order_id' )
                ->where( 'o.token', $token )
                ->find();

            /** @var BooklyLib\Entities\CustomerAppointment $customer_appointment */
            foreach ( $customer_appointments as $customer_appointment ) {
                if ( $customer_appointment->cancelAllowed() ) {
                    $customer_appointment->cancel();
                    $succeed = true;
                }
            }
        }

        BooklyLib\Utils\Common::cancelAppointmentRedirect( $succeed );
    }

    /**
     * Create BigBlueButton online meeting
     *
     * @return void
     */
    public static function bbb()
    {
        $meeting_id = self::parameter( 'meeting_id' );
        $token = self::parameter( 'token' );
        $errors = array();
        if ( $meeting_id ) {
            $row = BooklyLib\Entities\Appointment::query( 'a' )
                ->select( 'a.online_meeting_data, st.full_name AS moderator_name, s.title AS service_name' )
                ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' )
                ->leftJoin( 'Service', 's', 's.id = a.service_id' )
                ->where( 'a.online_meeting_provider', 'bbb' )
                ->where( 'a.online_meeting_id', $meeting_id )
                ->fetchRow();
            if ( $row ) {
                $data = json_decode( $row['online_meeting_data'], true );
                $moderator_pw = isset( $data['staff_pw'] ) ? $data['staff_pw'] : '';
                $attendee_pw = isset( $data['client_pw'] ) ? $data['client_pw'] : '';
                $bbb = new BigBlueButton( $meeting_id );
                if ( $bbb->create( $row['service_name'], $moderator_pw, $attendee_pw ) ) {
                    if ( $token == $moderator_pw ) {
                        // Staff
                        $name = $row['moderator_name'];
                        $password = $moderator_pw;
                    } else {
                        // Client
                        /** @var BooklyLib\Entities\Customer $customer */
                        $customer = BooklyLib\Entities\Customer::query()
                            ->where( 'email', self::parameter( 'email' ) )
                            ->findOne();
                        $name = $customer ? $customer->getFullName() : 'User-' . mt_rand( 100, 999 );
                        $password = $attendee_pw;
                    }

                    $url = $bbb->getJoinMeetingRedirectUrl( $name, $password );
                    BooklyLib\Utils\Common::redirect( $url );
                } else {
                    $errors = $bbb->errors();
                }
            } else {
                $errors[] = __( 'Invalid online meeting url', 'bookly' );
            }
        } else {
            $errors[] = __( 'Invalid online meeting url', 'bookly' );
        }

        echo implode( '<br>', $errors );
        exit;
    }

    /**
     * @return void
     */
    public static function applyGiftCard()
    {
        $userData = new BooklyLib\UserBookingData( self::parameter( 'form_id' ) );

        if ( $userData->load() ) {
            try {
                Common::validateGiftCard( self::parameter( 'gift_card' ), $userData );
                $userData->setGiftCode( self::parameter( 'gift_card' ) );
                $userData->sessionSave();
                wp_send_json_success();
            } catch ( \LogicException $e ) {
                $error = BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_cloud_gift_error_' . $e->getMessage() );
                // Gift card is invalid.
                $userData->setGiftCode( null );
                $userData->sessionSave();

                // Output JSON response.
                wp_send_json( array(
                    'success' => false,
                    'error' => $error,
                    'text' => InfoText::prepare( Steps::PAYMENT, '', $userData ),
                ) );
            }
        }

        Errors::sendSessionError();
    }

    /**
     * Override parent method to exclude actions from CSRF token verification.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        $excluded_actions = array(
            'cancelAppointments',
            'bbb',
        );

        return in_array( $action, $excluded_actions ) || parent::csrfTokenValid( $action );
    }
}