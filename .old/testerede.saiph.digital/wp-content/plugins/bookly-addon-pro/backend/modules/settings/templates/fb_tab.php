<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs as ControlsInputs;
use Bookly\Backend\Components\Settings\Inputs;
?>
<div class="tab-pane" id="bookly_settings_facebook">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'facebook' ) ) ?>">
        <div class="card-body">
            <div class="form-group">
                <h4><?php esc_html_e( 'Instructions', 'bookly' ) ?></h4>
                <p><?php esc_html_e( 'To set up Facebook integration, do the following:', 'bookly' ) ?></p>
                <ol>
                    <li><?php printf( esc_html__( 'Follow the steps at %s to create a Developer Account.', 'bookly' ), '<a href="https://developers.facebook.com/docs/development/register" target="_blank">developers.facebook.com/docs/development/register</a>' ) ?></li>
                    <li><?php esc_html_e( 'In the Apps section, click the Create App button. Select Other as the use case, and then choose Consumer as the app type.', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'In "Add products to your app" section, find "Facebook login" and press "Set up".', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'In the sidebar on the left, select "Settings".', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'Enable "Login with the JavaScript SDK" and enter your site URL in "Allow Domains for the JavaScript SDK". Click "Save changes".', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'At the top of your screen press on App ID to copy it.', 'bookly' ) ?> <?php esc_html_e( 'Use it in the form below.', 'bookly' ) ?></li>
                </ol>
            </div>
            <?php Inputs::renderText( 'bookly_fb_app_id', __( 'App ID', 'bookly' ) ) ?>
        </div>

        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php ControlsInputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( null, 'ml-2' ) ?>
        </div>
    </form>
</div>