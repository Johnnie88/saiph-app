<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager_Form' ) ) {
	include( JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php' );
}

if ( ! class_exists( 'WP_Job_Manager_Form_Submit_Job' ) ) {
	// As of WPJM 1.25.2 and newer, the job_manager_multi_job_type() function is called in the init_fields() method, if for some reason
	// that function is not available, we have to manually load the files, and remove the core method that includes them to prevent fatal PHP errors.
	if ( defined( 'JOB_MANAGER_VERSION' ) && version_compare( JOB_MANAGER_VERSION, '1.25.2', 'ge' ) && ! function_exists( 'job_manager_multi_job_type' ) ) {
		remove_class_filter( 'after_setup_theme', 'WP_Job_Manager', 'include_template_functions', 11 );
		require_once JOB_MANAGER_PLUGIN_DIR . '/wp-job-manager-functions.php';
		require_once JOB_MANAGER_PLUGIN_DIR . '/wp-job-manager-template.php';
	}
	require_once JOB_MANAGER_PLUGIN_DIR . '/includes/forms/class-wp-job-manager-form-submit-job.php';
}

/**
 * Class WP_Job_Manager_Field_Editor_Job_Submit_Form
 *
 * @since 1.1.9
 *
 */
Class WP_Job_Manager_Field_Editor_Job_Submit_Form extends WP_Job_Manager_Form_Submit_Job {

	private $wpjm;
	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var \WP_Job_Manager_Field_Editor_Job_Submit_Form The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Returns static instance of class.
	 *
	 * @since @@since
	 *
	 * @return self
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function __construct() {
		// Nothing to construct, just here to override extending class and prevent init filters/actions
	}

	/**
	 * Null Job and Company $fields
	 *
	 * @depreciated 1.7.3   No longer used or required.
	 *
	 * @since       1.1.9
	 *
	 */
	function remove_traces() {
		$this->fields = null;
	}

	/**
	 * Null, and regenerate Job and Company $fields
	 *
	 * @depreciated 1.7.3 No longer used or required
	 *
	 * @since       1.1.9
	 *
	 * @param string $type
	 */
	function regenerate_fields( $type ) {

		$this->remove_traces();

		if ( $type == 'job' || $type == 'company' ) {
			$this->init_fields();
		}
	}

	/**
	 * Get Default Job Fields
	 *
	 * Will initialize fields (if not already initialized) and return only default fields, without any customizations,
	 * or custom fields.  Handled by WP_Job_Manager_Field_Editor_Fields::$forced_filter
	 *
	 * @since 1.1.9
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	function get_default_fields( $type ) {

		// Make sure fields are initialized and set
		$this->init_fields();

		return $this->get_fields( $type );
	}

	/**
	 * Force Check for Validation Errors
	 *
	 * @depreciated 1.7.3   No longer used, was used previously to do validation before allowing core validation.
	 *
	 * @since       1.1.9
	 *
	 * @return bool
	 * @throws \Exception
	 */
	function validation_errors() {

		// NO LONGER USED

		try {

			$values = $this->get_posted_fields();

			if ( is_wp_error( ( $return = $this->validate_fields( $values ) ) ) ) {
				throw new Exception( $return->get_error_message() );
			}

			$this->remove_traces();

			return false;

		} catch ( Exception $e ) {

			$this->remove_traces();

			return true;
		}

	}

}