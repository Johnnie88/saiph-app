<?php
/**
 * Notice shown on `[past_applications]` shortcode when a user hasn't applied to any jobs.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-applications/past-applications-none.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager-applications
 * @category    Template
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

esc_html_e( 'You haven\'t made any applications yet!', 'wp-job-manager-applications' );
