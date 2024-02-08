<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Themes_WorkScout_Output
 *
 * @since @@since
 *
 */
class WP_Job_Manager_Field_Editor_Themes_WorkScout_Output {

	public $overview_actions = array(
		'single_job_listing_meta_start',
		'single_job_listing_meta_end',
		'single_job_listing_meta_after',
		'single_resume_meta_start',
		'single_resume_meta_end',
	);

	/**
	 * WP_Job_Manager_Field_Editor_Themes_WorkScout_Output constructor.
	 */
	function __construct() {
		add_filter( 'field_editor_auto_output_config', array( $this, 'auto_output_config' ), 10, 5 );
		add_filter( 'field_editor_output_as_field_open_full_wrapper', array( $this, 'open_full_wrapper' ), 10, 5 );
		add_filter( 'field_editor_output_as_field_close_full_wrapper', array( $this, 'close_full_wrapper' ), 10, 5 );
	}

	/**
	 * Customize Open Full Wrapper
	 *
	 * This method filters on the open full wrapper, to inject the required HTML for showing in WorkScout theme
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
	 * @since @@since
	 *
	 */
	public function open_full_wrapper( $open_wrapper, $args, $field_value, $field_slug, $job_id ) {

		if ( isset( $args['workscout_overview'] ) && ! empty( $args['workscout_overview'] ) ) {
			$icon_classes = isset( $args['workscout_icon_classes'] ) ? $args['workscout_icon_classes'] : '';
			// Closing </div> for content class is added in close full wrapper
			$open_wrapper .= ' <i class="' . $icon_classes . '"></i><div>';
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
	 * @since @@since
	 *
	 */
	public function close_full_wrapper( $close_wrapper, $args, $field_value, $field_slug, $job_id ) {

		if ( isset( $args['workscout_overview'] ) && ! empty( $args['workscout_overview'] ) ) {
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
	 * @since @@since
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

			if ( $post_type === 'resume' ) {
				$class_slug = 'resume';
			} elseif ( $post_type === $company_post_type ) {
				$class_slug = 'company';
			}

			$config['fw_classes'] .= " single-{$class_slug}-overview-detail";
			// Force enable value, and full wrapper
			$config['output_enable_vw']  = true;
			$config['output_enable_fw']  = true;
			$config['output_label_wrap'] = 'strong';
			$config['output_value_wrap'] = 'span';

			/**
			 * Default is TRUE for output CSV (regardless of field setting), but users can define false through a filter, and by
			 * checking if it's set to TRUE already, we can skip setting the value, in situation where they want it disabled for other
			 * fields but enabled for a specific field (from configuration).
			 *
			 * The better solution would be to set it true by default but since this is already the native way of handling it, we don't
			 * want to remove this (in favor of field setting) as that could cause issues for users already expecting CSV output.
			 */
			if( isset( $config['output_csv'] ) && empty( $config['output_csv'] ) ){
				$config['output_csv'] = apply_filters( 'field_editor_workscout_overview_output_csv', true, $config, $actual_action, $post_id, $post_type );
			}

			$config['label_show_colon']  = true;

			// Set workscout specific config so we can check it in later filers used
			$config['workscout_overview']     = true;
			$config['workscout_icon_classes'] = $config['output_classes'];
			// We have to set to empty string to prevent adding the class to value wrapper
			$config['output_classes'] = '';

			add_filter( 'field_editor_output_as_li_value_wrap_enabled', '__return_true' );
			add_action( 'field_editor_the_custom_field_output_as_end', array( $this, 'remove_li_value_wrap_enabled_filter' ) );

		} elseif( isset( $config['output'] ) && in_array( $config['output'], $this->overview_actions ) ){
			$config['output_classes'] = apply_filters( 'field_editor_auto_output_workscout_overview_output_classes', '', $config, $actual_action, $post_id, $is_wpcm_field, $action );
		}

		return $config;
	}

	/**
	 * Remove <li> Value Wrap Enabled Filter
	 *
	 * We add the filter in $this->auto_output_config() to enable value wrapper for <li> elements (disabled by default),
	 * this method is called after the field has been output, to remove that filer so it doesn't cause issues with any
	 * fields output after.
	 *
	 * @param $meta_key
	 *
	 * @since 1.10.2
	 *
	 */
	public function remove_li_value_wrap_enabled_filter( $meta_key ) {
		remove_filter( 'field_editor_output_as_li_value_wrap_enabled', '__return_true' );
	}
}