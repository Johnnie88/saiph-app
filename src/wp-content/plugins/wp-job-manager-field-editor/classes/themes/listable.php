<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Field_Editor_Themes_Listable {

	function __construct() {
		add_filter( 'job_manager_field_editor_admin_skip_fields', array( $this, 'admin_fields' ) );
		add_filter( 'job_manager_field_editor_js_conf_meta_keys', array( $this, 'company_logo' ) );
		add_filter( 'job_manager_field_editor_package_remove_old_meta', array( $this, 'package_change' ) );
		add_filter( 'submit_job_form_fields', array( $this, 'company_logo_check' ), 101 );
		// Listable currently adds their auto output locations through the theme
		// add_filter( 'field_editor_output_options', array($this, 'auto_output'), 10, 2 );
		add_action( 'submit_job_form_job_fields_start', array( $this, 'form_fields_start' ) );
	}

	/**
	 * Check if company_logo is Configured Correctly
	 *
	 * Listable versions older than 1.4.1 require the company_logo field to be set as multiple, newer
	 * versions it should be set back to single only as originally was with core WP Job Manager.
	 *
	 *
	 * @since 1.4.6
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	function company_logo_check( $fields ){

		if( ! is_array( $fields ) || ! isset( $fields['company'], $fields['company']['company_logo'] ) ) return $fields;

		$old_version = WP_Job_Manager_Field_Editor_Integration::check_theme( 'listable', '1.4.1', 'version', 'lt', TRUE );
		$company_logo_multiple_true = apply_filters( 'job_manager_field_editor_listable_company_logo_multiple_true', FALSE );

		/**
		 * Version 1.4.1 changed company_logo to no longer be required to be set to TRUE, so we need to set it to FALSE
		 * to match the core WP Job Manager handling.
		 *
		 * If user is using a version older than 1.4.1, then company_logo must have multiple set as TRUE.
		 */
		if( ! $old_version && ! $company_logo_multiple_true ){
			$fields['company']['company_logo']['multiple'] = FALSE;
		} elseif( $old_version || $company_logo_multiple_true ) {
			/**
			 * Setting multiple to TRUE for old versions, should fix problems for users that saved the company_logo
			 * field before I could require it to be set to TRUE when saving.
			 */
			$fields['company']['company_logo']['multiple'] = TRUE;
		}

		return $fields;
	}

	/**
	 * Fields to skip when output in admin section
	 *
	 *
	 * @since 1.4.2
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	function admin_fields( $fields ){

		$fields[] = 'company_logo';
		/**
		 * main_image is used for gallery images now
		 */
		$fields[] = 'main_image';

		return $fields;
	}

	/**
	 * Set company_logo multiple checkbox as hidden input
	 *
	 *
	 * @since 1.4.2
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	function company_logo( $data ){

		/**
		 * Listable >= 1.4.1 now uses just the main_image meta key, we only need to set multiple as required
		 * if they are using Listable version older than 1.4.1
		 */
		$old_version = WP_Job_Manager_Field_Editor_Integration::check_theme( 'listable', '1.4.1', 'version', 'lt', true );

		if( $old_version ){
			$data['company_logo']['hidden_input'] = array( 'multiple_0' => '1' );
		}

		return $data;
	}

	/**
	 * Add additional meta to remove when package upgraded/downgraded
	 *
	 *
	 * @since 1.4.2
	 *
	 * @param $metakeys
	 *
	 * @return mixed
	 */
	function package_change( $metakeys ){

		/**
		 * Listable >= 1.4.1 now uses just the main_image meta key, we only need to set multiple as required
		 * if they are using Listable version older than 1.4.1
		 */
		$old_version = WP_Job_Manager_Field_Editor_Integration::check_theme( 'listable', '1.4.1', 'version', 'lt', TRUE );

		if( $old_version ){
			$metakeys['company_logo'] = 'main_image';
		}

		return $metakeys;
	}

	/**
	 * Listable Theme custom action output areas
	 *
	 * Requires Listable 1.0.2 or newer
	 *
	 * @since @@since
	 *
	 * @param $current_options
	 * @param $type
	 *
	 * @return array|bool
	 */
	function auto_output( $current_options, $type ) {
		// Listable currently adds their auto output locations through the theme
		return $current_options;
	}

	/**
	 * Output custom CSS before form fields
	 *
	 *
	 * @since 1.7.2
	 *
	 */
	function form_fields_start(){
		// Make sure checkbox fields show inline
		echo '<style>fieldset .field .input-checkbox { float: left; margin-right: 5px; }</style>';
	}
}