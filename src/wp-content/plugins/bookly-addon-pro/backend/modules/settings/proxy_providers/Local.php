<?php
namespace BooklyPro\Backend\Modules\Settings\ProxyProviders;

use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Components\Settings\Inputs;
use Bookly\Backend\Components\Settings\Menu;
use Bookly\Backend\Modules\Settings\Proxy;
use Bookly\Lib\Utils;
use Bookly\Lib as BooklyLib;

class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function renderFinalStepUrl()
    {
        self::renderTemplate( 'final_step_url' );
    }

    /**
     * @inheritDoc
     */
    public static function renderCancellationConfirmationUrl()
    {
        Inputs::renderText( 'bookly_url_cancel_confirm_page_url', __( 'Appointment cancellation confirmation URL', 'bookly' ), __( 'Set the URL of an appointment cancellation confirmation page that is shown to clients when they press cancellation link.', 'bookly' ) );
    }

    /**
     * @inheritDoc
     */
    public static function renderCustomersAddress()
    {
        self::renderTemplate( 'customers_address' );
    }

    /**
     * @inheritDoc
     */
    public static function renderCustomersLimitStatuses()
    {
        $statuses = array();
        foreach ( BooklyLib\Entities\CustomerAppointment::getStatuses() as $status ) {
            $statuses[] = array( $status, BooklyLib\Entities\CustomerAppointment::statusToString( $status ) );
        }
        Selects::renderMultiple( 'bookly_cst_limit_statuses', __( 'Do not count appointments in \'Limit appointments per customer\' with the following statuses', 'bookly' ), null, $statuses );
    }

    /**
     * @inheritDoc
     */
    public static function renderCustomersAddressTemplate()
    {
        self::renderTemplate( 'customers_address_template' );
    }

    /**
     * @inheritDoc
     */
    public static function renderCustomersBirthday()
    {
        Selects::renderSingle(
            'bookly_cst_required_birthday',
            __( 'Make birthday mandatory', 'bookly' ),
            __( 'If enabled, a customer will be required to enter a date of birth to proceed with a booking.', 'bookly' )
        );
    }

    /**
     * @inheritDoc
     */
    public static function renderMinimumTimeRequirement()
    {
        $values = array(
            'bookly_gen_min_time_prior_booking' => array( array( '0', __( 'Disabled', 'bookly' ) ) ),
            'bookly_gen_min_time_prior_cancel'  => array( array( '0', __( 'Disabled', 'bookly' ) ) ),
        );
        foreach ( array_merge( array( 0.5 ), range( 1, 12 ), range( 24, 144, 24 ), range( 168, 672, 168 ) ) as $hour ) {
            $values['bookly_gen_min_time_prior_booking'][] = array( $hour, Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) );
        }
        foreach ( array_merge( array( 1 ), range( 2, 12, 2 ), range( 24, 168, 24 ) ) as $hour ) {
            $values['bookly_gen_min_time_prior_cancel'][] = array( $hour, Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) );
        }

        self::renderTemplate( 'minimum_time_requirement', compact( 'values' ) );
    }

    /**
     * @inheritDoc
     */
    public static function renderCreateWordPressUser()
    {
        Selects::renderSingle( 'bookly_cst_create_account', __( 'Create WordPress user account for customers', 'bookly' ), __( 'If this setting is enabled then Bookly will be creating WordPress user accounts for all new customers. If the user is logged in then the new customer will be associated with the existing user account.', 'bookly' ) );
    }

    /**
     * @inheritDoc
     */
    public static function renderNewClientAccountRole()
    {
        $roles = array();
        $wp_roles = new \WP_Roles();
        foreach ( $wp_roles->get_names() as $role => $name ) {
            $roles[] = array( $role, $name );
        }
        Selects::renderSingle( 'bookly_cst_new_account_role', __( 'New user account role', 'bookly' ), __( 'Select what role will be assigned to newly created WordPress user accounts for customers.', 'bookly' ), $roles );
    }

    /**
     * @inheritDoc
     */
    public static function renderNewStaffAccountRole()
    {
        $roles = array();
        $wp_roles = new \WP_Roles();
        foreach ( $wp_roles->get_names() as $role => $name ) {
            $roles[] = array( $role, $name );
        }
        Selects::renderSingle( 'bookly_staff_new_account_role', __( 'New staff account role', 'bookly' ), __( 'Select what role will be assigned to newly created WordPress user accounts for staff', 'bookly' ), $roles );
    }

    /**
     * @inheritDoc
     */
    public static function renderAppointmentsSettings()
    {
        $slot_lengths = array(
            array( 0, __( 'Default', 'bookly' ) ),
        );
        foreach ( array( 2, 4, 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 180, 240, 360 ) as $duration ) {
            $slot_lengths[] = array( $duration, Utils\DateTime::secondsToInterval( $duration * MINUTE_IN_SECONDS ) );
        }
        $time_delimiter = get_option( 'bookly_appointments_time_delimiter', 0 );

        $statuses = array();
        foreach ( BooklyLib\Entities\CustomerAppointment::getStatuses() as $status ) {
            $statuses[] = array(
                $status,
                BooklyLib\Entities\CustomerAppointment::statusToString( $status ),
                0,
            );
        }

        self::renderTemplate( 'appointments', compact( 'slot_lengths', 'time_delimiter', 'statuses' ) );
    }

    /**
     * @inheritDoc
     */
    public static function renderMenuItem( $title, $slug )
    {
        Menu::renderItem( $title, $slug );
    }
}