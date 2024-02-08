<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Job_Manager_Form' ) )
	include( JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php' );

if ( ! class_exists( 'WP_Resume_Manager_Form_Submit_Resume' ) )
	require_once( RESUME_MANAGER_PLUGIN_DIR . '/includes/forms/class-wp-resume-manager-form-submit-resume.php' );

/**
 * Class WP_Job_Manager_Field_Editor_Resume_Submit_Form
 *
 * @since 1.1.9
 *
 */
Class WP_Job_Manager_Field_Editor_Resume_Submit_Form extends WP_Resume_Manager_Form_Submit_Resume {

	private $wprm;
	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var \WP_Job_Manager_Field_Editor_Resume_Submit_Form The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Returns static instance of class.
	 *
	 * @since @@since
	 *
	 * @return \WP_Job_Manager_Field_Editor_Resume_Submit_Form
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
	 * Null Resume $fields
	 *
	 * @depreciated 1.7.3   No longer used or required.
	 *
	 * @since 1.1.9
	 *
	 */
	function remove_traces(){
		$this->fields = null;
	}

	/**
	 * Null and regenerate Resume $fields
	 *
	 * @depreciated 1.7.3 No longer used or required
	 *
	 * @since 1.1.9
	 *
	 * @param string $type
	 */
	function regenerate_fields( $type ){

		$this->remove_traces();

		if( $type == 'resume_fields' ){
			$this->init_fields();
		}
	}

	/**
	 * Get default Resume $fields
	 *
	 * @since 1.1.9
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	function get_default_fields( $type ){

		// Make sure fields are initialized and set
		$this->init_fields();

		return $this->get_fields( $type );
	}

	/**
	 * Force Check for Validation Errors
	 *
	 *
	 * @since 1.1.9
	 *
	 * @return bool
	 * @throws \Exception
	 */
	function validation_errors() {

		try {

			$values   = $this->get_posted_fields();

			if ( is_wp_error( ( $return = $this->validate_fields( $values ) ) ) ) {
				throw new Exception( TRUE );
			}

			$this->remove_traces();

			return FALSE;

		} catch ( Exception $e ) {

			$this->remove_traces();
			return TRUE;
		}

	}
}