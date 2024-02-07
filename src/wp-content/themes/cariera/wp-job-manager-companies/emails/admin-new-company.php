<?php
/**
 * Custom: Company Emails - Admin New Company Email Notification
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/emails/admin-new-company.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.7
 * @version     1.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$message             = [];
$message['greeting'] = '<p>' . esc_html__( 'Hello,', 'cariera' ) . '</p>';
$message['intro']    = '<p>' . wp_kses_post( sprintf( __( 'A new company has just been submitted, <strong>%s</strong>.', 'cariera' ), $company->post_title ) ) . '</p>';

switch ( $company->post_status ) {
	case 'publish':
		$message['view_company_link'] = '<p>' . wp_kses_post( sprintf( __( 'It has been published and is now available to the public. You can view this company here: <a href="%s">Company Link</a>', 'cariera' ), esc_url( get_permalink( $company_id ) ) ) ) . '</p>';
		break;
	case 'pending':
		$message['view_company_link'] = '<p>' . wp_kses_post( sprintf( __( 'It is awaiting approval by an administrator in <a href="%s">WordPress admin</a>.', 'cariera' ), esc_url( admin_url( 'edit.php?post_type=company' ) ) ) ) . '</p>';
		break;
}

echo implode( '', apply_filters( 'cariera_new_company_notification_meta', $message, $company_id, $company ) );
