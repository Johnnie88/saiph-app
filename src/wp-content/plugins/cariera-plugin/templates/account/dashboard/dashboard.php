<?php
/**
 * Cariera Dashboard template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/dashboard.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_roles;

if ( ! is_user_logged_in() ) { ?>
	<p><?php esc_html_e( 'You need to be signed in to access your dashboard.', 'cariera' ); ?></p>

	<?php
	$login_registration = get_option( 'cariera_login_register_layout' );

	if ( $login_registration === 'popup' ) {
		?>
		<a href="#login-register-popup" class="btn btn-main btn-effect popup-with-zoom-anim">
		<?php
	} else {
		$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
		$login_registration_page_url = get_permalink( $login_registration_page );
		?>

		<a href="<?php echo esc_url( $login_registration_page_url ); ?>" class="btn btn-main btn-effect">
		<?php
	}
		esc_html_e( 'Sign in', 'cariera' );
	?>
	</a>
	<?php
} else {
	// Dashboard Cards.
	cariera_get_template_part( 'account/dashboard/cards' );
	?>

	<!-- Start of Charts & Packages -->
	<div class="row mt50">
		<?php
		// Dashboard Statistics.
		cariera_get_template_part( 'account/dashboard/views-charts' );

		// Dashboard Active Packages.
		cariera_get_template_part( 'account/dashboard/active-packages' );
		?>
	</div>
	<?php
}
