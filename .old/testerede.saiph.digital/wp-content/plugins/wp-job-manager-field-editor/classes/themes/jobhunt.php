<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Themes_JobHunt
 *
 * @since 1.10.2
 *
 */
class WP_Job_Manager_Field_Editor_Themes_JobHunt {

	/**
	 * WP_Job_Manager_Field_Editor_Themes_JobHunt constructor.
	 */
	function __construct() {
		add_filter( 'field_editor_output_options', array( $this, 'auto_output' ), 10, 2 );
		add_filter( 'field_editor_auto_output_custom_job_actions', array( $this, 'custom_job_actions' ), 10, 2 );
		add_filter( 'field_editor_auto_output_custom_resume_actions', array( $this, 'custom_resume_actions' ), 10, 2 );
		add_filter( 'field_editor_auto_output_custom_company_actions', array( $this, 'custom_company_actions' ), 10, 2 );
//		add_filter( 'jobhunt_submit_job_form_company_fields', array( $this, 'company_fields' ), 9999999999 );
	}

	/**
	 * Filter Company Fields
	 *
	 * For some weird reason JobHunt doesn't use the native `company` key in array for passing fields through
	 * WP Job Manager, so we have to use this custom filter to add the company fields to the array.
	 *
	 * @param $company_fields
	 *
	 * @since 1.12.10
	 *
	 */
	public function company_fields( $company_fields ) {
		// TODO
	}

	/**
	 * Define Custom Company Actions
	 *
	 * @param $actions
	 * @param $auto_output
	 *
	 * @return array
	 * @since 1.10.2
	 *
	 */
	function custom_company_actions( $actions, $auto_output ) {

		$custom_actions = array(
			'jobhunt_before_company',
			'jobhunt_before_company_title',
			'jobhunt_company_title',
			'jobhunt_after_company_title',
			'jobhunt_after_company',
			'single_company_sidebar_bottom'
		);

		add_action( 'single_company_sidebar', array( $auto_output, 'single_company_sidebar_bottom' ), 9999999 );

		return array_merge( $actions, $custom_actions );
	}

	/**
	 * Define Custom Resume Actions
	 *
	 * @param $actions
	 * @param $auto_output
	 *
	 * @return array
	 * @since 1.10.2
	 *
	 */
	function custom_resume_actions( $actions, $auto_output ) {

		$custom_actions = array(
			'jobhunt_before_resume',
			'jobhunt_before_resume_title',
			'jobhunt_resume_title',
			'jobhunt_after_resume_title',
			'jobhunt_after_resume'
		);

		return array_merge( $actions, $custom_actions );
	}

	/**
	 * Define Custom Job Actions
	 *
	 * @param $actions
	 * @param $auto_output
	 *
	 * @return array
	 * @since 1.10.2
	 *
	 */
	function custom_job_actions( $actions, $auto_output ){

		$custom_actions = array(
			'job_content_start',
			'job_content_end',
			'jobhunt_before_job_listing',
			'jobhunt_before_job_listing_title',
			'jobhunt_job_listing_title',
			'jobhunt_after_job_listing_title',
			'jobhunt_after_job_listing',
			'single_job_listing_sidebar_bottom',
		);

		// Add custom action since called with higher priority
		add_action( 'single_job_listing_sidebar', array( $auto_output, 'single_job_listing_sidebar_bottom' ), 9999999 );

		return array_merge( $actions, $custom_actions );
	}

	/**
	 * Jobify Theme custom action output areas
	 *
	 * Requires Jobify 2.0.1.2 or newer
	 *
	 * @param $current_options
	 * @param $type
	 *
	 * @return array|bool
	 * @since @@since
	 *
	 */
	function auto_output( $current_options, $type ) {

		$theme_outputs = array();

		$job_theme_outputs = array(
			'single_job_listing_jobhunt_label'       => '---' . __( "JobHunt Single Listing Page", 'wp-job-manager-field-editor' ),
			'job_content_start'                      => __( 'Before/Top Entire Listing', 'wp-job-manager-field-editor' ),
			'single_job_listing_inner_before'        => __( 'Inner Before', 'wp-job-manager-field-editor' ),
			'single_job_listing_content_area_before' => __( 'Content Area Before', 'wp-job-manager-field-editor' ),
			'single_job_listing_sidebar'             => __( 'Sidebar Top', 'wp-job-manager-field-editor' ),
			'single_job_listing_sidebar_bottom'      => __( 'Sidebar Bottom', 'wp-job-manager-field-editor' ),
			'single_job_listing_content_area_after'  => __( 'Content Area After', 'wp-job-manager-field-editor' ),
			'single_job_listing_inner_after'         => __( 'Inner After', 'wp-job-manager-field-editor' ),
			'job_content_end'                        => __( 'After/Bottom Entire Listing', 'wp-job-manager-field-editor' ),

			'listing_list_jobhunt_label'       => '---' . __( "JobHunt Listings List Page", 'wp-job-manager-field-editor' ),
			'jobhunt_before_job_listing'       => __( 'Before Job Listing (before element)', 'wp-job-manager-field-editor' ),
			'jobhunt_before_job_listing_title' => __( 'Before Job Title', 'wp-job-manager-field-editor' ),
			'jobhunt_job_listing_title'        => __( 'Job Title', 'wp-job-manager-field-editor' ),
			'jobhunt_after_job_listing_title'  => __( 'After Listing Title', 'wp-job-manager-field-editor' ),
			'jobhunt_after_job_listing'        => __( 'After Job Listing (after element)', 'wp-job-manager-field-editor' ),
		);

		$resume_theme_outputs = array(
			'single_resume_jobhunt_label'         => '---' . __( "JobHunt Single Resume Page", 'wp-job-manager-field-editor' ),
			'single_resume_before'                => __( 'Before Entire Resume', 'wp-job-manager-field-editor' ),
			'single_resume_head_before'           => __( 'Before Resume Header', 'wp-job-manager-field-editor' ),
			'single_resume_head'                  => __( 'Resume Header', 'wp-job-manager-field-editor' ),
			'single_resume_head_after'            => __( 'After Resume Header', 'wp-job-manager-field-editor' ),
			'single_resume_content_navbar_before' => __( 'Before Resume Navbar', 'wp-job-manager-field-editor' ),
			'single_resume_content_navbar'        => __( 'Resume Navbar', 'wp-job-manager-field-editor' ),
			'single_resume_content_navbar_after'  => __( 'After Resume Navbar', 'wp-job-manager-field-editor' ),
			'single_resume_content_before'        => __( 'Before Resume Content', 'wp-job-manager-field-editor' ),
			'single_resume_content'               => __( 'Resume Content', 'wp-job-manager-field-editor' ),
			'single_resume_content_after'         => __( 'After Resume Content', 'wp-job-manager-field-editor' ),
			'single_resume_sidebar_before'        => __( 'Before Resume Sidebar', 'wp-job-manager-field-editor' ),
			'single_resume_sidebar'               => __( 'Resume Sidebar', 'wp-job-manager-field-editor' ),
			'single_resume_sidebar_after'         => __( 'After Resume Sidebar', 'wp-job-manager-field-editor' ),
			'single_resume_after'                 => __( 'After Entire Resume', 'wp-job-manager-field-editor' ),

			'resume_list_jobhunt_label'       => '---' . __( "JobHunt Resumes List Page", 'wp-job-manager-field-editor' ),
			'jobhunt_before_resume'       => __( 'Before Resume (before element)', 'wp-job-manager-field-editor' ),
			'jobhunt_before_resume_title' => __( 'Before Resume Title', 'wp-job-manager-field-editor' ),
			'jobhunt_resume_title'        => __( 'Resume Title', 'wp-job-manager-field-editor' ),
			'jobhunt_after_resume_title'  => __( 'After Resume Title', 'wp-job-manager-field-editor' ),
			'jobhunt_after_resume'        => __( 'After Resume (after element)', 'wp-job-manager-field-editor' ),
		);

		$company_theme_outputs = array(
			'single_company_jobhunt_label'  => '---' . __( "JobHunt Single Company Page", 'wp-job-manager-field-editor' ),
			'single_company_content_before' => __( 'Before Company Content', 'wp-job-manager-field-editor' ),
			'single_company_head'           => __( 'Before Company Header', 'wp-job-manager-field-editor' ),
			'single_company_before'         => __( 'Before Company', 'wp-job-manager-field-editor' ),
			'single_company'                => __( 'Company', 'wp-job-manager-field-editor' ),
			'single_company_after'          => __( 'After Company', 'wp-job-manager-field-editor' ),
			'single_company_sidebar'        => __( 'Sidebar Top', 'wp-job-manager-field-editor' ),
			'single_company_sidebar_bottom' => __( 'Sidebar Bottom', 'wp-job-manager-field-editor' ),

			'company_list_jobhunt_label'   => '---' . __( "JobHunt Company List Page", 'wp-job-manager-field-editor' ),
			'jobhunt_before_company'       => __( 'Before Company (before element)', 'wp-job-manager-field-editor' ),
			'jobhunt_before_company_title' => __( 'Before Company Title', 'wp-job-manager-field-editor' ),
			'jobhunt_company_title'        => __( 'Company Title', 'wp-job-manager-field-editor' ),
			'jobhunt_after_company_title'  => __( 'After Company Title', 'wp-job-manager-field-editor' ),
			'jobhunt_after_company'        => __( 'After Company (after element)', 'wp-job-manager-field-editor' ),
		);

		// Add output locations for Job and Company fields (they use same template files)
		if ( ( $type == 'job' || $type == 'company' ) && ! isset( $current_options['single_job_listing_jobhunt_label'] ) ) {
			$theme_outputs = $job_theme_outputs;
		} elseif ( $type == 'resume_fields' && ! isset( $current_options['single_resume_jobhunt_label'] ) ) {
			$theme_outputs = $resume_theme_outputs;
		} elseif ( $type == 'company_fields' && ! isset( $current_options['single_company_jobhunt_label'] ) ) {
			$theme_outputs = $company_theme_outputs;
		} elseif( ! isset( $current_options['single_job_listing_jobhunt_label'] ) && ! $type ) {
			$theme_outputs = array_merge( $job_theme_outputs, $resume_theme_outputs, $company_theme_outputs );
		}

		// We MUST merge the new array with the old one
		return array_merge( $current_options, $theme_outputs );
	}

}