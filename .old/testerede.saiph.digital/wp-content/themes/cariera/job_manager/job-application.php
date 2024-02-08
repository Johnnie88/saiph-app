<?php
/**
 * Show job application when viewing a single job listing.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-application.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.31.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php
if ( $apply = get_the_job_application_method() ) :
	wp_enqueue_script( 'wp-job-manager-job-application' );
	?>

	<div class="job_application application">
		<?php do_action( 'job_application_start', $apply ); ?>

		<a href="#job-popup" class="application_button btn btn-main btn-effect popup-with-zoom-anim"><?php esc_html_e( 'Apply for job', 'cariera' ); ?></a>

		<div id="job-popup" class="small-dialog zoom-anim-dialog mfp-hide">
			<div class="job-app-msg">
				<div class="small-dialog-headline">
					<h3 class="title"><?php esc_html_e( 'Apply for this job', 'cariera' ); ?></h3>
				</div>

				<div class="small-dialog-content">
					<?php
						/**
						 * job_manager_application_details_email or job_manager_application_details_url hook
						 */
						do_action( 'job_manager_application_details_' . $apply->type, $apply );
					?>
				</div>
			</div>
		</div>

		<?php do_action( 'job_application_end', $apply ); ?>
	</div>

<?php endif; ?>
