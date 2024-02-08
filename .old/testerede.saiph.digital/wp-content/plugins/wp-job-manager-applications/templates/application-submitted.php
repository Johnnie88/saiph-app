<?php
/**
 * Notice shown when a user has successfully applied for a job.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-applications/application-submitted.php.
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
?>
<p class="job-manager-message">
	<?php esc_html_e( 'Your job application has been submitted successfully', 'wp-job-manager-applications' ); ?>
</p>
