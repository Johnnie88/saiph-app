<?php
/**
 * Notice shown when user is required to log in before applying for a job.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-applications/application-form-login.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Applications
 * @category    Template
 * @version     1.4.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$login_registration = get_option( 'cariera_login_register_layout' );

if ( 'popup' === $login_registration ) {
	$login = '<a href="#login-register-popup" class="popup-with-zoom-anim">' . esc_html__( 'Sign in', 'cariera' ) . '</a>';
} else {
	$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
	$login_registration_page_url = get_permalink( $login_registration_page );

	$login = '<a href="' . esc_url( $login_registration_page_url ) . '">' . esc_html__( 'Sign in', 'cariera' ) . '</a>';
} ?>

<p><?php echo apply_filters( 'job_manager_job_applications_login_required_message', sprintf( esc_html__( 'You must %s to apply for this position.', 'cariera' ), $login ) ); ?></p>
