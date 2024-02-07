<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Components\Notices;
/** @var array $slot_lengths */
/** @var integer $time_delimiter */
/** @var array $statuses */
?>
<?php Selects::renderSingle( 'bookly_appointments_main_value', __( 'First value for newly created appointments via backend', 'bookly' ), __( 'Select what value should be selected first when creating a new appointment via backend.', 'bookly' ), array( array( 'provider', __( 'Provider', 'bookly' ) ), array( 'service', __( 'Service', 'bookly' ) ) ) ) ?>
<?php Selects::renderSingle( 'bookly_appointments_displayed_time_slots', __( 'Displayed time slots', 'bookly' ), __( 'Select what time slots will be shown when creating a new appointment via backend.', 'bookly' ), array( array( 'all', __( 'All', 'bookly' ) ), array( 'appropriate', __( 'Only appropriate slots', 'bookly' ) ) ), array( 'data-expand' => 'all' ) ) ?>
<div class="form-group border-left mt-3 ml-4 pl-3 bookly_appointments_displayed_time_slots-expander">
    <label for="bookly_appointments_time_delimiter"><?php esc_html_e( 'Time delimiter', 'bookly' ) ?></label>
    <select id="bookly_appointments_time_delimiter" class="form-control custom-select" name="bookly_appointments_time_delimiter">
        <?php foreach ( $slot_lengths as $slot ) : ?>
            <option value="<?php echo $slot[0] ?>"<?php selected( $slot[0] == $time_delimiter ) ?>><?php echo $slot[1] ?></option>
        <?php endforeach ?>
    </select>
    <small class="form-text text-muted"><?php esc_html_e( 'This setting allows you to set a delimiter during a day for appointments created via backend.', 'bookly' ) ?></small>
</div>
<?php Selects::renderSingle( 'bookly_appointment_cancel_action', __( 'Cancel appointment action', 'bookly' ), __( 'Select what happens when customer clicks cancel appointment link. With "Delete" the appointment will be deleted from the calendar. With "Cancel" only appointment status will be changed to "Cancelled".', 'bookly' ), array( array( 'delete', __( 'Delete', 'bookly' ) ), array( 'cancel', __( 'Cancel', 'bookly' ) ) ) ) ?>
<?php Selects::renderSingle( 'bookly_auto_change_status', __( 'Automatically change status of appointment', 'bookly' ), __( 'If enabled, initial status of the appointment will be changed to the status set in the "Status to be set" field after the appointment has been completed.', 'bookly' ), array(), array( 'data-expand' => '1' ) ) ?>
<div class="border-left mt-3 ml-4 pl-3 bookly_auto_change_status-expander">
    <?php Selects::renderSingle( 'bookly_auto_change_status_from', __( 'Initial status', 'bookly' ), null, $statuses ) ?>
    <?php Selects::renderSingle( 'bookly_auto_change_status_to', __( 'Status to be set', 'bookly' ), null, $statuses ) ?>
    <?php Notices\Cron\Notice::render() ?>
</div>