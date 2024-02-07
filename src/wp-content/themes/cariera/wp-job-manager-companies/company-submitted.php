<?php
/**
 * Custom: Company - Company Submitted
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-submitted.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.4
 * @version     1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $wp_post_types;

switch ( $company->post_status ) :
	case 'publish':
		echo '<div class="company-submitted">' . wp_kses_post(
			sprintf(
				esc_html__( 'Your company has been submitted successfully. To view your company %1$sclick here%2$s.', 'cariera' ),
				'<a href="' . esc_url( get_permalink( $company->ID ) ) . '">',
				'</a>'
			)
		) . '</div>';
		break;
	case 'pending':
		echo '<div class="company-submitted">' . wp_kses_post(
			sprintf(
				esc_html__( 'Your company has been submitted successfully and is pending approval.', 'cariera' )
			)
		) . '</div>';
		break;
	default:
		do_action( 'cariera_company_submitted_content_' . str_replace( '-', '_', sanitize_title( $company->post_status ) ), $company );
		break;
endswitch;

do_action( 'cariera_company_submitted_content_after', sanitize_title( $company->post_status ), $company );
