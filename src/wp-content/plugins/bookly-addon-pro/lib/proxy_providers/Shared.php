<?php
namespace BooklyPro\Lib\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Appointment;
use Bookly\Lib\Entities\Notification;
use Bookly\Lib\Entities\Payment;
use Bookly\Lib\Slots\DatePoint;
use Bookly\Lib\Utils\Common;
use BooklyPro\Backend\Components\License;
use BooklyPro\Lib\Bbb\BigBlueButton;
use BooklyPro\Lib\Config;
use BooklyPro\Lib\Zoom;
use BooklyPro\Lib\Entities;

class Shared extends BooklyLib\Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function doDailyRoutine()
    {
        // Grace routine.
        $remaining_days = Config::graceRemainingDays();
        if ( $remaining_days !== false ) {
            $today = (int) ( current_time( 'timestamp' ) / DAY_IN_SECONDS );
            $grace_notifications = get_option( 'bookly_grace_notifications' );
            if ( $today != $grace_notifications['sent'] ) {
                $admin_emails = Common::getAdminEmails();
                if ( ! empty ( $admin_emails ) ) {
                    $grace_notifications['sent'] = $today;
                    if ( $remaining_days === 0 && ( $grace_notifications['bookly'] != 1 ) ) {
                        $subject = __( 'Please verify your Bookly Pro license', 'bookly' );
                        $message = __( 'Bookly Pro will need to verify your license to restore access to your bookings. Please enter the purchase code in the administrative panel.', 'bookly' );
                        foreach ( $admin_emails as $email ) {
                            if ( BooklyLib\Utils\Mail::send( $email, $subject, $message ) ) {
                                $grace_notifications['bookly'] = 1;
                                update_option( 'bookly_grace_notifications', $grace_notifications );
                            }
                        }
                    } elseif ( in_array( $remaining_days, array( 13, 7, 1 ) ) ) {
                        $days_text = sprintf( _n( '%d day', '%d days', $remaining_days, 'bookly' ), $remaining_days );
                        $replace = array( '{days}' => $days_text, '{site_url}' => site_url() );
                        $subject = __( 'Please verify your Bookly Pro license', 'bookly' );
                        $message = strtr( __( 'Please verify Bookly Pro license in the administrative panel on {site_url}.', 'bookly' ) . ' ' . __( 'If you do not verify the license within {days}, access to your bookings will be disabled.', 'bookly' ), $replace );
                        foreach ( $admin_emails as $email ) {
                            if ( BooklyLib\Utils\Mail::send( $email, $subject, $message ) ) {
                                update_option( 'bookly_grace_notifications', $grace_notifications );
                            }
                        }
                    }
                }
            }
        }

        if ( get_option( 'bookly_pr_show_time' ) < time() ) {
            update_option( 'bookly_pr_show_time', time() + 7776000 );
            if ( get_option( 'bookly_pro_envato_purchase_code' ) == '' ) {
                foreach ( get_users( array( 'role' => 'administrator' ) ) as $admin ) {
                    update_user_meta( $admin->ID, 'bookly_show_purchase_reminder', '1' );
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function doHourlyRoutine()
    {
        if ( get_option( 'bookly_auto_change_status' ) ) {
            $statuses = BooklyLib\Entities\CustomerAppointment::getStatuses();
            $status_from = get_option( 'bookly_auto_change_status_from' );
            $status_to = get_option( 'bookly_auto_change_status_to' );
            if ( in_array( $status_from, $statuses, true ) && in_array( $status_to, $statuses, true ) ) {
                BooklyLib\Entities\CustomerAppointment::query( 'ca' )
                    ->update()
                    ->set( 'status', $status_to )
                    ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
                    ->where( 'status', $status_from )
                    ->whereRaw( 'DATE_ADD(a.end_date, INTERVAL a.extras_duration SECOND) < \'%s\'', array( current_time( 'mysql' ) ) )
                    ->execute();
            }
        }

        Entities\EmailLog::query()->delete()
            ->whereRaw( 'created_at < DATE(NOW() - INTERVAL %s DAY)', array( get_option( 'bookly_email_logs_expire', 30 ) ) )
            ->execute();
    }

    /**
     * @inheritDoc
     */
    public static function renderAdminNotices( $bookly_page )
    {
        License\Components::renderLicenseRequired( $bookly_page );
        License\Components::renderLicenseNotice( $bookly_page );
        License\Components::renderPurchaseReminder( $bookly_page );
    }

    /**
     * @inheritDoc
     */
    public static function prepareCaSeStQuery( BooklyLib\Query $query )
    {
        if ( ! BooklyLib\Config::customerGroupsActive() ) {
            $query->where( 's.visibility', BooklyLib\Entities\Service::VISIBILITY_PUBLIC );
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public static function prepareStaffServiceQuery( BooklyLib\Query $query )
    {
        $query
            ->addSelect( 'spo.position' )
            ->leftJoin( 'StaffPreferenceOrder', 'spo', 'spo.service_id = ss.service_id AND spo.staff_id = ss.staff_id', '\BooklyPro\Lib\Entities' );

        return $query;
    }

    /**
     * @inheritDoc
     */
    public static function prepareStatement( $value, $statement, $table )
    {
        $tables = array( 'Service', 'Staff' );
        $key = $table . '-' . $statement;
        if ( in_array( $table, $tables ) ) {
            if ( ! self::hasInCache( $key ) ) {
                preg_match( '/(?:(\w+)\()?\W*(?:(\w+)\.(\w+)|(\w+))/', $statement, $match );

                $count = count( $match );
                if ( $count == 4 ) {
                    $field = $match[3];
                } elseif ( $count == 5 ) {
                    $field = $match[4];
                }

                switch ( $field ) {
                    case 'category_id':
                    case 'padding_left':
                    case 'padding_right':
                    case 'staff_preference':
                    case 'staff_preference_settings':
                        self::putInCache( $key, $statement );
                        break;
                }
            }
        } else {
            self::putInCache( $key, $value );
        }

        return self::getFromCache( $key );
    }

    /**
     * @inheritDoc
     */
    public static function prepareNotificationTypes( array $types, $gateway )
    {
        if ( $gateway == 'email' ) {
            $types[] = Notification::TYPE_APPOINTMENT_REMINDER;
            $types[] = Notification::TYPE_LAST_CUSTOMER_APPOINTMENT;
            $types[] = Notification::TYPE_STAFF_DAY_AGENDA;
        }
        $types[] = Notification::TYPE_NEW_BOOKING_COMBINED;
        $types[] = Notification::TYPE_CUSTOMER_BIRTHDAY;
        $types[] = Notification::TYPE_CUSTOMER_NEW_WP_USER;
        $types[] = Notification::TYPE_STAFF_NEW_WP_USER;

        if ( Config::giftCardsActive() ) {
            $types[] = Notification::TYPE_NEW_GIFT_CARD;
        }

        return $types;
    }

    /**
     * @inheritDoc
     */
    public static function prepareTableColumns( $columns, $table )
    {
        switch ( $table ) {
            case BooklyLib\Utils\Tables::APPOINTMENTS:
                $columns['customer_address'] = esc_attr__( 'Customer address', 'bookly' );
                $columns['customer_birthday'] = esc_attr__( 'Customer birthday', 'bookly' );
                $columns['online_meeting'] = esc_attr__( 'Online meeting', 'bookly' );
                break;

            case BooklyLib\Utils\Tables::CUSTOMERS:
                $columns['address'] = esc_attr( BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_address' ) );
                $columns['facebook'] = 'Facebook';
                break;

            case BooklyLib\Utils\Tables::SERVICES:
                $columns['online_meetings'] = esc_attr__( 'Online meetings', 'bookly' );
                break;

            case BooklyLib\Utils\Tables::STAFF_MEMBERS:
                $columns['category_name'] = esc_attr__( 'Category', 'bookly' );
                break;

            case BooklyLib\Utils\Tables::GIFT_CARDS:
                $columns['id'] = esc_html__( 'ID', 'bookly' );
                $columns['code'] = esc_html__( 'Code', 'bookly' );
                $columns['type'] = esc_html__( 'Type', 'bookly' );
                $columns['balance'] = esc_html__( 'Balance', 'bookly' );
                $columns['customer'] = esc_html__( 'Customer', 'bookly' );
                $columns['payment'] = esc_html__( 'Payment', 'bookly' );
                $columns['notes'] = esc_html__( 'Notes', 'bookly' );
                break;

            case BooklyLib\Utils\Tables::GIFT_CARD_TYPES:
                $columns['id'] = esc_html__( 'ID', 'bookly' );
                $columns['title'] = esc_html__( 'Title', 'bookly' );
                $columns['amount'] = esc_html__( 'Amount', 'bookly' );
                $columns['services'] = esc_html__( 'Services', 'bookly' );
                $columns['staff'] = esc_html__( 'Staff', 'bookly' );
                $columns['start_date'] = esc_html__( 'Active from', 'bookly' );
                $columns['end_date'] = esc_html__( 'Active until', 'bookly' );
                $columns['min_appointments'] = esc_html__( 'Min. appointments', 'bookly' );
                $columns['max_appointments'] = esc_html__( 'Max. appointments', 'bookly' );
                $columns['link_with_buyer'] = esc_html__( 'Link with customer', 'bookly' );
                break;
        }

        return $columns;
    }

    /**
     * @inheritDoc
     */
    public static function buildOnlineMeetingUrl( $default, Appointment $appointment, $customer = null )
    {
        switch ( $appointment->getOnlineMeetingProvider() ) {
            case 'zoom':
                $default = 'https://zoom.us/j/' . $appointment->getOnlineMeetingId();
                break;
            case 'google_meet':
            case 'jitsi':
                $default = $appointment->getOnlineMeetingId();
                break;
            case 'bbb':
                $bbb = new BigBlueButton( $appointment->getOnlineMeetingId() );
                $default = $bbb->getJoinMeetingClientUrl( $customer );
                break;
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public static function buildOnlineMeetingPassword( $default, Appointment $appointment )
    {
        if ( $appointment->getOnlineMeetingProvider() == 'zoom' ) {
            $options = json_decode( $appointment->getOnlineMeetingData() ?: '{}', true );

            return isset( $options['password'] ) ? $options['password'] : $default;
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public static function buildOnlineMeetingStartUrl( $default, Appointment $appointment )
    {
        switch ( $appointment->getOnlineMeetingProvider() ) {
            case 'zoom':
                $options = json_decode( $appointment->getOnlineMeetingData() ?: '{}', true );

                $default = isset( $options['start_url'] ) ? $options['start_url'] : self::buildOnlineMeetingUrl( $default, $appointment, null );
                break;
            case 'google_meet':
            case 'jitsi':
                $default = self::buildOnlineMeetingUrl( $default, $appointment, null );
                break;
            case 'bbb':
                $bbb = new BigBlueButton( $appointment->getOnlineMeetingId() );
                $default = $bbb->getCreateMeetingStaffUrl( json_decode( $appointment->getOnlineMeetingData(), true ) );
                break;
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public static function buildOnlineMeetingJoinUrl( $default, Appointment $appointment, $customer )
    {
        switch ( $appointment->getOnlineMeetingProvider() ) {
            case 'zoom':
                $options = json_decode( $appointment->getOnlineMeetingData() ?: '{}', true );

                $default = isset( $options['join_url'] ) ? $options['join_url'] : self::buildOnlineMeetingUrl( $default, $appointment, null );
                break;
            case 'google_meet':
            case 'jitsi':
                $default = $appointment->getOnlineMeetingId();
                break;
            case 'bbb':
                $bbb = new BigBlueButton( $appointment->getOnlineMeetingId() );
                $default = $bbb->getJoinMeetingClientUrl( $customer );
                break;
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public static function syncOnlineMeeting( array $errors, Appointment $appointment, $service )
    {
        if ( $service && $appointment->getStartDate() ) {
            // Zoom.
            if ( $service->getOnlineMeetings() == 'zoom' ) {
                $start = DatePoint::fromStr( $appointment->getStartDate() );
                $end = DatePoint::fromStr( $appointment->getEndDate() );
                $duration = $end->diff( $start ) + $appointment->getExtrasDuration();

                $zoom = new Zoom\Meetings( BooklyLib\Entities\Staff::find( $appointment->getStaffId() ) );
                $data = array(
                    'topic' => $service->getTitle(),
                    'start_time' => $start->toTz( 'UTC' )->format( 'Y-m-d\TH:i:s\Z' ),
                    'duration' => (int) ( $duration / 60 ),  // duration in minutes
                );
                if ( $appointment->getOnlineMeetingId() != '' ) {
                    $res = $zoom->update( $appointment->getOnlineMeetingId(), $data );
                } else {
                    $res = $zoom->create( $data );
                    if ( $res ) {
                        $appointment
                            ->setOnlineMeetingProvider( 'zoom' )
                            ->setOnlineMeetingId( $res['id'] )
                            ->setOnlineMeetingData( json_encode( $res ) )
                            ->save();
                    }
                }

                if ( ! $res ) {
                    $errors = array_merge( $errors, array_map( function ( $e ) {
                        return 'Zoom: ' . $e;
                    }, $zoom->errors() ) );
                }
            } elseif ( $service->getOnlineMeetings() == 'jitsi' ) {
                if ( ! $appointment->getOnlineMeetingId() ) {
                    $token = md5( uniqid( time(), true ) );
                    $url = sprintf(
                        '%s/%s-%s-%s',
                        get_option( 'bookly_jitsi_server' ),
                        substr( $token, 0, 3 ),
                        substr( $token, 3, 4 ),
                        substr( $token, 7, 3 )
                    );
                    $appointment
                        ->setOnlineMeetingProvider( 'jitsi' )
                        ->setOnlineMeetingId( $url )
                        ->save();
                }
            } elseif ( $service->getOnlineMeetings() == 'bbb' ) {
                if ( ! $appointment->getOnlineMeetingId() ) {
                    $appointment
                        ->setOnlineMeetingProvider( 'bbb' )
                        ->setOnlineMeetingId( BooklyLib\Utils\Common::generateToken( get_class( $appointment ), 'online_meeting_id' ) )
                        ->setOnlineMeetingData( json_encode( array(
                            'staff_pw' => wp_generate_password( 8, false ),
                            'client_pw' => wp_generate_password( 8, false ),
                        ) ) )
                        ->save();
                }
            }
        }

        return $errors;
    }

    /**
     * @inheritDoc
     */
    public static function prepareAppointmentCodes( $codes, $appointment )
    {
        $customer = new BooklyLib\Entities\Customer();
        $customer->setFullName( 'Client' );
        $codes['online_meeting_url'] = BooklyLib\Proxy\Shared::buildOnlineMeetingUrl( '', $appointment, $customer );
        $codes['online_meeting_password'] = BooklyLib\Proxy\Shared::buildOnlineMeetingPassword( '', $appointment );
        $codes['online_meeting_start_url'] = BooklyLib\Proxy\Shared::buildOnlineMeetingStartUrl( '', $appointment );
        $codes['online_meeting_join_url'] = BooklyLib\Proxy\Shared::buildOnlineMeetingJoinUrl( '', $appointment, $customer );
        $codes['on_waiting_list'] = BooklyLib\Config::waitingListActive()
            ? BooklyLib\Entities\CustomerAppointment::query( 'ca' )
                ->where( 'ca.appointment_id', $appointment->getId() )
                ->where( 'status', BooklyLib\Entities\CustomerAppointment::STATUS_WAITLISTED )
                ->count()
            : 0;

        $staff = BooklyLib\Entities\Staff::find( $appointment->getStaffId() );
        $staff_category = $staff->getCategoryId() ? Entities\StaffCategory::find( $staff->getCategoryId() ) : null;
        $codes['staff_category_name'] = $staff_category ? $staff_category->getTranslatedName() : '';
        $codes['staff_category_info'] = $staff_category ? $staff_category->getTranslatedInfo() : '';
        $codes['staff_category_image'] = ( $staff_category && ( $url = $staff_category->getImageUrl() ) ) ? '<img src="' . $url . '"/>' : '';

        return $codes;
    }

    /**
     * @inheritDoc
     */
    public static function prepareCustomerAppointmentCodes( $codes, $customer_appointment, $format )
    {
        $customer = BooklyLib\Entities\Customer::find( $customer_appointment->getCustomerId() );
        $codes['status'] = BooklyLib\Entities\CustomerAppointment::statusToString( $customer_appointment->getStatus() );
        $codes['client_address'] = $customer->getAddress();
        $codes['client_full_birthday'] = $customer->getBirthday() ? BooklyLib\Utils\DateTime::formatDate( $customer->getBirthday() ) : '';
        $codes['client_birthday'] = $customer->getBirthday() ? date_i18n( 'F j', strtotime( $customer->getBirthday() ) ) : '';

        return $codes;
    }

    /**
     * @inheritDoc
     */
    public static function prepareL10nGlobal( array $obj )
    {
        $plugins = apply_filters( 'bookly_plugins', array() );
        unset ( $plugins['bookly-responsive-appointment-booking-tool'] );
        foreach ( array_keys( $plugins ) as $addon ) {
            $obj['addons'][] = substr( $addon, 13 );
        }

        return $obj;
    }

    /**
     * @inheritDoc
     */
    public static function prepareNotificationTitles( array $titles )
    {
        $titles['new_booking_combined'] = __( 'New booking combined notification', 'bookly' );
        $titles['customer_new_wp_user'] = __( 'New customer\'s WordPress user login details', 'bookly' );
        $titles['staff_new_wp_user'] = __( 'New staff\'s WordPress user login details', 'bookly' );
        $titles['new_gift_card'] = __( 'Notification about new gift card creation', 'bookly' );

        return $titles;
    }

    /**
     * @inheritDoc
     */
    public static function prepareTableDefaultSettings( $columns, $table )
    {
        switch ( $table ) {
            case BooklyLib\Utils\Tables::GIFT_CARDS:
            case BooklyLib\Utils\Tables::GIFT_CARD_TYPES:
                $columns = array_merge( $columns, array(
                    'id' => false,
                ) );
                break;
        }

        return $columns;
    }

    /**
     * @inheritDoc
     */
    public static function preparePaymentDetails( $details )
    {
        $user_booking_data = $details->getCartInfo()->getUserData();
        if ( $gift_card = $user_booking_data->getGiftCard() ) {
            $details->setData( array(
                'gift_card' => array(
                    'id' => $gift_card->getId(),
                    'code' => $gift_card->getCode(),
                    'amount' => $user_booking_data->getGiftCardAmount(),
                ),
                'gift_card_id' => $details->getCartInfo()->getGiftCard()->getId()
            ) );
        }

        return $details;
    }

    /**
     * @inerhitDoc
     */
    public static function preparePaymentImage( $url, $gateway )
    {
        return $gateway === Payment::TYPE_PAYPAL
            ? plugins_url( 'frontend/resources/images/paypal.svg', \BooklyPro\Lib\Plugin::getMainFile() )
            : $url;
    }

    /**
     * @inerhitDoc
     */
    public static function saveUserBookingData( $userData )
    {
        if ( $gift_card = $userData->getGiftCard() ) {
            $amount = $userData->cart->getInfo()->getGiftCardDiscount();
            $gift_card->charge( $amount )->save();
            $userData->setGiftCardAmount( $amount );
        }
    }

    /**
     * @inerhitDoc
     */
    public static function prepareCartInfo( BooklyLib\CartInfo $cart_info, BooklyLib\CartItem $item )
    {
        if ( $item->getType() === BooklyLib\CartItem::TYPE_GIFT_CARD ) {
            $card_type = Entities\GiftCardType::find( $item->getCartTypeId() );
            if ( $card_type ) {
                $cart_info->setSubtotal( $cart_info->getSubtotal() + $card_type->getAmount() );
                $cart_info->setDeposit( $cart_info->getDeposit() + $card_type->getAmount() );
            }
        }
    }
}