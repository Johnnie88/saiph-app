<?php
/**
 * Custom: Bookmark Trigger Button
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-bookmarks/bookmark-trigger.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $job_manager_bookmarks;

$is_bookmarked               = $job_manager_bookmarks->is_bookmarked( $post->ID );
$bookmark_text               = $is_bookmarked ? esc_html__( 'Bookmarked', 'cariera' ) : esc_html__( 'Bookmark', 'cariera' );
$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
$login_registration_page_url = get_permalink( $login_registration_page );

if ( is_user_logged_in() ) {
	echo '<a href="#bookmark-popup-' . esc_attr( get_the_ID() ) . '" class="listing-bookmark btn btn-main btn-effect popup-with-zoom-anim">' . esc_html( $bookmark_text ) . '</a>';
} else {
	$login_registration = get_option( 'cariera_login_register_layout' );

	if ( 'popup' === $login_registration ) {
		echo '<a href="#login-register-popup" class="listing-bookmark btn btn-main btn-effect popup-with-zoom-anim">';
	} else {
		echo '<a href="' . esc_url( $login_registration_page_url ) . '" class="listing-bookmark btn btn-main btn-effect">';
	}

	esc_html_e( 'Login to bookmark', 'cariera' );

	echo '</a>';
}
