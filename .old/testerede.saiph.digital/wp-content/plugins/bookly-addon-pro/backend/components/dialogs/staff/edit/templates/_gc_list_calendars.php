<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly ?>
<div class="form-group bookly-js-google-calendars-list">
    <label for="bookly-calendar-id">
        <?php esc_html_e( 'Calendar', 'bookly' ) ?>
        <div class="spinner-border spinner-border-sm ml-1" role="status" style="display: none;"></div>
    </label>
    <select class="form-control custom-select" name="google_calendar_id" id="bookly-calendar-id">
        <option value=""><?php esc_html_e( '-- Select calendar --', 'bookly' ) ?></option>
        <?php foreach ( $calendars as $id => $calendar ) : ?>
            <option value="<?php echo esc_attr( $id ) ?>"<?php selected( $selected_calendar_id == $id ) ?>>
                <?php echo esc_html( $calendar['summary'] ) ?>
            </option>
        <?php endforeach ?>
    </select>
    <div class="alert alert-warning form-group mt-2 p-1 bookly-js-duplicate-calendar-warning" style="display: none;"><i class="fas pl-1 fa-exclamation-triangle"></i> <?php esc_html_e( 'This calendar is already connected to another staff member', 'bookly' ) ?></div>
    <small class="form-text text-muted"><?php esc_html_e( 'When you connect a calendar all future and past events will be synchronized according to the selected synchronization mode. This may take a few minutes. Please wait.', 'bookly' ) ?></small>
</div>