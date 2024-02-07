<?php
/**
 * Custom: Company - Company Submit
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-submit.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.4
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$submission_limit = get_option( 'cariera_company_submission_limit' );
$company_count    = cariera_count_user_companies();

wp_enqueue_style( 'cariera-wpjm-submissions' );
wp_enqueue_script( 'cariera-company-manager-submission' ); ?>

<form action="<?php echo esc_url( $action ); ?>" method="post" id="submit-company-form" class="job-manager-form" enctype="multipart/form-data">
	<?php
	do_action( 'submit_company_form_start' );

	if ( apply_filters( 'submit_company_form_show_signin', true ) ) {
		get_job_manager_template( 'account-signin.php', [ 'class' => $class ], 'wp-job-manager-companies' );
	}

	if ( cariera_user_can_post_company() ) {
		// Company Fields.
		get_job_manager_template(
			'company-submit-fields.php',
			[
				'class'          => $class,
				'form'           => $form,
				'company_id'     => $company_id,
				'job_id'         => $job_id,
				'action'         => $action,
				'company_fields' => $company_fields,
				'step'           => $step,
			],
			'wp-job-manager-companies'
		);
		?>

		<?php do_action( 'submit_company_form_company_fields_end' ); ?>

		<div class="cariera-listing-submission">
			<div class="submission-progress"></div>
			<input type="hidden" name="company_manager_form" value="<?php echo esc_attr( $form ); ?>" />
			<input type="hidden" name="company_id" value="<?php echo esc_attr( $company_id ); ?>" />
			<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
			<?php do_action( 'cariera_company_submission_steps' ); ?>
			<input type="submit" name="submit_company" class="button" value="<?php echo esc_attr( $submit_button_text ); ?>" />
		</div>

		<?php
	} else {
		do_action( 'submit_company_form_disabled' );
	}

	do_action( 'submit_company_form_end' );
	?>
</form>
