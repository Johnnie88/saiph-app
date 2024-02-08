<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Themes_Cariera_Company_Fields
 *
 * @since 1.10.0
 *
 */
class WP_Job_Manager_Field_Editor_Themes_Cariera_Company_Fields extends WP_Job_Manager_Field_Editor_Company_Fields {

	function __construct() {

		add_filter( 'cariera_submit_company_form_fields', array( $this, 'init_fields' ), 100 );
		add_filter( 'cariera_submit_company_form_fields_get_company_data', array( $this, 'get_company_data' ), 100, 2 );
		add_filter( 'cariera_company_manager_fields', array( $this, 'admin_fields' ), 100 );

		add_action( 'cariera_update_company_data', array( $this, 'save_fields' ), 100, 2 );
		add_action( 'cariera_save_company', array( $this, 'save_admin_fields' ), 100, 2 );

		add_filter( 'cariera_submit_company_form_fields_get_user_data', array( $this, 'get_user_data' ), 100, 2 );
		add_filter( 'cariera_submit_company_form_required_label', array( $this, 'custom_required_label' ), 100, 2 );
		add_filter( 'cariera_submit_company_form_submit_button_text', array( $this, 'custom_submit_button' ), 100 );

		add_action( 'submit_company_form_start', array( $this, 'company_package_field' ) );

		add_filter( 'cariera_submit_company_form_save_company_data', array( $this, 'allow_empty_post_content' ), 99999, 5 );

		add_filter( 'cariera_submit_company_form_validate_fields', array( $this, 'check_uploads' ), 9999, 3 );
	}

}