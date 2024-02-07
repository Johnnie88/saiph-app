<?php
/**
 * Uninstall LoginPress.
 *
 * @package loginpress
 * @author WPBrigade
 * @since 1.1.9
 * @version  3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$loginpress_setting   = get_option( 'loginpress_setting' );
$loginpress_uninstall = isset( $loginpress_setting['loginpress_uninstall'] ) ? $loginpress_setting['loginpress_uninstall'] : 'off';
if ( 'on' != $loginpress_uninstall ) {
	return;
}

// Load the LoginPress file.
require_once 'loginpress.php';

// Array of Plugin's Option.
$loginpress_uninstall_options = array(
	'loginpress_customization',
	'loginpress_setting',
	'loginpress_addon_active_time',
	'loginpress_addon_dismiss_1',
	'loginpress_review_dismiss',
	'loginpress_active_time',
	'_loginpress_optin',
	'loginpress_friday_sale_active_time',
	'loginpress_friday_sale_dismiss',
	'loginpress_friday_21_sale_dismiss',
);

if ( ! is_multisite() ) {
	// Handle the delete loginpress force rest password for all users.
	loginpress_force_reset_password_remove();

	// Delete all plugin Options.
	foreach ( $loginpress_uninstall_options as $option ) {
		if ( get_option( $option ) ) {
			delete_option( $option );
		}
	}

} else {

	global $wpdb;
	$loginpress_blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

	foreach ( $loginpress_blog_ids as $blog_id ) {

		switch_to_blog( $blog_id );

		// Handle the delete loginpress force rest password for all users.
		loginpress_force_reset_password_remove();

		// Pull the LoginPress page from options.
		$loginpress             = new LoginPress();
		$loginpress_page        = $loginpress->get_loginpress_page();
		$loginpress_page_id     = $loginpress_page->ID;

		wp_trash_post( $loginpress_page_id );

		// Delete all plugin Options.
		foreach ( $loginpress_uninstall_options as $option ) {
			if ( get_option( $option ) ) {
				delete_option( $option );
			}
		}

		restore_current_blog();
	}
}


/**
 * Handle the delete loginpress force rest password for all users.
 *
 * @return void
 * @since 3.0.0
 */
function loginpress_force_reset_password_remove() {

	$args = array(
		'meta_query' => array(
			array(
				'key'     => 'loginpress_password_reset_limit',
			),
		),
		'fields' => 'ID',
	);

	$blog_users = get_users($args);

	if ( $blog_users ) {
		foreach ( $blog_users as $users ) {
			delete_user_meta( $users->ID, 'loginpress_password_reset_limit' );
		}
	}
}
// Clear any cached data that has been removed.
// wp_cache_flush();
