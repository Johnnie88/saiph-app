<?php
/**
 * Custom: Single Company Page - Print listing
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/single-company/single-company-print.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.1
 * @version     1.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<a class="print-page" href="javascript:void(0)" onclick="window.print();" aria-label="<?php esc_attr_e( 'Print', 'cariera' ); ?>"><i class="fas fa-print"></i><?php esc_html_e( 'Print Company', 'cariera' ); ?></a>