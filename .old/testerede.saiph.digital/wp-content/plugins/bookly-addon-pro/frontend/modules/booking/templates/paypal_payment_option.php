<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Utils\Price;
?>
<div class="bookly-box bookly-list">
    <label>
        <input type="radio" class="bookly-js-payment" name="payment-method-<?php echo $form_id ?>" value="paypal"/>
        <span><?php echo Common::getTranslatedOption( 'bookly_l10n_label_pay_paypal' ) ?>
            <?php if ( $show_price ) : ?>
                <span class="bookly-js-pay"><?php echo Price::format( $cart_info->getPayNow() ) ?></span>
            <?php endif ?>
        </span>
        <img src="<?php echo plugins_url( 'frontend/resources/images/paypal.svg', BooklyPro\Lib\Plugin::getMainFile() ) ?>" alt="PayPal" />
    </label>
</div>