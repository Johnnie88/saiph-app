<?php
/**
 * File containing the class WP_Job_Manager_Applications_Job_Submission.
 *
 * @package wp-job-manager-applications
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Job_Manager_Applications_Job_Submission class.
 *
 * Adds the application form field to the job submission form.
 */
class WP_Job_Manager_Applications_Job_Submission {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'submit_job_form_fields', [ $this, 'application_form_field' ] );
		add_filter( 'job_manager_job_listing_data_fields', [ $this, 'applications_admin_fields' ] );
	}

	/**
	 * Add Application Form to Admin fields
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function applications_admin_fields( $fields = array() ) {
		$form_field = get_submit_job_application_form_field();
		if ( ! empty( $form_field ) ) {
			$fields['_application_form']             = $form_field;
			$fields['_application_form']['priority'] = 3;
		}

		return $fields;
	}

	/**
	 * Add the job deadline field to the submission form
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function application_form_field( $fields = array() ) {

		$form_field = get_submit_job_application_form_field();

		/**
		 * Filters whether to hide the application form dropdown.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $show Whether to hide or not.
		 */
		if ( ! empty( $form_field ) && ! apply_filters( 'job_application_hide_form_fields_dropdown', false ) ) {
			$fields['job']['application_form']             = $form_field;
			$fields['job']['application_form']['required'] = true;
			$fields['job']['application_form']['priority'] = 7;
		}

		return $fields;
	}
}

new WP_Job_Manager_Applications_Job_Submission();
