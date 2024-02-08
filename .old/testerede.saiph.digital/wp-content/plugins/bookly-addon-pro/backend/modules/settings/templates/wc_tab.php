<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs as ControlsInputs;
use Bookly\Backend\Components\Settings\Inputs;
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Components\Ace;
use Bookly\Backend\Modules\Settings\Codes;
?>
<div class="tab-pane" id="bookly_settings_woo_commerce">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'woo_commerce' ) ) ?>" id="woocommerce">
        <div class="card-body">
            <div class="form-group">
                <h4><?php esc_html_e( 'Instructions', 'bookly' ) ?></h4>
                <p>
                    <?php _e( 'You need to install and activate WooCommerce plugin before using the options below.<br/><br/>Once the plugin is activated do the following steps:', 'bookly' ) ?>
                </p>
                <ol>
                    <li><?php esc_html_e( 'Create a product in WooCommerce that can be placed in cart.', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'In the form below enable WooCommerce option.', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'Select the product that you created at step 1 in the drop down list of products.', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'If needed, edit item data which will be displayed in the cart. Besides cart item data Bookly passes address and account fields into WooCommerce if you collect them in your booking form.', 'bookly' ) ?></li>
                </ol>
                <p>
                    <?php esc_html_e( 'Note that once you have enabled WooCommerce option in Bookly the built-in payment methods will no longer work. All your customers will be redirected to WooCommerce cart instead of standard payment step.', 'bookly' ) ?>
                </p>
            </div>

            <?php Selects::renderSingle( 'bookly_wc_enabled', 'WooCommerce', null, array(), array( 'data-expand' => '1' ) ) ?>
            <?php if ( $wc_warning ): ?>
                <div class='alert alert-danger form-group my-n2 p-1'><i class='fas pl-1 fa-times'></i> <?php echo $wc_warning ?></div>
            <?php endif ?>
            <div class="form-group border-left mt-3 ml-4 pl-3 bookly_wc_enabled-expander">
                <?php Selects::renderSingle( 'bookly_wc_product', __( 'Booking product', 'bookly' ), null, $products ) ?>
                <?php Selects::renderSingle( 'bookly_wc_create_order_via_backend', __( 'Create a WooCommerce order from backend', 'bookly' ), __( 'If enabled, a WooCommerce order will be created for appointment when a payment is attached in backend', 'bookly' ), array(), array( 'data-expand' => '1' ) ) ?>
                <?php if ( $order_statuses ) : ?>
                    <div class='form-group border-left mt-3 ml-4 pl-3 bookly_wc_create_order_via_backend-expander'>
                        <?php Selects::renderSingle( 'bookly_wc_default_order_status', __( 'Default status of WooCommerce order', 'bookly' ), __( 'Status of WooCommerce order created from backend', 'bookly' ), $order_statuses ) ?>
                    </div>
                <?php endif ?>
                <?php Selects::renderSingle( 'bookly_wc_create_order_at_zero_cost', __( 'Create a WooCommerce order if the cost of the service is zero', 'bookly' ), __( 'If enabled, a WooCommerce order will be created for services with 0 (zero) price. Please note that if the order contains at least one service with a price other than 0, the WooCommerce order will still be created', 'bookly' ) ) ?>
                <?php Inputs::renderText( 'bookly_l10n_wc_cart_info_name', __( 'Cart item data', 'bookly' ) ) ?>
                <?php Ace\Editor::render( 'bookly-settings-woo-commerce', 'bookly_wc_cart_info', Codes::getJson( 'woocommerce' ), get_option( 'bookly_l10n_wc_cart_info_value', '' ) ) ?>
                <input type="hidden" name="bookly_l10n_wc_cart_info_value" value="<?php echo esc_attr( get_option( 'bookly_l10n_wc_cart_info_value', '' ) ) ?>">
            </div>
        </div>

        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php ControlsInputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( null, 'ml-2' ) ?>
        </div>
    </form>
</div>