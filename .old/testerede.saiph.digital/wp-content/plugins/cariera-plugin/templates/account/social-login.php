<?php
/**
 * Cariera Social login support
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/social-login.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Social Login (miniorange) Support
 *
 * @see https://wordpress.org/plugins/miniorange-login-openid/
 */
if ( function_exists( 'mo_openid_initialize_social_login' ) ) {
	?>
	<div class="social-miniorange-container">
		<div class="social-login-separator"><span><?php esc_html_e( 'Or connect with', 'cariera' ); ?></span></div>
		<?php echo do_shortcode( '[miniorange_social_login  view="horizontal" heading=""]' ); ?>
	</div>
	<?php
}

/**
 * WordPress Social Login Support
 *
 * @see https://wordpress.org/plugins/wordpress-social-login/
 */
if ( function_exists( '_wsl_e' ) ) {
	?>
	<div class="social-login-separator"><span><?php esc_html_e( 'Or connect with', 'cariera' ); ?></span></div>
	<?php
	do_action( 'wordpress_social_login' );
}
