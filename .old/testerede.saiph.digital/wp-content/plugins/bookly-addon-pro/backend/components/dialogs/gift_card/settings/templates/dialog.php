<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;

$bookly_gift_card_partial_payment = get_option( 'bookly_gift_card_partial_payment', 0 );
?>
<form id="bookly-gift-cards-settings-modal" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title h5"><?php _e( 'Gift cards settings', 'bookly' ) ?></div>
                <button type="button" class="close" data-dismiss="bookly-modal"><span>×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label
                        for="bookly_gift_card_partial_payment"><?php esc_html_e( 'Use gift card for partial payment', 'bookly' ) ?></label>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="bookly_gift_card_partial_payment-0" name="bookly_gift_card_partial_payment" value="0"<?php checked( ! $bookly_gift_card_partial_payment ) ?> class="custom-control-input"/>
                        <label for="bookly_gift_card_partial_payment-0" class="custom-control-label"><?php esc_html_e( 'Disabled', 'bookly' ) ?></label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="bookly_gift_card_partial_payment-1" name="bookly_gift_card_partial_payment" value="1"<?php checked( $bookly_gift_card_partial_payment ) ?> class="custom-control-input"/>
                        <label for="bookly_gift_card_partial_payment-1" class="custom-control-label"><?php esc_html_e( 'Enabled', 'bookly' ) ?></label>
                    </div>
                    <small class="form-text text-muted"><?php esc_html_e( 'If enabled, clients will be able to use gift card when the balance of the gift card is less than the total amount due. If disabled, clients will only be able to use gift card that has a balance equal to or greater than the service price.', 'bookly' ) ?></small>
                </div>
                <div class="form-group">
                    <label for="bookly_gift_card_mask"><?php esc_html_e( 'Default gift card mask', 'bookly' ) ?></label>
                    <input class="form-control" type="text" name="bookly_gift_card_mask" id="bookly_gift_card_mask" value="<?php echo esc_attr( get_option( 'bookly_cloud_gift_default_code_mask', '' ) ) ?>"/>
                    <div class="alert alert-danger form-group mt-2 p-1 bookly-collapse"><i class="fas pl-1 fa-times mr-2"></i><?php esc_html_e( 'Minimum number of asterisks "*" - 4', 'bookly' ) ?></div>
                    <small class="form-text text-muted"><?php esc_html_e( 'Enter default mask for auto-generated codes. You can enter asterisks "*" for variables here.', 'bookly' ) ?></small>
                </div>
            </div>
            <div class="modal-footer">
                <div class="ml-auto">
                    <?php Buttons::renderSubmit( null, 'bookly-js-save bookly-js-hide-on-loading' ) ?>
                    <?php Buttons::renderCancel( __( 'Close', 'bookly' ) ) ?>
                </div>
            </div>
        </div>
    </div>
</form>