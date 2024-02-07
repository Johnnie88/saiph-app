<?php
/**
 * Custom: Company - Single Company
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/content-single-company.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.3.0
 * @version     1.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

wp_enqueue_style( 'cariera-single-company' );

$layout = cariera_single_company_layout();

if ( cariera_user_can_view_company( $post->ID ) ) {
	get_job_manager_template_part( 'single-company/single', 'company-' . $layout, 'wp-job-manager-companies' );
} else {
	get_job_manager_template_part( 'access-denied', 'single-company', 'wp-job-manager-companies' );
}
