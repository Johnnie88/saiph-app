<?php
/**
 * Login & Register Popup
 *
 * This template can be overridden by copying it to cariera-child/templates/popups/login-register.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Start of Login & Register Popup -->
<div id="login-register-popup" class="small-dialog zoom-anim-dialog mfp-hide">

	<!-- Start of Signin wrapper -->
	<div class="signin-wrapper">
		<div class="small-dialog-headline">
			<h3 class="title"><?php esc_html_e( 'Sign in', 'cariera' ); ?></h3>
		</div>

		<div class="small-dialog-content">
			<?php echo do_shortcode( '[cariera_login_form]' ); // Add login form. ?>

			<div class="bottom-links">
				<a href="#" class="signup-trigger"><i class="fas fa-user"></i><?php esc_html_e( 'Don\'t have an account?', 'cariera' ); ?></a>
				<a href="#" class="forget-password-trigger"><i class="fas fa-lock"></i><?php esc_html_e( 'Forgot Password?', 'cariera' ); ?></a>
			</div>

			<?php do_action( 'cariera_social_login' ); ?>
		</div>    
	</div>
	<!-- End of Signin wrapper -->

	<!-- Start of Signup wrapper -->
	<div class="signup-wrapper">
		<div class="small-dialog-headline">
			<h3 class="title"><?php esc_html_e( 'Sign Up', 'cariera' ); ?></h3>
		</div>

		<div class="small-dialog-content">
			<?php echo do_shortcode( '[cariera_registration_form]' ); // Add registration form. ?>

			<div class="bottom-links">
				<a href="#" class="signin-trigger"><i class="fas fa-user"></i><?php esc_html_e( 'Already registered?', 'cariera' ); ?></a>
				<a href="#" class="forget-password-trigger"><i class="fas fa-lock"></i><?php esc_html_e( 'Forgot Password?', 'cariera' ); ?></a>
			</div>

			<?php do_action( 'cariera_social_login' ); ?>
		</div>
	</div>
	<!-- End of Signup wrapper -->


	<!-- Start of Forget Password wrapper -->
	<div class="forgetpassword-wrapper">
		<div class="small-dialog-headline">
			<h3 class="title"><?php esc_html_e( 'Forgotten Password', 'cariera' ); ?></h3>
		</div>

		<div class="small-dialog-content">
			<?php echo do_shortcode( '[cariera_forgetpass_form]' ); // Add forget password form. ?>

			<div class="bottom-links">
				<a href="#" class="signin-trigger"><i class="fas fa-arrow-left"></i><?php esc_html_e( 'Go back', 'cariera' ); ?></a>
			</div>
		</div>

	</div>
	<!-- End of Forget Password wrapper -->
</div>
