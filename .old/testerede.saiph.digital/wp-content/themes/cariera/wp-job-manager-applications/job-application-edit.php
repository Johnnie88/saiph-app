<?php
/**
 * Form to allow an application reviewer to change the application status and add a rating or notes.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-applications/job-application-edit.php.
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
<form class="job-manager-application-edit-form job-manager-form" method="post">

	<fieldset class="fieldset-status">
		<label for="application-status-<?php echo esc_attr( $application->ID ); ?>"><?php esc_html_e( 'Application status', 'cariera' ); ?>:</label>
		<div class="field">
			<select class="cariera-select2" id="application-status-<?php echo esc_attr( $application->ID ); ?>" name="application_status">
				<?php foreach ( get_job_application_statuses() as $name => $label ) : ?>
					<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $application->post_status, $name ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</fieldset>

	<fieldset class="fieldset-rating">
		<label for="application-rating-<?php esc_attr( absint( $application->ID ) ); ?>"><?php esc_html_e( 'Rating (out of 5)', 'cariera' ); ?>:</label>
		<div class="field">
			<input type="number" id="application-rating-<?php echo esc_attr( absint( $application->ID ) ); ?>" name="application_rating" step="0.5" max="5" min="0" placeholder="0" value="<?php echo esc_attr( get_job_application_rating( $application->ID ) ); ?>" />
		</div>
	</fieldset>

	<p class="application-action-buttons">
		<input class="btn btn-main btn-effect" type="submit" name="wp_job_manager_edit_application" value="<?php esc_attr_e( 'Save changes', 'cariera' ); ?>" />
		<input type="hidden" name="application_id" value="<?php echo esc_attr( absint( $application->ID ) ); ?>" />
		<?php wp_nonce_field( 'edit_job_application' ); ?>
		<a class="delete_job_application btn btn-main btn-effect" href="<?php echo wp_nonce_url( add_query_arg( 'delete_job_application', $application->ID ), 'delete_job_application' ); ?>"><?php esc_html_e( 'Delete this application', 'cariera' ); ?></a>
	</p>
</form>
