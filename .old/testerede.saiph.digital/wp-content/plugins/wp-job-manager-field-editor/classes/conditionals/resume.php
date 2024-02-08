<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Conditionals_Resume
 *
 * @since 1.7.10
 *
 */
class WP_Job_Manager_Field_Editor_Conditionals_Resume extends WP_Job_Manager_Field_Editor_Conditionals {

	/**
	 *
	 *
	 *
	 * @since 1.8.1
	 *
	 * @return string
	 */
	public function get_slug(){
		return 'resume';
	}

	/**
	 * Actions/Filters
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function hooks() {

		add_action( 'submit_resume_form_resume_fields_start', array( $this, 'form' ) );

	}

	/**
	 * Return Repeatable Fields
	 *
	 *
	 * @since 1.8.0
	 *
	 * @return array|mixed|void
	 */
	public function get_repeatable_fields(){

		$repeatables = array( 'candidate_education', 'candidate_experience', 'links' );

		return apply_filters( 'field_editor_resume_conditional_logic_repeatable_fields', $repeatables );
	}

	/**
	 * Add Filter for Fields (to set required false)
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function add_fields_filter() {

		if ( empty( $_POST['submit_resume'] ) ) {
			return;
		}

		add_filter( 'submit_resume_form_fields', array( $this, 'set_required_false' ), 9999999999 );
		add_filter( 'resume_manager_update_resume_listings_message', array( $this, 'reset_fields_back' ) );
	}

	/**
	 * Reset Fields Back
	 *
	 * This method resets/clears the edit class instance fields so they can be re-init when $this->init_fields() is called
	 * again before displaying the resume-submit.php template file.  This is required to prevent the template from showing the
	 * fields array after conditional logic fields not used were removed to prevent excessive post meta inserts for fields
	 * that are not used on the listing.
	 *
	 * Since Resumes does not have an action when a user updates/edits a listing, we have to use the filter for the resume
	 * updated message, to determine when to clear the fields.
	 *
	 *
	 * @return bool
	 * @since 1.10.0
	 *
	 */
	public function reset_fields_back( $message ) {

		if ( empty( $this->removed_logic_fields ) || ! apply_filters( 'field_editor_conditional_logic_reset_resume_fields_after_edit_listing', true, $this ) ) {
			return $message;
		}

		$ei = WP_Resume_Manager_Form_Edit_Resume::instance();
		// Method won't exist until WP Job Manager 1.34.2+
		// @see https://github.com/Automattic/WP-Job-Manager/pull/1949
		if ( method_exists( $ei, 'clear_fields' ) ) {
			remove_filter( 'submit_resume_form_fields', array( $this, 'set_required_false' ), 9999999999 );
			$ei->clear_fields();
		}

		return $message;
	}

	/**
	 * Get Logic Fields
	 *
	 *
	 * @since 1.7.10
	 *
	 * @return array|bool
	 */
	public function get_logic(){

		if ( $this->logic !== null ) {
			return $this->logic;
		}

		$logic = get_option( 'field_editor_resume_conditional_logic', array() );

		// Remove any disabled field groups
		$this->logic = wp_list_filter( $logic, array( 'status' => 'disabled' ), 'NOT' );

		if ( empty( $this->logic ) ) {
			return false;
		}

		return $this->logic;
	}

	/**
	 * Get Fields
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function get_fields() {

		$jmfe   = WP_Job_Manager_Field_Editor_Fields::get_instance();
		$fields = $jmfe->get_fields( 'resume_fields' );

		return $fields;

	}

	/**
	 * Get listing ID when editing listings
	 *
	 *
	 * @since 1.8.1
	 *
	 * @return bool|int
	 */
	public function get_edit_listing_id() {

		if ( array_key_exists( 'resume_id', $_GET ) ) {
			return absint( $_GET['resume_id'] );
		}

		return false;
	}

	/**
	 * Try to get Resume ID from Class Object
	 *
	 *
	 * @since 1.8.5
	 *
	 * @return bool|int
	 */
	public function get_class_listing_id(){

		$resume_id = false;

		if( class_exists( 'WP_Resume_Manager_Form_Submit_Resume' ) && method_exists( 'WP_Resume_Manager_Form_Submit_Resume', 'instance' ) ){

			$rm = WP_Resume_Manager_Form_Submit_Resume::instance();

			if( $rm && method_exists( $rm, 'get_resume_id' ) ){
				$resume_id = WP_Resume_Manager_Form_Submit_Resume::instance()->get_resume_id();
			}

		}

		// Return false for any "empty" values returned
		if( empty( $resume_id ) ){
			return false;
		}

		return $resume_id;
	}
}