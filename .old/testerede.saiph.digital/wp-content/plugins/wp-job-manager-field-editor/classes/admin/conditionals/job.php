<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Job_Manager_Field_Editor_Admin_Conditionals_Job extends WP_Job_Manager_Field_Editor_Admin_Conditionals {


	/**
	 * Return Job Fields
	 *
	 *
	 * @since 1.7.10
	 *
	 * @return mixed|void
	 */
	public function get_fields(){

		$company_fields = $this->admin->get_fields( 'company' );
		$job_fields = $this->admin->get_fields( 'job' );

		$all_fields = array_merge( $company_fields, $job_fields );

		return apply_filters( 'field_editor_job_conditinals_get_fields', $all_fields, $this );
	}
}