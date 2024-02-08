<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager_Form' ) ) {
	include( JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php' );
}

if ( ! class_exists( 'MAS_WP_Job_Manager_Company_Form_Submit_Company' ) ) {
	$mas_cm_instance = MAS_WP_Job_Manager_Company::instance();
	require_once( $mas_cm_instance->plugin_dir . 'includes/forms/class-mas-wp-job-manager-company-form-submit-company.php' );
}

/**
 * Class WP_Job_Manager_Field_Editor_Plugins_MASCM_Submit
 *
 * @since @@since
 *
 */
class WP_Job_Manager_Field_Editor_Plugins_MASCM_Submit extends MAS_WP_Job_Manager_Company_Form_Submit_Company {

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var \WP_Job_Manager_Field_Editor_Company_Submit_Form The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Returns static instance of class.
	 *
	 * @return \WP_Job_Manager_Field_Editor_Company_Submit_Form
	 * @since @@since
	 *
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
	 * Null Company $fields
	 *
	 * @depreciated 1.7.3   No longer used or required.
	 *
	 * @since       1.10.0
	 *
	 */
	function remove_traces() {

		$this->fields = null;
	}

	/**
	 * Null and regenerate Company $fields
	 *
	 * @depreciated 1.7.3 No longer used or required
	 *
	 * @param string $type
	 *
	 * @since       1.10.0
	 *
	 */
	function regenerate_fields( $type ) {

		$this->remove_traces();

		if ( $type == 'company_fields' ) {
			$this->init_fields();
		}
	}

	/**
	 * Get default Company $fields
	 *
	 * @param string $type
	 *
	 * @return array
	 * @since 1.10.0
	 *
	 */
	function get_default_fields( $type ) {

		// Make sure fields are initialized and set
		$this->init_fields();

		return $this->get_fields( $type );
	}

	/**
	 * Force Check for Validation Errors
	 *
	 *
	 * @return bool
	 * @throws \Exception
	 * @since 1.10.0
	 *
	 */
	function validation_errors() {

		try {

			$values = $this->get_posted_fields();

			if ( is_wp_error( ( $return = $this->validate_fields( $values ) ) ) ) {
				throw new Exception( true );
			}

			$this->remove_traces();

			return false;

		} catch ( Exception $e ) {

			$this->remove_traces();

			return true;
		}

	}
}