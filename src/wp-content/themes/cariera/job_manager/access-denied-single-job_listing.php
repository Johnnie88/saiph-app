<?php
/**
 * Message to display when access is denied to a single job listing.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/access-denied-single-job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @since       1.37.0
 * @version     1.37.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<main class="ptb80">
	<div class="container">
		<?php if ( $post->post_status === 'expired' ) : ?>
			<div class="job-manager-info"><?php esc_html_e( 'This listing has expired', 'cariera' ); ?></div>
		<?php else : ?>
			<p class="job-manager-error"><?php esc_html_e( 'Sorry, you do not have permission to view this job listing.', 'cariera' ); ?></p>
		<?php endif; ?>
	</div>
</div>
