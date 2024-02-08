<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Conditionals_Company
 *
 * @since 1.12.10
 *
 */
class WP_Job_Manager_Field_Editor_Conditionals_Company extends WP_Job_Manager_Field_Editor_Conditionals {

	/**
	 *
	 *
	 *
	 * @return string
	 * @since 1.12.10
	 *
	 */
	public function get_slug() {

		return 'company';
	}

	/**
	 * Actions/Filters
	 *
	 *
	 * @since 1.12.10
	 *
	 */
	public function hooks() {

		// Would use submit_company_form_company_fields_start but not available in all
		add_action( 'submit_company_form_company_fields_end', array( $this, 'form' ) );

	}

	/**
	 * Add Filter for Fields (to set required false)
	 *
	 *
	 * @since 1.12.10
	 *
	 */
	public function add_fields_filter() {

		if ( empty( $_POST['submit_company'] ) ) {
			return;
		}

//		add_filter( 'submit_company_form_fields', array( $this, 'set_required_false' ), 9999999999 );
//		add_filter( 'cariera_submit_company_form_fields', array( $this, 'set_required_false' ), 9999999999 );
//		add_filter( 'company_manager_update_company_data', array( $this, 'reset_fields_back' ) );
	}

	/**
	 * Reset Fields Back
	 *
	 * This method resets/clears the edit class instance fields so they can be re-init when $this->init_fields() is called
	 * again before displaying the resume-submit.php template file.  This is required to prevent the template from showing the
	 * fields array after conditional logic fields not used were removed to prevent excessive post meta inserts for fields
	 * that are not used on the listing.
	 *
	 * Since Companys does not have an action when a user updates/edits a listing, we have to use the filter for the resume
	 * updated message, to determine when to clear the fields.
	 *
	 *
	 * @return bool
	 * @since 1.12.10
	 *
	 */
	public function reset_fields_back( $message ) {

		if ( empty( $this->removed_logic_fields ) || ! apply_filters( 'field_editor_conditional_logic_reset_company_fields_after_edit_listing', true, $this ) ) {
			return $message;
		}

		$int = WP_Job_Manager_Field_Editor_Integration_Company::get_instance();

		$ei = WP_Company_Manager_Form_Edit_Company::instance();
		// Method won't exist until WP Job Manager 1.34.2+
		// @see https://github.com/Automattic/WP-Job-Manager/pull/1949
		if ( method_exists( $ei, 'clear_fields' ) ) {
			remove_filter( 'submit_company_form_fields', array( $this, 'set_required_false' ), 9999999999 );
			$ei->clear_fields();
		}

		return $message;
	}

	/**
	 * Get Logic Fields
	 *
	 *
	 * @return array|bool
	 * @since 1.12.10
	 *
	 */
	public function get_logic() {

		if ( $this->logic !== null ) {
			return $this->logic;
		}

		$logic = get_option( 'field_editor_company_conditional_logic', array() );

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
	 * @since 1.12.10
	 *
	 */
	public function get_fields() {

		$jmfe   = WP_Job_Manager_Field_Editor_Fields::get_instance();
		$fields = $jmfe->get_fields( 'company_fields' );

		return $fields;

	}

	/**
	 * Get listing ID when editing listings
	 *
	 *
	 * @return bool|int
	 * @since 1.8.1
	 *
	 */
	public function get_edit_listing_id() {

		if ( array_key_exists( 'company_id', $_GET ) ) {
			return absint( $_GET['company_id'] );
		}

		return false;
	}

	/**
	 * Try to get Company ID from Class Object
	 *
	 *
	 * @return bool|int
	 * @since 1.8.5
	 *
	 */
	public function get_class_listing_id() {

		$company_id = false;

		if ( class_exists( 'WP_Company_Manager_Form_Submit_Company' ) && method_exists( 'WP_Company_Manager_Form_Submit_Company', 'instance' ) ) {

			$rm = WP_Company_Manager_Form_Submit_Company::instance();

			if ( $rm && method_exists( $rm, 'get_company_id' ) ) {
				$company_id = WP_Company_Manager_Form_Submit_Company::instance()->get_company_id();
			}

		}

		// Return false for any "empty" values returned
		if ( empty( $company_id ) ) {
			return false;
		}

		return $company_id;
	}
}