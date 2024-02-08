<?php
/**
 * Single job listing.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-single-job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager
 * @category    Template
 * @since       1.0.0
 * @version     1.37.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

wp_enqueue_style( 'cariera-single-job-listing' );

$layout = cariera_single_job_layout();

if ( job_manager_user_can_view_job_listing( $post->ID ) ) {
	get_job_manager_template_part( 'single-job/single', 'job-listing-' . $layout );
} else {
	get_job_manager_template_part( 'access-denied', 'single-job_listing' );
}
