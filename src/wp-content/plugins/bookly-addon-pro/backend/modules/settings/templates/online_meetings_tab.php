<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs as ControlsInputs;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Settings\Inputs;
use Bookly\Backend\Components\Settings\Selects;
use BooklyPro\Lib;
?>
<div class="tab-pane" id="bookly_settings_online_meetings">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'online_meetings' ) ) ?>">
        <div class="card-body">
            <div class="card bookly-collapse-with-arrow">
                <div class="card-header d-flex align-items-center">
                    <a href="#bookly_om_zoom" class="ml-2" role="button" data-toggle="bookly-collapse">
                        Zoom
                    </a>
                    <img class="ml-auto" src="<?php echo plugins_url( 'backend/modules/settings/resources/images/Zoom_logo.svg', Lib\Plugin::getMainFile() ) ?>" height="24px"/>
                </div>
                <div id="bookly_om_zoom" class="bookly-collapse bookly-show">
                    <div class="card-body">
                        <?php Selects::renderSingle( 'bookly_zoom_authentication', __( 'Authentication', 'bookly' ), __( 'Select the type of authorization that will be used by default for staff members' ), array( array( Lib\Zoom\Authentication::TYPE_OAuth, 'OAuth 2.0' ), ) ) ?>
                        <div id="bookly-zoom-oauth" class="bookly-js-zoom-credentials" style="display: none">
                            <div class="form-group">
                                <h4><?php esc_html_e( 'Instructions for creating an OAuth App', 'bookly' ) ?></h4>
                                <p><?php esc_html_e( 'To find your Client ID and Client Secret, do the following:', 'bookly' ) ?></p>
                                <ol>
                                    <li><?php esc_html_e( 'Sign in to your Zoom account', 'bookly' ) ?></li>
                                    <li><?php _e( 'Visit the <a href="https://marketplace.zoom.us/" target="_blank">Zoom App Marketplace</a>', 'bookly' ) ?></li>
                                    <li><?php _e( 'Click on the <b>Develop</b> option in the dropdown on the top-right corner and select <b>Build App</b>', 'bookly' ) ?></li>
                                    <li><?php _e( 'A page with various app types will be displayed. Select <b>OAuth</b> as the app type and click on <b>Create</b>', 'bookly' ) ?></li>
                                    <li><?php _e( 'Enter the name of your App. In <b>Choose app type</b> select <b>Account-level app</b> and click <b>Create</b>', 'bookly' ) ?></li>
                                    <li><?php esc_html_e( 'After creating your app, fill out descriptive and contact information', 'bookly' ) ?></li>
                                    <li><?php _e( 'Go to <b>App Credentials</b> tab and look for the <b>Client ID</b> and <b>Client Secret</b>. Use them in the form below on this page', 'bookly' ) ?></li>
                                    <li><?php _e( 'For <b>Redirect URL for OAuth</b> enter the <b>Redirect URL for OAuth</b> found below on this page', 'bookly' ) ?></li>
                                    <li><?php _e( 'For <b>Whitelist URL</b> add a new record with the value of <b>Redirect URL for OAuth</b> found below on this page. If you use both HTTP and HTTPS, make sure to add both records for <b>Whitelist URL</b>', 'bookly' ) ?></li>
                                    <li><?php esc_html_e( 'Add the scopes required for your app. At the very least, you should select meeting:write:admin', 'bookly' ) ?></li>
                                    <li><?php _e( 'Once the app is created, <b>Save</b> the changes on this page', 'bookly' ) ?></li>
                                    <li><?php _e( 'Click the <b>Connect OAuth</b> button below and complete the connection between Bookly and Zoom', 'bookly' ) ?></li>
                                </ol>
                            </div>
                            <?php Inputs::renderText( 'bookly_zoom_oauth_client_id', __( 'Client ID', 'bookly' ), __( 'The Client ID obtained from your OAuth app', 'bookly' ) ) ?>
                            <?php Inputs::renderText( 'bookly_zoom_oauth_client_secret', __( 'Client Secret', 'bookly' ), __( 'The Client Secret obtained from your OAuth app', 'bookly' ) ) ?>
                            <?php Inputs::renderTextCopy( add_query_arg( array( 'action' => 'bookly_pro_request_zoom_access_token' ), admin_url( 'admin-ajax.php' ) ), __( 'Redirect URL for OAuth', 'bookly' ), __( 'Destination URL where Zoom will send the access token after the user completes the OAuth authentication', 'bookly' ) ) ?>
                            <?php if ( get_option( 'bookly_zoom_oauth_client_id' ) && get_option( 'bookly_zoom_oauth_client_secret' ) ): ?>
                                <?php if ( $connected ) : ?>
                                    <?php Buttons::render( 'bookly-zoom-disconnect', 'btn-danger', __( 'Disconnect', 'bookly' ) . ' OAuth' ) ?>
                                <?php else: ?>
                                    <?php Buttons::render( 'bookly-zoom-connect', 'btn-success', __( 'Connect', 'bookly' ) . ' OAuth' ) ?>
                                <?php endif ?>
                            <?php else: ?>
                                <?php Buttons::render( null, 'btn-success', __( 'Connect', 'bookly' ) . ' OAuth', array( 'disabled' => 'disabled' ) ) ?>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bookly-collapse-with-arrow">
                <div class="card-header d-flex align-items-center">
                    <a href="#bookly_om_bbb" class="ml-2" role="button" data-toggle="bookly-collapse">
                        BigBlueButton
                    </a>
                    <img class="ml-auto" src="<?php echo plugins_url( 'backend/modules/settings/resources/images/BigBlueButton_logo.svg', Lib\Plugin::getMainFile() ) ?>" height="24px"/>
                </div>
                <div id="bookly_om_bbb" class="bookly-collapse bookly-show">
                    <div class="card-body pb-0">
                        <div class="form-group">
                            <p><?php esc_html_e( 'To find your URL and Secret, connect to the server (e.g. via SSH) with hosted BBB and execute the following command in console', 'bookly' ) ?>:</p>
                            <?php Inputs::renderTextCopy( 'bbb-config --secret', '', null ) ?>
                        </div>
                        <?php Inputs::renderText( 'bookly_bbb_server_end_point', __( 'Server', 'bookly' ), __( 'The URL obtained from command return', 'bookly' ) ) ?>
                        <?php Inputs::renderText( 'bookly_bbb_shared_secret', __( 'Shared secret', 'bookly' ), __( 'The Secret obtained from command return', 'bookly' ) ) ?>
                    </div>
                </div>
            </div>

            <div class="card bookly-collapse-with-arrow">
                <div class="card-header d-flex align-items-center">
                    <a href="#bookly_om_jitsi" class="ml-2" role="button" data-toggle="bookly-collapse">
                        Jitsi Meet
                    </a>
                    <img class="ml-auto" src="<?php echo plugins_url( 'backend/modules/settings/resources/images/jitsi.svg', Lib\Plugin::getMainFile() ) ?>" height="24px"/>
                </div>
                <div id="bookly_om_jitsi" class="bookly-collapse bookly-show">
                    <div class="card-body pb-0">
                        <?php Inputs::renderText( 'bookly_jitsi_server', __( 'Server', 'bookly' ), __( 'The URL of Jitsi server used for online meetings', 'bookly' ) ) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php ControlsInputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( 'bookly-online-meetings-reset', 'ml-2' ) ?>
        </div>
    </form>
</div>