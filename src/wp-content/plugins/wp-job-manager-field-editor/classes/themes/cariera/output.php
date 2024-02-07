<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Themes_Cariera_Output
 *
 * @since 1.10.0
 *
 */
class WP_Job_Manager_Field_Editor_Themes_Cariera_Output {

	public $overview_actions = array( 'single_job_listing_meta_start', 'single_job_listing_meta_end', 'single_job_listing_meta_after', 'single_resume_meta_start', 'single_resume_meta_end', 'cariera_single_company_meta_start', 'cariera_single_company_meta_end' );

	/**
	 * WP_Job_Manager_Field_Editor_Themes_Cariera_Output constructor.
	 */
	function __construct() {
		add_filter( 'field_editor_auto_output_li_actions', array( $this, 'auto_output_li_actions' ) );
		add_filter( 'field_editor_auto_output_config', array( $this, 'auto_output_config' ), 10, 5 );
		add_filter( 'field_editor_output_as_field_open_full_wrapper', array( $this, 'open_full_wrapper' ), 10, 5 );
		add_filter( 'field_editor_output_as_field_close_full_wrapper', array( $this, 'close_full_wrapper' ), 10, 5 );
	}

	/**
	 * Customize Open Full Wrapper
	 *
	 * This method filters on the open full wrapper, to inject the required HTML for showing in Cariera theme
	 * sidebar correctly, along with icons.  The icon classes should be added in the "classes" configuration for
	 * auto output.
	 *
	 * @param $open_wrapper
	 * @param $args
	 * @param $field_value
	 * @param $field_slug
	 * @param $job_id
	 *
	 * @return string
	 * @since 1.10.0
	 *
	 */
	public function open_full_wrapper( $open_wrapper, $args, $field_value, $field_slug, $job_id ) {

		if ( isset( $args['cariera_overview'] ) && ! empty( $args['cariera_overview'] ) ) {
			$icon_classes = isset( $args['cariera_icon_classes'] ) ? $args['cariera_icon_classes'] : '';
			// Closing </div> for content class is added in close full wrapper
			$open_wrapper .= ' <div class="icon"><i class="' . $icon_classes . '"></i></div><div class="content">';
		}

		return $open_wrapper;
	}

	/**
	 * Customize Close Full Wrapper
	 *
	 * This method just closes the wrapping div element, see open_full_wrapper() above for more details
	 *
	 * @param $close_wrapper
	 * @param $args
	 * @param $field_value
	 * @param $field_slug
	 * @param $job_id
	 *
	 * @return string
	 * @since 1.10.0
	 *
	 */
	public function close_full_wrapper( $close_wrapper, $args, $field_value, $field_slug, $job_id ) {

		if ( isset( $args['cariera_overview'] ) && ! empty( $args['cariera_overview'] ) ) {
			$close_wrapper = '</div>' . $close_wrapper;
		}

		return $close_wrapper;
	}

	/**
	 * Customize Auto Output Configuration
	 *
	 * This method is initially used for customizing the auto output feature, to build the correct HTML for the
	 * sidebar overview output.  This will only customize these fields when it is one of the actions listed in
	 * $this->overview_actions
	 *
	 * @param $config
	 * @param $actual_action
	 * @param $post_id
	 * @param $is_wpcm_field
	 * @param $action
	 *
	 * @return mixed
	 * @since 1.10.0
	 */
	public function auto_output_config( $config, $actual_action, $post_id, $is_wpcm_field, $action ) {

		if ( in_array( $actual_action, $this->overview_actions ) ) {
			if ( ! isset( $config['fw_classes'] ) ) {
				$config['fw_classes'] = '';
			}

			// Default to job
			$class_slug = 'job';

			$post_type = get_post_type( $post_id );

			$company_post_type = WP_Job_Manager_Field_Editor_Integration_Company::get_post_type();

			if( $post_type === 'resume' ){
				$class_slug = 'resume';
			} elseif( $post_type === $company_post_type ){
				$class_slug = 'company';
			}

			$config['fw_classes'] .= " single-{$class_slug}-overview-detail";
			// Force enable value, and full wrapper
			$config['output_enable_vw']  = true;
			$config['output_enable_fw']  = true;
			$config['output_label_wrap'] = 'h6';
			$config['output_value_wrap'] = 'span';
			/**
			 * Default is TRUE for output CSV (regardless of field setting), but users can define false through a filter, and by
			 * checking if it's set to TRUE already, we can skip setting the value, in situation where they want it disabled for other
			 * fields but enabled for a specific field (from configuration).
			 *
			 * The better solution would be to set it true by default but since this is already the native way of handling it, we don't
			 * want to remove this (in favor of field setting) as that could cause issues for users already expecting CSV output.
			 */
			if ( isset( $config['output_csv'] ) && empty( $config['output_csv'] ) ) {
				$config['output_csv'] = apply_filters( 'field_editor_cariera_overview_output_csv', true, $config, $actual_action, $post_id, $post_type );
			}

			$config['label_show_colon']  = false;

			// Set cariera specific config so we can check it in later filers used
			$config['cariera_overview']     = true;
			$config['cariera_icon_classes'] = $config['output_classes'];
			// We have to set to empty string to prevent adding the class to value wrapper
			$config['output_classes'] = '';
		} elseif ( isset( $config['output'] ) && in_array( $config['output'], $this->overview_actions ) ) {
			$config['output_classes'] = apply_filters( 'field_editor_auto_output_cariera_overview_output_classes', '', $config, $actual_action, $post_id, $is_wpcm_field, $action );
		}

		return $config;
	}

	/**
	 * Remove LI Specific Actions
	 *
	 * Cariera theme does not use the <ul> wrapper around specific actions like the normal WP Job Manager
	 * template does, so we need to make sure and remove those from forcing an <li> wrapper
	 *
	 * @param $li_actions
	 *
	 * @return mixed
	 * @since 1.10.0
	 *
	 */
	public function auto_output_li_actions( $li_actions ) {

		$remove = array( 'single_job_listing_meta_end', 'single_job_listing_meta_start', 'single_resume_meta_start', 'single_resume_meta_end' );

		foreach ( $li_actions as $action_index => $li_action ) {
			if ( in_array( $li_action, $remove ) ) {
				unset( $li_actions[ $action_index ] );
			}
		}

		return $li_actions;
	}

}