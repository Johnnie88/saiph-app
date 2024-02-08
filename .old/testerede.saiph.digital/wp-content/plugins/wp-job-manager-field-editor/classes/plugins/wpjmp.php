<?php

if( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Field_Editor_Plugins_WPJMP {

	/**
	 * WP_Job_Manager_Field_Editor_Plugins_WPJMP constructor.
	 */
	public function __construct() {

		add_filter( 'job_manager_field_editor_job_init_fields', array( $this, 'check_empty_options' ) );
		add_filter( 'job_manager_field_editor_js_conf_meta_keys', array( $this, 'meta_key_js' ) );
		add_action( 'wpjmp_javascript_variables', array( $this, 'max_selections' ), 99999 );
	}

	/**
	 * Make sure Product respects max selected options field config
	 *
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @since 1.8.12
	 *
	 */
	function max_selections( $args ){

		$max = get_custom_field_config( 'products', 'max_selected' );

		// Has to also check for array, as get_custom_field_config returns field array if config_key is not found
		if( ! empty( $max ) && ! is_array( $max ) ){
			$args['chosen_max_selected_options'] = $max;
		}

		return $args;
	}

	/**
	 * Remove products key, or value from aray
	 *
	 * We do not want the products value to automatically save so we need to remove it
	 * from the array, even if it's an empty value (to prevent empty value being saved)
	 *
	 *
	 * @since 1.4.1
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function check_empty_options( $fields ){
		// Return standard fields if company key, or products key in company is not set
		if( ! isset( $fields['company' ], $fields['company']['products'] ) ) return $fields;
		// Unset field if options is not set
		if( ! isset($fields['company']['products']['options'] ) ) unset($fields['company']['products']);
		// Unset field if options array is empty
		if( empty($fields['company']['products']['options'] ) ) unset( $fields['company']['products'] );

		return $fields;
	}


	/**
	 * Set Products Meta Key Configuration
	 *
	 *
	 * @since 1.4.1
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	function meta_key_js( $data ){

		$data['products'] = array(
			"type_disabled_by" => array('multiselect'),
			"hidden_tabs"      => array('options', 'output'),
			"disable_types"    => array(
				'text',
				'textarea',
				'wp-editor',
				'select',
				'file',
				'password',
				'radio',
				'checkbox',
				'date',
				'phone',
				'term-checklist',
				'term-multiselect',
				'term-select',
				'header',
				'html',
				'actionhook',
				'number',
				'range',
				'fpdate',
				'fptime',
				'autocomplete',
				'url',
				'email',
				'tel'
			),
			"not_required"     => array('options')
		);

		return $data;
	}
}

if( class_exists( 'WP_Job_Manager_Products' ) ) new WP_Job_Manager_Field_Editor_Plugins_WPJMP();
