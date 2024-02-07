<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Admin_Conditionals_Resume
 *
 * @since 1.7.10
 *
 */
class WP_Job_Manager_Field_Editor_Admin_Conditionals_Resume extends WP_Job_Manager_Field_Editor_Admin_Conditionals {


	/**
	 * Return Resume Fields
	 *
	 *
	 * @since 1.7.10
	 *
	 * @return mixed|void
	 */
	public function get_fields(){

		$fields = $this->admin->get_fields( 'resume' );

		return apply_filters( 'field_editor_conditionals_resume_get_fields', $fields, $this );
	}

}