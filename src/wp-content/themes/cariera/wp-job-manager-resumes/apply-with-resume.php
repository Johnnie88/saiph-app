<?php
/**
 * Apply with Resume content that displays on single job listings.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/apply-with-resume.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager-resumes
 * @category    Template
 * @version     1.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

if ( ! get_option( 'resume_manager_force_application' ) ) {
	echo '<hr/>';
}

if ( is_user_logged_in() && count( $resumes ) ) : ?>
	<form class="apply_with_resume" method="post">
		<p><?php esc_html_e( 'Apply using your online resume; just enter a short message to send your application.', 'cariera' ); ?></p>
		<p>
			<label for="resume_id"><?php esc_html_e( 'Online resume', 'cariera' ); ?>:</label>
			<select name="resume_id" id="resume_id" class="cariera-select2" required>
				<?php
				foreach ( $resumes as $resume ) {
					echo '<option value="' . esc_attr( absint( $resume->ID ) ) . '">' . esc_html( $resume->post_title ) . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<label><?php esc_html_e( 'Message', 'cariera' ); ?>:</label>
			<textarea name="application_message" cols="20" rows="4" required>
				<?php
				if ( isset( $_POST['application_message'] ) ) {
					echo esc_textarea( stripslashes( $_POST['application_message'] ) );
				} else {
					echo esc_html_x( 'To whom it may concern,', 'default cover letter', 'cariera' ) . "\n\n";

					printf( esc_html_x( 'I am very interested in the %s position at %s. I believe my skills and work experience make me an ideal candidate for this role. I look forward to speaking with you soon about this position.', 'default cover letter', 'cariera' ), $post->post_title, get_post_meta( $post->ID, '_company_name', true ) );

					echo "\n\n" . esc_html_x( 'Thank you for your consideration.', 'default cover letter', 'cariera' );
				}
				?>
			</textarea>
		</p>
		<p>
			<input type="submit" class="btn btn-main btn-effect" name="wp_job_manager_resumes_apply_with_resume" value="<?php esc_attr_e( 'Send Application', 'cariera' ); ?>" />
			<input type="hidden" name="job_id" value="<?php echo esc_attr( absint( $post->ID ) ); ?>" />
		</p>
	</form>
<?php else : ?>
	<form class="apply_with_resume" method="post" action="<?php echo esc_url( get_permalink( get_option( 'resume_manager_submit_resume_form_page_id' ) ) ); ?>">
		<p><?php esc_html_e( 'You can apply to this job and others using your online resume. Click the link below to submit your online resume and email your application to this employer.', 'cariera' ); ?></p>

		<p>
			<input type="submit" class="btn btn-main btn-effect" name="wp_job_manager_resumes_apply_with_resume_create" value="<?php esc_attr_e( 'Submit Resume &amp; Apply', 'cariera' ); ?>" />
			<input type="hidden" name="job_id" value="<?php echo esc_attr( absint( $post->ID ) ); ?>" />
		</p>
	</form>
<?php endif; ?>
