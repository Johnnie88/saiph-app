<?php
/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) {
	return;
}
?>

<div class="woo-form-coupon">
	<?php
	if ( ! WC()->cart->applied_coupons ) {
		$info_message = apply_filters( 'woocommerce_checkout_coupon_message', esc_html__( 'Have a coupon?', 'cariera' ) . ' <a href="#" class="showcoupon">' . esc_html__( 'Click here to enter your code', 'cariera' ) . '</a>' );
		wc_print_notice( $info_message, 'notice' );
	}
	?>

	<form class="checkout_coupon" method="post" style="display:none">
		<p class="form-row form-row-first">
			<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'cariera' ); ?>" id="coupon_code" value="" />
		</p>

		<p class="form-row form-row-last">            
			<button type="submit" class="button btn btn-main btn-effect<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'cariera' ); ?>"><?php esc_html_e( 'Apply coupon', 'cariera' ); ?></button>
		</p>

		<div class="clear"></div>
	</form>
</div>
