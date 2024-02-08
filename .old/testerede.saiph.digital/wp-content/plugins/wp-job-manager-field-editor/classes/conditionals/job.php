<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Conditionals
 *
 * @since 1.7.10
 *
 */
class WP_Job_Manager_Field_Editor_Conditionals_Job extends WP_Job_Manager_Field_Editor_Conditionals {

	/**
	 *
	 *
	 *
	 * @since 1.8.1
	 *
	 * @return string
	 */
	public function get_slug(){
		return 'job';
	}

	/**
	 * Actions/Filters
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function hooks(){

		add_action( 'submit_job_form_job_fields_start', array( $this, 'form' ) );

	}

	/**
	 * Add filter on fields (to set required false)
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function add_fields_filter() {

		if ( empty( $_POST['submit_job'] ) ) {
			return;
		}

		add_filter( 'submit_job_form_fields', array( $this, 'set_required_false' ), 9999999999 );
		add_action( 'job_manager_user_edit_job_listing', array( $this, 'reset_fields_back' ) );
	}

	/**
	 * Reset Fields Back
	 *
	 * This method resets/clears the edit class instance fields so they can be re-init when $this->init_fields() is called
	 * again before displaying the job-submit.php template file.  This is required to prevent the template from showing the
	 * fields array after conditional logic fields not used were removed to prevent excessive post meta inserts for fields
	 * that are not used on the listing.
	 *
	 *
	 * @return bool
	 * @since 1.10.0
	 *
	 */
	public function reset_fields_back() {

		if ( empty( $this->removed_logic_fields ) || ! apply_filters( 'field_editor_conditional_logic_reset_job_fields_after_edit_listing', true, $this ) ) {
			return true;
		}

		$ei = WP_Job_Manager_Form_Edit_Job::instance();
		// Method won't exist until WP Job Manager 1.34.2+
		// @see https://github.com/Automattic/WP-Job-Manager/pull/1949
		if ( method_exists( $ei, 'clear_fields' ) ) {
			remove_filter( 'submit_job_form_fields', array( $this, 'set_required_false' ), 9999999999 );
			$ei->clear_fields();
		}
	}

	/**
	 * Get Logic Configuration
	 *
	 *
	 * @since 1.7.10
	 *
	 * @return array|bool
	 */
	public function get_logic(){

		if( $this->logic !== null ){
			return $this->logic;
		}

		$logic = get_option( 'field_editor_job_listing_conditional_logic', array() );

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

		if( $this->fields ){
			return $this->fields;
		}

		$jmfe = WP_Job_Manager_Field_Editor_Fields::get_instance();

		$job_fields     = $jmfe->get_fields( 'job' );
		$company_fields = $jmfe->get_fields( 'company' );

		$this->fields   = array_merge( $job_fields, $company_fields );

		return $this->fields;

	}

	/**
	 * Get listing ID when editing listing
	 *
	 *
	 * @since 1.8.1
	 *
	 * @return bool|int
	 */
	public function get_edit_listing_id(){

		if( array_key_exists( 'job_id', $_GET ) ){
			return absint( $_GET['job_id'] );
		}

		return false;
	}

	/**
	 * Try to get Job ID from Class Object
	 *
	 *
	 * @since 1.8.5
	 *
	 * @return bool|int
	 */
	public function get_class_listing_id() {

		$job_id = false;

		if ( class_exists( 'WP_Job_Manager_Form_Submit_Job' ) && method_exists( 'WP_Job_Manager_Form_Submit_Job', 'instance' ) ) {

			$rm = WP_Job_Manager_Form_Submit_Job::instance();

			if ( $rm && method_exists( $rm, 'get_job_id' ) ) {
				$job_id = WP_Job_Manager_Form_Submit_Job::instance()->get_job_id();
			}

		}

		// Return false for any "empty" values returned
		if ( empty( $job_id ) ) {
			return false;
		}

		return $job_id;
	}
}