<?php
/**
 * Notice when job has been submitted.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-submitted.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager
 * @category    Template
 * @version     1.34.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $wp_post_types;

switch ( $job->post_status ) :
	case 'publish':
		echo '<div class="job-submitted">' . wp_kses_post(
			sprintf(
				esc_html__( '%1$s listed successfully. To view your listing %2$sclick here%3$s.', 'cariera' ),
				esc_html( $wp_post_types['job_listing']->labels->singular_name ),
				'<a href="' . esc_url( get_permalink( $job->ID ) ) . '">',
				'</a>'
			)
		) . '</div>';
		break;
	case 'pending':
		echo '<div class="job-submitted">' . wp_kses_post(
			sprintf(
				esc_html__( '%s submitted successfully. Your listing will be visible once approved.', 'cariera' ),
				esc_html( $wp_post_types['job_listing']->labels->singular_name ),
				esc_url( get_permalink( $job->ID ) )
			)
		) . '</div>';
		break;
	default:
		do_action( 'job_manager_job_submitted_content_' . str_replace( '-', '_', sanitize_title( $job->post_status ) ), $job );
		break;
endswitch;

do_action( 'job_manager_job_submitted_content_after', sanitize_title( $job->post_status ), $job );
