<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Job_Manager_Field_Editor_Plugins_MASCM {

	/**
	 * WP_Job_Manager_Field_Editor_Plugins_MASCM constructor.
	 */
	public function __construct() {
		// We return true because function exists (initialized this class), so we know the plugin is active
		add_filter( 'field_editor_wpcm_active', '__return_true' );
		add_filter( 'field_editor_company_submit_form_class_name', array( $this, 'submit_form_class_name' ) );
		add_filter( 'field_editor_get_company_id_from_job_company_meta_key', array( $this, 'get_company_id_meta_key' ) );
		add_filter( 'field_editor_output_options_wpcm', array( $this, 'output_options' ) );
	}

	/**
	 * Return Company Output Locations
	 *
	 * @param $options
	 *
	 * @return mixed
	 * @since 1.10.2
	 *
	 */
	public function output_options( $options ) {

		$options = array(
			'single_company_mas_page' => '---' . __( "MAS Company Manager Locations", 'wp-job-manager-field-editor' ),
			'single_company_before_start'   => __( 'Single Company Before Start', 'wp-job-manager-field-editor' ),
			'single_company_start'     => __( 'Single Company Start', 'wp-job-manager-field-editor' ),
			'single_company'        => __( 'Single Company', 'wp-job-manager-field-editor' ),
			'single_company_end'          => __( 'Single Company End', 'wp-job-manager-field-editor' ),
			'single_company_after_end' => __( 'Single Company After End', 'wp-job-manager-field-editor' ),
			'the_company_description'     => __( 'Bottom of Company Description', 'wp-job-manager-field-editor' ),
			'company_content_area_before'     => __( 'Company Content Area Before', 'wp-job-manager-field-editor' ),
			'company_start'     => __( 'Company Start', 'wp-job-manager-field-editor' ),
			'company'     => __( 'Company', 'wp-job-manager-field-editor' ),
			'company_end'     => __( 'Company End', 'wp-job-manager-field-editor' ),
			'company_content_area_after'     => __( 'Company Content Area After', 'wp-job-manager-field-editor' ),
		);

		return $options;
	}

	/**
	 * Return MAS Company ID Meta Key
	 *
	 * @param $meta_key
	 *
	 * @return string
	 * @since 1.10.2
	 *
	 */
	public function get_company_id_meta_key( $meta_key ) {
		return '_company_id';
	}

	/**
	 * Return Custom Class Name for MAS Company Manager
	 *
	 * @param $name
	 *
	 * @return string
	 * @since 1.10.2
	 *
	 */
	public function submit_form_class_name( $name ) {
		return 'WP_Job_Manager_Field_Editor_Plugins_MASCM_Submit';
	}
}

if ( class_exists( 'MAS_WP_Job_Manager_Company' ) ) {
	new WP_Job_Manager_Field_Editor_Plugins_MASCM();
}
