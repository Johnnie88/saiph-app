<?php
/**
 * Lists job listing alerts content if user is not logged in.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-alerts/my-alerts-login.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Alerts
 * @category    Template
 * @version     1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$login_registration          = get_option( 'cariera_login_register_layout' );
$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
$login_registration_page_url = get_permalink( $login_registration_page );
?>

<div id="job-manager-job-alerts">
	<p class="account-sign-in"><?php esc_html_e( 'You need to be signed in to manage your alerts.', 'cariera' ); ?></p>
	<a class="btn btn-main btn-effect <?php echo 'popup' === $login_registration ? 'popup-with-zoom-anim' : ''; ?>" href="<?php echo 'popup' === $login_registration ? '#login-register-popup' : esc_url( $login_registration_page_url ); ?>"><?php esc_html_e( 'Sign in', 'cariera' ); ?></a>
</div>
