<?php
/**
 * Cariera Login Form template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/login-form.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'cariera-user-ajax' );

do_action( 'cariera_login_form_before' ); ?>

<form id="cariera_login" method="post">
	<p class="status"></p>

	<div class="form-group">
		<label for="username"><?php esc_html_e( 'Username or Email', 'cariera' ); ?></label>
		<input type="text" class="form-control" id="username" name="username" placeholder="<?php esc_html_e( 'Your Username or Email', 'cariera' ); ?>" />
	</div>

	<div class="form-group">
		<label for="password"><?php esc_html_e( 'Password', 'cariera' ); ?></label>
		<div class="cariera-password">
			<input type="password" class="form-control" id="password" name="password" placeholder="<?php esc_html_e( 'Your Password', 'cariera' ); ?>" />
			<i class="far fa-eye"></i>
		</div>
	</div>

	<?php
	$recaptcha_sitekey = get_option( 'cariera_recaptcha_sitekey' );
	$login_captcha     = get_option( 'cariera_recaptcha_login' );

	if ( class_exists( 'Cariera_Core\Extensions\Recaptcha\Recaptcha' ) && Cariera_Core\Extensions\Recaptcha\Recaptcha::is_recaptcha_enabled() && $login_captcha ) {
		?>
		<div class="form-group">
			<div id="recaptcha-login-form" class="g-recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha_sitekey ); ?>"></div>
		</div>
	<?php } ?>

	<div class="form-group">
		<div class="checkbox">
			<input id="check1" type="checkbox" name="remember" value="yes">
			<label for="check1"><?php esc_html_e( 'Keep me signed in', 'cariera' ); ?></label>
		</div>
	</div>

	<div class="form-group">
		<input type="submit" value="<?php esc_html_e( 'Sign in', 'cariera' ); ?>" class="btn btn-main btn-effect nomargin" /> 
	</div>

	<?php wp_nonce_field( 'cariera-ajax-login-nonce', 'login_security' ); ?>
</form>

<?php do_action( 'cariera_login_form_after' ); ?>
