<?php
/**
 * File containing the WP_Resume_Manager_Forms.
 *
 * @package wp-job-manager-resumes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Resume_Manager_Forms class.
 */
class WP_Resume_Manager_Forms {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'load_posted_form' ] );
	}

	/**
	 * If a form was posted, load its class so that it can be processed before display.
	 */
	public function load_posted_form() {
		if ( ! empty( $_POST['resume_manager_form'] ) ) {
			$this->load_form_class( sanitize_title( $_POST['resume_manager_form'] ) );
		}
	}

	/**
	 * Load a form's class
	 *
	 * @param  string $form_name
	 * @return string class name on success, false on failure
	 */
	private function load_form_class( $form_name ) {
		if ( ! class_exists( 'WP_Job_Manager_Form' ) ) {
			include JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php';
		}

		// Now try to load the form_name
		$form_class = 'WP_Resume_Manager_Form_' . str_replace( '-', '_', $form_name );
		$form_file  = RESUME_MANAGER_PLUGIN_DIR . '/includes/forms/class-wp-resume-manager-form-' . $form_name . '.php';

		if ( class_exists( $form_class ) ) {
			return call_user_func( [ $form_class, 'instance' ] );
		}

		if ( ! file_exists( $form_file ) ) {
			return false;
		}

		if ( ! class_exists( $form_class ) ) {
			include $form_file;
		}

		// Init the form
		return call_user_func( [ $form_class, 'instance' ] );
	}

	/**
	 * get_form function.
	 *
	 * @param string $form_name
	 * @param  array  $atts Optional passed attributes
	 * @return string
	 */
	public function get_form( $form_name, $atts = [] ) {
		if ( $form = $this->load_form_class( $form_name ) ) {
			ob_start();
			$form->output( $atts );
			return ob_get_clean();
		}
	}
}
