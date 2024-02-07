<?php
/**
 * Custom: Company Access Denied Single Company
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/access-denied-single-company.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.7
 * @version     1.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
} ?>

<main class="ptb80">
	<div class="container">
		<p class="job-manager-error"><?php esc_html_e( 'Sorry, you do not have permission to view this company.', 'cariera' ); ?></p>
	</div>
</main>
