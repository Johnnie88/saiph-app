<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Modules\Settings\Page as Settings;
use Bookly\Lib\Utils\Common;
use BooklyPro\Lib\Config;
?>
<div class="col-md-3 my-2">
    <?php Inputs::renderCheckBox( __( 'Show birthday field', 'bookly' ), null, get_option( 'bookly_app_show_birthday' ), array( 'id' => 'bookly-show-birthday' ) ) ?>
</div>
<div class="col-md-3 my-2">
    <div data-toggle="bookly-popover" data-trigger="hover" data-placement="auto">
        <?php Inputs::renderCheckBox( __( 'Show address fields', 'bookly' ), null, get_option( 'bookly_app_show_address' ), array( 'id' => 'bookly-show-address' ) ) ?>
    </div>
</div>
<div class="col-md-3 my-2">
    <?php Inputs::renderCheckBox( __( 'Show Facebook login button', 'bookly' ), null, Config::showFacebookLoginButton(), array( 'id' => 'bookly-show-facebook-login-button', 'data-appid' => Config::getFacebookAppId() ) ) ?>
</div>
<div id="bookly-facebook-warning" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Facebook</h5>
                <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <?php printf( __( 'Please configure Facebook App integration in <a href="%s">settings</a> first.', 'bookly' ), Common::escAdminUrl( Settings::pageSlug(), array( 'tab' => 'facebook' ) ) ) ?>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderCancel( __( 'Ok', 'bookly' ) ) ?>
            </div>
        </div>
    </div>
</div>