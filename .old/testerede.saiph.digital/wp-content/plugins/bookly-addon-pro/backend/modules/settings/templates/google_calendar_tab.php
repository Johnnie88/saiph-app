<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs as ControlsInputs;
use Bookly\Backend\Components\Settings\Inputs;
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Modules\Settings\Proxy;
use Bookly\Backend\Modules\Settings\Codes;
use Bookly\Backend\Components\Ace;
use Bookly\Lib\Config;
use BooklyPro\Lib\Google;
?>
<div class="tab-pane" id="bookly_settings_google_calendar">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'google_calendar' ) ) ?>">
        <div class="card-body">
            <div class="form-group">
                <h4><?php esc_html_e( 'Instructions', 'bookly' ) ?></h4>
                <p><?php esc_html_e( 'To find your client ID and client secret, do the following:', 'bookly' ) ?></p>
                <ol>
                    <li><?php _e( 'Go to the <a href="https://console.developers.google.com/" target="_blank">Google Cloud Platform</a>.', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'Select a project or create a new one.', 'bookly' ) ?></li>
                    <li><?php printf( __( 'In the sidebar on the left, select <b>Library</b>. In the list of APIs look for <b>Calendar API</b> or click <a href="%s" target="_blank">this link</a> to go directly to Calendar API and make sure it is enabled.', 'bookly' ), 'https://console.cloud.google.com/apis/library/calendar-json.googleapis.com?supportedpurview=project' ) ?></li>
                    <li><?php _e( 'Click <b>OAuth consent screen</b> tab, select user type and provide the necessary information (App name, User support email and Developer contact information). Then click <b>Save and continue</b>. You can leave the following settings unchanged.', 'bookly' ) ?></li>
                    <li><?php _e( 'We recommend to change the publishing status of your app to <b>In production</b> by clicking <b>Publish app</b> button.', 'bookly' ) ?></li>
                    <li><?php printf( __( 'In the sidebar on the left, select <a href="%s" target="_blank">Credentials</a>, and in <b>Create credentials</b> drop-down menu select <b>OAuth client ID</b>.', 'bookly' ), 'https://console.cloud.google.com/apis/credentials' ) ?></li>
                    <li><?php _e( 'Select <b>Web application</b> and create your project\'s <b>OAuth 2.0 credentials</b> by providing the necessary information. For <b>Authorized redirect URIs</b> enter the <b>Redirect URI</b> found below on this page. Click <b>Create</b>.', 'bookly' ) ?></li>
                    <li><?php _e( 'In the popup window look for the <b>Client ID</b> and <b>Client secret</b>. Use them in the form below on this page.', 'bookly' ) ?></li>
                    <li><?php _e( 'Go to <b>Staff Members</b>, select a staff member and enable the connection with Google Calendar on the <b>Advanced</b> tab.', 'bookly' ) ?></li>
                </ol>
            </div>
            <?php Inputs::renderText( 'bookly_gc_client_id', __( 'Client ID', 'bookly' ), __( 'The client ID obtained from the Google Cloud Platform', 'bookly' ) ) ?>
            <?php Inputs::renderText( 'bookly_gc_client_secret', __( 'Client secret', 'bookly' ), __( 'The client secret obtained from the Google Cloud Platform', 'bookly' ) ) ?>
            <?php Inputs::renderTextCopy( Google\Client::generateRedirectURI(), __( 'Redirect URI', 'bookly' ), __( 'Enter this URL as a redirect URI in the Google Cloud Platform', 'bookly' ) ) ?>
            <?php if ( Config::advancedGoogleCalendarActive() ) : ?>
                <?php Proxy\AdvancedGoogleCalendar::renderSettings() ?>
            <?php else : ?>
                <?php Selects::renderSingle( 'bookly_gc_sync_mode', __( 'Synchronization mode', 'bookly' ), __( 'With "One-way" sync Bookly pushes new appointments and any further changes to Google Calendar. With "Two-way front-end only" sync Bookly will additionally fetch events from Google Calendar and remove corresponding time slots before displaying the Time step of the booking form (this may lead to a delay when users click Next to get to the Time step).', 'bookly' ), array(
                    array(
                        '1-way',
                        __( 'One-way', 'bookly' ),
                    ),
                    array( '1.5-way', __( 'Two-way front-end only', 'bookly' ) ),
                ) ) ?>
            <?php endif ?>
            <div class="border-left ml-4 pl-3">
                <?php Selects::renderSingle( 'bookly_gc_limit_events', __( 'Limit number of fetched events', 'bookly' ), __( 'If there is a lot of events in Google Calendar sometimes this leads to a lack of memory in PHP when Bookly tries to fetch all events. You can limit the number of fetched events here.', 'bookly' ), $fetch_limits ) ?>
            </div>
            <?php Inputs::renderText( 'bookly_gc_event_title', __( 'Template for event title', 'bookly' ), __( 'Configure what information should be placed in the title of Google Calendar event. Available codes are {service_name}, {category_name}, {staff_name} and {client_names}.', 'bookly' ) ) ?>
            <div class="form-group">
                <label for="bookly_gc_event_description"><?php esc_html_e( 'Template for event description', 'bookly' ) ?></label>
                <?php Ace\Editor::render( 'bookly-settings-google-calendar', 'bookly_gc_event_description', Codes::getJson( 'google_calendar' ), get_option( 'bookly_gc_event_description', '' ) ) ?>
                <input type="hidden" name="bookly_gc_event_description" value="<?php echo esc_attr( get_option( 'bookly_gc_event_description', '' ) ) ?>">
            </div>
        </div>

        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php ControlsInputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( null, 'ml-2' ) ?>
        </div>
    </form>
</div>