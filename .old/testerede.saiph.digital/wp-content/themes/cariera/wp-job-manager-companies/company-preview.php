<?php
/**
 * Custom: Company - Company Preview Submission Step
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-preview.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<form method="post" id="company_preview" action="<?php echo esc_url( $form->get_action() ); ?>">
	<?php
	/**
	 * Fires at the top of the preview job form.
	 *
	 * @since 1.7.0
	 */
	do_action( 'preview_company_form_start' );
	?>
	<div class="company_preview_title cariera-listing-submission">
		<div class="submission-progress"></div>
		<input type="submit" name="edit_company" class="button" value="<?php esc_attr_e( '&larr; Edit Company', 'cariera' ); ?>" />
		<?php do_action( 'cariera_company_submission_steps' ); ?>
		<input type="submit" name="continue" id="company_preview_submit_button" class="button" value="<?php echo apply_filters( 'cariera_submit_company_step_preview_text', esc_attr__( 'Submit Company &rarr;', 'cariera' ) ); ?>" />
		<input type="hidden" name="company_id" value="<?php echo esc_attr( $form->get_company_id() ); ?>" />
		<input type="hidden" name="job_id" value="<?php echo esc_attr( $form->get_job_id() ); ?>" />
		<input type="hidden" name="step" value="<?php echo esc_attr( $form->get_step() ); ?>" />
		<input type="hidden" name="company_manager_form" value="<?php echo esc_attr( $form->form_name ); ?>" />
	</div>
	<div class="company-page company-preview single-company">
		<?php get_job_manager_template_part( 'content-single', 'company', 'wp-job-manager-companies' ); ?>
	</div>
</form>
