<?php
/**
 * Cariera Forgot Password Form template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/forgot-password-form.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'cariera-user-ajax' );
?>

<form id="cariera_forget_pass" method="post">
	<p class="status"></p>

	<div class="form-group">
		<label for="forgot_pass"><?php esc_html_e( 'Username or Email Address *', 'cariera' ); ?></label>
		<input id="forgot_pass" type="text" name="forgot_pass" class="form-control" placeholder="<?php esc_html_e( 'Your Username or Email Address', 'cariera' ); ?>" />
	</div>

	<?php
	$recaptcha_sitekey  = get_option( 'cariera_recaptcha_sitekey' );
	$forgotpass_captcha = get_option( 'cariera_recaptcha_forgotpass' );

	if ( class_exists( 'Cariera_Core\Extensions\Recaptcha\Recaptcha' ) && \Cariera_Core\Extensions\Recaptcha\Recaptcha::is_recaptcha_enabled() && $forgotpass_captcha ) {
		?>
		<div class="form-group">
			<div id="recaptcha-forgot-pass-form" class="g-recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha_sitekey ); ?>"></div>
		</div>
	<?php } ?>

	<div class="form-group">
		<input type="submit" name="submit" value="<?php esc_html_e( 'Reset Password', 'cariera' ); ?>" class="btn btn-main btn-effect nomargin" />
	</div>

	<?php wp_nonce_field( 'cariera-ajax-forgetpass-nonce', 'forgetpass_security' ); ?>
</form>
