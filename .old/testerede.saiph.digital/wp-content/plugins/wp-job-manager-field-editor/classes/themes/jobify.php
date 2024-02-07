<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Themes_Jobify
 *
 * @since 1.7.0
 *
 */
class WP_Job_Manager_Field_Editor_Themes_Jobify {

	/**
	 * WP_Job_Manager_Field_Editor_Themes_Jobify constructor.
	 */
	function __construct() {

		add_filter( 'field_editor_output_options', array( $this, 'auto_output' ), 10, 2 );
		add_filter( 'job_manager_field_editor_set_featured_image', array( $this, 'featured_image' ), 10 );
		add_filter( 'field_editor_auto_output_li_actions', array( $this, 'add_li_actions' ) );
		add_action( 'field_editor_before_output_as_gallery', array( $this, 'fix_lightbox_close' ) );
		add_filter( 'job_manager_term_multiselect_field_args', array( $this, 'multiselect_add_avoid_class' ), 9999 );
		add_filter( 'submit_job_form_start', array( $this, 'custom_css' ) );
		add_filter( 'submit_resume_form_start', array( $this, 'custom_css' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 999999 );
	}

	/**
	 * Dequeue WPJM Frontend and Chosen CSS
	 *
	 * Jobify has this same exact code located at
	 * @see jobify/inc/integrations/wp-job-manager/class-wp-job-manager-template.php
	 *
	 * But the problem is that code is ran at the standard priority of 10, whereas WPJM <= 1.31.3 enqueues it at the same
	 * priority, and as such, I have to add this code to run at later priority to make sure these styles are actually dequeued
	 *
	 * @since 1.8.9
	 *
	 */
	public function wp_enqueue_scripts() {
		wp_dequeue_style( 'wp-job-manager-frontend' );
		wp_dequeue_style( 'chosen' );
	}

	/**
	 * Add Class to Avoid Wrapping Multiselect in <span>
	 *
	 * Listify and Jobify both use javascript to wrap any select elements in a <span> wrapper, causing display issues
	 * when we remove an "avoid" class, so this filter adds a class from the javascript that will avoid adding the span wrapper.
	 *
	 * @see   wp-content/themes/jobify/js/app/app.js
	 *
	 * @since 1.8.5
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function multiselect_add_avoid_class( $args ) {

		$classes = isset( $args['class'] ) ? $args['class'] : '';

		// Empty value will cause core to automatically add job-manager-category-dropdown, which is already in the list of classes to "avoid"
		if ( empty( $classes ) || strpos( $classes, 'job-manager-category-dropdown' ) !== false ) {
			return $args;
		}

		// It does have dynamic tax class, let's add a Jobify one to skip wrapping in <span> element
		if ( strpos( $classes, 'jmfe-dynamic-tax' ) !== false && strpos( $classes, 'feedFormField' ) === false ) {
			$args['class'] = $classes . ' feedFormField';
		}

		return $args;
	}

	/**
	 * Fix Magnific Popup default CSS
	 *
	 * Since Jobify adds custom colors for the close button, we have to override the default width 100%
	 * to make the close button look correct.
	 *
	 * @since 1.7.2
	 *
	 */
	function fix_lightbox_close(){
		echo '<style>.mfp-image-holder button.mfp-close { width: 30px; padding-right: 4px; }</style>';
	}

	/**
	 * Add Hooks that should be wrapped in <li> tags
	 *
	 *
	 * @since 1.7.0
	 *
	 * @param $actions
	 *
	 * @return array
	 */
	function add_li_actions( $actions ){

		$actions[] = 'job_listing_company_social_before';
		$actions[] = 'job_listing_company_social_after';

		return $actions;
	}

	/**
	 * Jobify Theme custom action output areas
	 *
	 * Requires Jobify 2.0.1.2 or newer
	 *
	 * @since @@since
	 *
	 * @param $current_options
	 * @param $type
	 *
	 * @return array|bool
	 */
	function auto_output( $current_options, $type ) {

		if ( $type === 'company' ) {
			$type = "job";
		}
		if ( $type === 'resume_fields' ) {
			$type = "resume";
		}

		$field_groups = ! empty( $type ) ? array( $type ) : array( 'job', 'resume' );

		$theme_version = WP_Job_Manager_Field_Editor_Integration::check_theme( 'jobify', '2.0.1.2', 'version' );
		if ( ! $theme_version ) {
			return $current_options;
		}

		$jobify_options_job = array(
			'2.0.1.2' => array(
				'single_job_listing_info_jobify'      => array(
					'label'                          => '---' . __( 'Jobify Single Listing Page', 'wp-job-manager-field-editor' ),
					'single_job_listing_info_before' => __( 'Single Job Listing Before', 'wp-job-manager-field-editor' ),
					'single_job_listing_info_after'  => __( 'Single Job Listing After', 'wp-job-manager-field-editor' ),
					'single_job_listing_info_start'  => __( 'Single Job Listing Start', 'wp-job-manager-field-editor' ),
					'single_job_listing_info_end'    => __( 'Single Job Listing End', 'wp-job-manager-field-editor' ),
				),
			),
			'2.0.2.2' => array(
				'single_job_listing_jobify_widgets'    => array(
					'label'                             => '---' . __( 'Jobify Theme Widgets', 'wp-job-manager-field-editor' ),
					'jobify_widget_job_apply_after'     => __( 'After Apply', 'wp-job-manager-field-editor' ),
					'job_listing_company_social_before' => __( 'Before Company Social Icons', 'wp-job-manager-field-editor' ),
					'job_listing_company_social_after'  => __( 'After Company Social Icons', 'wp-job-manager-field-editor' ),
				),
			)
		);

		$jobify_options_resume = array(
			'2.0.1.2' => array(
				'single_resume_info_jobify' => array(
					'label'                     => '---' . __( 'Jobify Single Listing Page', 'wp-job-manager-field-editor' ),
					'single_resume_info_before' => __( 'Single Resume Listing Before', 'wp-job-manager-field-editor' ),
					'single_resume_info_after'  => __( 'Single Resume Listing After', 'wp-job-manager-field-editor' ),
					'single_resume_info_start'  => __( 'Single Resume Listing Start', 'wp-job-manager-field-editor' ),
					'single_resume_info_end'    => __( 'Single Resume Listing End', 'wp-job-manager-field-editor' ),
				),
			)
		);

		$build_options = array();

		foreach ( $field_groups as $group ) {

			if ( ! isset( ${"jobify_options_$group"} ) ) {
				continue;
			}

			foreach ( ${"jobify_options_$group"} as $version => $options ) {

				if ( version_compare( $theme_version, $version, 'ge' ) ) {
					$build_options = array_merge_recursive( $build_options, $options );
				}

			}
		}

		// Loop through all built options (separated by groups) and rebuild non multi-dimensional array
		foreach ( $build_options as $option_group => $option_options ) {
			$current_options[ $option_group ] = $option_options['label'];
			unset( $option_options ['label'] );
			$current_options += $option_options;
		}

		return $current_options;

	}

	/**
	 * Output Custom CSS for Submit Page
	 *
	 *
	 * @since 1.8.5
	 *
	 */
	function custom_css() {

		echo '<style>.job-manager-form > fieldset > div.field > span.select { width: 100%; display: block; } .job-manager-form > fieldset > div.field > .chosen-container { width: 100% !important; } .dynamic-single-select-wrapper::before { display: none !important; } </style>';
	}

	/**
	 * Prevent set/save featured_image meta key as featured image
	 *
	 * Jobify uses the `featured_image` meta key on its own, outside of the actual featured image, and
	 * the featured image should be set by the core WPJM from the `company_logo` field.
	 *
	 * @since 1.5.0
	 *
	 * @param $allow
	 *
	 * @return bool
	 */
	function featured_image( $allow ){
		return false;
	}
}