<?php
/**
 * Footer shown below a job application.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-applications/job-application-footer.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager-applications
 * @category    Template
 * @version     2.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_post_statuses;

$rating = get_job_application_rating( $application->ID ); ?>

<div class="rating <?php echo cariera_get_rating_class( $rating ); ?>">
	<div class="star-rating"></div>
	<div class="star-bg"></div>
</div>

<ul class="meta">
	<li><i class="far fa-file-alt"></i><?php echo esc_html( $wp_post_statuses[ $application->post_status ]->label ); ?></li>
	<li><i class="far fa-calendar-alt"></i> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $application->post_date ) ) ); ?></li>
</ul>
