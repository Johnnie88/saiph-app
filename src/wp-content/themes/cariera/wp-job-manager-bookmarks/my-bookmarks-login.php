<?php
/**
 * Lists a users bookmarks.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-bookmarks/my-bookmarks.php.
 *
 * @see      https://wpjobmanager.com/document/template-overrides/
 * @author   Automattic
 * @package  WP Job Manager - Bookmarks
 * @category Template
 * @version  1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$login_registration          = get_option( 'cariera_login_register_layout' );
$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
$login_registration_page_url = get_permalink( $login_registration_page );
?>

<div id="job-manager-job-bookmarks">
	<p class="account-sign-in"><?php esc_html_e( 'You need to be signed in to manage your bookmarks.', 'cariera' ); ?></p>
	<a class="btn btn-main btn-effect <?php echo 'popup' === $login_registration ? 'popup-with-zoom-anim' : ''; ?>" href="<?php echo 'popup' === $login_registration ? '#login-register-popup' : esc_url( $login_registration_page_url ); ?>"><?php esc_html_e( 'Sign in', 'cariera' ); ?></a>
</div>
