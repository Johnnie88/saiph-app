<?php
/**
 * Custom: Company Access Denied Browse Companies
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/access-denied-browse-companies.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.7
 * @version     1.4.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
} ?>

<p class="job-manager-error"><?php esc_html_e( 'Sorry, you do not have permission to browse companies.', 'cariera' ); ?></p>
