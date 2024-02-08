<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs as ControlsInputs;
use Bookly\Backend\Modules\Settings\Proxy;
?>
<div class="tab-pane" id="bookly_settings_additional">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'additional' ) ) ?>">
        <div class="card-body">
            <div class="form-group">
                <?php Selects::renderSingle( 'bookly_cal_frontend_enabled', __( 'Display front-end calendar', 'bookly' ), __( 'If this option is enabled then customers will be able to view the availability of the selected staff in a front-end calendar', 'bookly' ) ) ?>
            </div>
            <?php Proxy\Shared::renderAdditionalSettings() ?>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php ControlsInputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( null, 'ml-2' ) ?>
        </div>
    </form>
</div>