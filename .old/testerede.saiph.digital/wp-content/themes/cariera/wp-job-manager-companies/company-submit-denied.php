<?php
/**
 * Custom: Company - Company Submission Denied
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-submit-denied.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.4
 * @version     1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
} ?>

<div class="job-manager-message error"><?php esc_html_e( 'You have exceeded the limit of company submissions, you can not post another company.', 'cariera' ); ?></div>
