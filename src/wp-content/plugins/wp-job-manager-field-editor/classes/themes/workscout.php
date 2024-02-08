<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Field_Editor_Themes_WorkScout {

	function __construct() {
		add_filter( 'field_editor_output_options', array( $this, 'auto_output' ), 10, 2 );
		new WP_Job_Manager_Field_Editor_Themes_WorkScout_Output();
	}

	/**
	 * WorkScout Theme custom action output areas
	 *
	 *
	 * @since @@since
	 *
	 * @param $current_options
	 * @param $type
	 *
	 * @return array|bool
	 */
	function auto_output( $current_options, $type ) {

		if( $type === 'company' ) $type = "job";
		if( $type === 'resume_fields' ) $type = "resume";

		$field_groups = ! empty( $type ) ? array( $type ) : array( 'job', 'resume', 'company_fields' );

		$theme_version = WP_Job_Manager_Field_Editor_Integration::check_theme( 'workscout', '1.0.9', 'version' );
		if( ! $theme_version ) return FALSE;

		$workscout_options_job = array(
				'1.0.9' => array(
						'job_listing_workscout_list_page' => array(
							'label' => '---' . __( 'WorkScout Listing List', 'wp-job-manager-field-editor' ),
							'job_content_start' => __( 'Job Content Start', 'wp-job-manager-field-editor' ),
							'workscout_job_listing_meta_start' => __( 'WorkScout Meta Start', 'wp-job-manager-field-editor' ),
							'workscout_job_listing_meta_end' => __( 'WorkScout Meta End', 'wp-job-manager-field-editor' ),
						),
						'single_job_listing_workscout'                       => array(
							'label'									=> '---' . __( 'WorkScout Single Listing', 'wp-job-manager-field-editor' ),
							'workscout_bookmark_hook'       => __( 'Bookmark Hook', 'wp-job-manager-field-editor' ),
						),
				),
		);

		$workscout_options_resume = array(
				'1.0.9' => array(
						'single_resume_listing_workscout'                       => array(
							'label'							=> '---' . __( 'WorkScout Single Listing', 'wp-job-manager-field-editor' ),
							'workscout_bookmark_hook'       => __( 'Bookmark Hook', 'wp-job-manager-field-editor' ),
						),
				),
		);

		$build_options = array();

		foreach( $field_groups as $group ){

			if( ! isset( ${"workscout_options_$group"} ) ) continue;

			foreach( ${"workscout_options_$group"} as $version => $options ) {

				if( version_compare( $theme_version, $version, 'ge' ) ) {
					$build_options = array_merge_recursive( $build_options, $options );
				}

			}
		}

		// Loop through all built options (separated by groups) and rebuild non multi-dimensional array
		foreach( $build_options as $option_group => $option_options ){
			$current_options[ $option_group ] = $option_options[ 'label' ];
			unset( $option_options ['label'] );
			$current_options += $option_options;
		}

		return $current_options;

	}
}