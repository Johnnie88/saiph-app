<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Plugins_AFJCL
 *
 * Astoundify Company Listings
 */
class WP_Job_Manager_Field_Editor_Plugins_AFJCL {

	/**
	 * WP_Job_Manager_Field_Editor_Plugins_AFJCL constructor.
	 */
	public function __construct() {

		// We return true because function exists (initialized this class), so we know the plugin is active
		add_filter( 'field_editor_wpcm_active', '__return_true' );
		add_filter( 'field_editor_company_post_type', array( $this, 'post_type' ) );
		add_filter( 'field_editor_company_submit_form_class_name', array( $this, 'submit_form_class_name' ) );
		add_filter( 'field_editor_get_company_id_from_job_company_meta_key', array( $this, 'get_company_id_meta_key' ) );

//		add_filter( 'field_editor_output_options_wpcm', array( $this, 'output_options' ) );
	}

	/**
	 * Company Post Type
	 *
	 * Astoundify uses "company_listings" instead of "company" for the post type
	 *
	 * @param $post_type
	 *
	 * @return string
	 * @since 1.11.3
	 *
	 */
	public function post_type( $post_type ) {
		return 'company_listings';
	}

	/**
	 * Return Company Output Locations
	 *
	 * @param $options
	 *
	 * @return mixed
	 * @since 1.11.3
	 *
	 */
	public function output_options( $options ) {

		$options = array(
			'single_company_astoundify_page'               => '---' . __( "Astoundify Companies", 'wp-job-manager-field-editor' ),
			'WP_Job_Manager_before_single_company_summary' => __( 'Single Company Before Summary', 'wp-job-manager-field-editor' ),
			'WP_Job_Manager_single_company_summary'        => __( 'Single Company Summary', 'wp-job-manager-field-editor' ),
			'WP_Job_Manager_after_single_company_summary'  => __( 'Single Company After Summary', 'wp-job-manager-field-editor' ),
			'WP_Job_Manager_after_single_company'          => __( 'Single Company After', 'wp-job-manager-field-editor' ),
		);

		return $options;
	}

	/**
	 * Return Company ID Meta Key
	 *
	 * @param $meta_key
	 *
	 * @return string
	 * @since 1.11.3
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
	 * @since 1.11.3
	 *
	 */
	public function submit_form_class_name( $name ) {
		return 'WP_Job_Manager_Field_Editor_Plugins_AFJCL_Submit';
	}
}

if ( class_exists( 'WP_Job_Manager_Company_Listings' ) ) {
	new WP_Job_Manager_Field_Editor_Plugins_AFJCL();
}
