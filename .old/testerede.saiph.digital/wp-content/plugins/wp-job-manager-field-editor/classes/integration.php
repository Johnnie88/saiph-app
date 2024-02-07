<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Integration
 *
 * @since 1.1.9
 *
 */
class WP_Job_Manager_Field_Editor_Integration extends WP_Job_Manager_Field_Editor_Fields {

	private static $instance;
	private $job_fields;
	private $resume_fields;
	private $packages;
	protected static $force_validate_resumes = false;
	private $is_output_apply = false;

	function __construct() {

		$this->job_fields = new WP_Job_Manager_Field_Editor_Job_Fields();
		$this->packages = new WP_Job_Manager_Field_Editor_Package_WC();
		new WP_Job_Manager_Field_Editor_reCAPTCHA();
		if( $this->wprm_active() ) $this->resume_fields = new WP_Job_Manager_Field_Editor_Resume_Fields();

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_filter( 'job_manager_locate_template', array( $this, 'locate_template' ), 10, 3 );
		add_filter( 'job_manager_upload_file_pre_upload', array( $this, 'check_upload_sizes' ), 9999, 3 );
		add_filter( 'job_manager_upload_file_pre_upload', array( $this, 'check_upload_dimensions' ), 99999, 3 );

//		add_filter( 'submit_resume_form_validate_fields', array( $this, 'validate_post_fields' ), 20, 3 );
//		add_filter( 'submit_job_form_validate_fields', array( $this, 'validate_post_fields' ), 20, 3 );

		add_filter( 'pre_get_avatar_data', array( $this, 'check_custom_avatar' ), 10, 2 );
		add_filter( 'job_listing_searchable_meta_keys', array( $this, 'search_all_meta_keys' ) );
		// add_action( 'single_job_listing_start', array( $this, 'company_disabled_check' ), 25 );
		add_filter( 'job_manager_mime_types', array( $this, 'allowed_mime_types' ), 11, 2 );

		add_action( 'job_application_form_fields_start', array( $this, 'job_application_form_fields_start' ) );
		add_action( 'job_application_form_fields_end', array( $this, 'job_application_form_fields_end' ) );

		$this->init_theme();
		WP_Job_Manager_Field_Editor_Integration_Company::get_instance();

		add_filter( 'body_class', array( $this, 'listing_body_classes' ) );
	}

	/**
	 * Adds custom classes to body based on listing page (edit, new, etc)
	 *
	 * @param array $classes
	 *
	 * @return array
	 * @since 1.12.10
	 */
	function listing_body_classes( $classes ) {
		$classes = (array) $classes;

		if( isset( $_GET['action'] ) && $_GET['action'] === 'edit' ){

			if( isset( $_GET['job_id'] ) && ! empty( $_GET['job_id'] ) ){
				$classes[] = 'edit-job-page';
			} elseif ( isset( $_GET['resume_id'] ) && ! empty( $_GET['resume_id'] ) ){
				$classes[] = 'edit-resume-page';
			} elseif ( isset( $_GET['company_id'] ) && ! empty( $_GET['company_id'] ) ) {
				$classes[] = 'edit-company-page';
			}

		}

		return array_unique( $classes );
	}

	/**
	 * Check Form Action Params to Remove
	 *
	 * Check if the form action URL has params (specifically taxonomy) that need to be removed,
	 * to prevent fatal errors when submitting listings due to param values not matching.
	 *
	 * @see   https://github.com/tripflex/wp-job-manager-field-editor/issues/639
	 *
	 * @param string              $action
	 * @param WP_Job_Manager_Form $that
	 *
	 * @since 1.10.2
	 *
	 * @return mixed|string
	 */
	public function check_auto_get_populate_params( $action, $that ) {

		// If no action value, or is not URL with query arguments, return original value
		if ( empty( $action ) || strpos( $action, '?' ) === false ) {
			return $action;
		}

		/**
		 * Check to make sure the action we're processing is actually pulled from the request URL,
		 * as a custom action set on the class object does not need processing.
		 */
		$default_action = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( $action !== $default_action || ! apply_filters( 'field_editor_form_action_remove_auto_populate_params', true, $action, $that, $this ) ) {
			return $action;
		}

		/**
		 * Should be null if no query parameters exist on the URL
		 */
		$parsed_url = wp_parse_url( $action, PHP_URL_QUERY );
		if ( ! $parsed_url ) {
			return $action;
		}

		parse_str( $parsed_url, $parsed_params );
		// If error parsing the query parameters, or no params parsed, return original action just in case
		if ( ! isset( $parsed_params ) || empty( $parsed_params ) ) {
			return $action;
		}

		$fields_to_check = $this->auto_populate_check_form_get_fields( $that );

		if ( empty( $fields_to_check ) ) {
			return $action;
		}

		/**
		 * Remove ALL query arguments from URL
		 *
		 * We use this method to make it faster than looping through every meta key, removing it from the URL, by just cleaning off
		 * all query arguments, and then checking for any query arguments that are NOT meta keys, and adding those back.
		 */
		$no_query_arg_url = explode( '?', esc_url_raw( add_query_arg( array(), $action ) ) );

		if ( ! empty( $no_query_arg_url ) && isset( $no_query_arg_url[0] ) && ! empty( $no_query_arg_url[0] ) ) {
			$action = $no_query_arg_url[0];

			// Check for any query arguments that are NOT meta keys
			$non_meta_query_params = array_diff_key( $parsed_params, $fields_to_check );

			// If there are any, add them back to the URL
			if ( ! empty( $non_meta_query_params ) ) {
				$action = add_query_arg( $non_meta_query_params, $action );
			}
		}

		return $action;
	}

	/**
	 * Set Local Var Before Application Form is Output
	 *
	 *
	 * @since 1.10.0
	 *
	 */
	public function job_application_form_fields_end() {
		$this->is_output_apply = false;
	}

	/**
	 * Set Local Var when Application Form Ends
	 *
	 *
	 * @since 1.10.0
	 *
	 */
	public function job_application_form_fields_start() {
		$this->is_output_apply = true;
	}

	/**
	 * Validate Max Selections on Multiselect Fields
	 *
	 * May not need this to resolve frontend issues, as this could cause problems with the child dropdown feature.
	 *
	 * @since 1.8.9
	 *
	 * @param $valid
	 * @param $fields
	 * @param $values
	 *
	 * @return \WP_Error
	 */
	function validate_post_fields( $valid, $fields, $values ){

		if( ! apply_filters( 'field_editor_validate_max_selected', true, $fields, $values ) ){
			return $valid;
		}

		foreach ( $fields as $group_key => $group_fields ) {

			foreach ( $group_fields as $key => $field ) {

				if( ! array_key_exists( 'max_selected', $field ) || empty( $field['max_selected'] ) || ! in_array( $field['type'], array( 'term-checklist', 'multiselect', 'term-multiselect' ) ) ){
					continue;
				}

				if( array_key_exists( $key, $values[ $group_key ] ) && is_array( $values[ $group_key ][ $key ] ) && count( $values[ $group_key ][ $key ] ) > $field['max_selected'] ){
					return new WP_Error( 'validation-error', sprintf( __( '%1$s max allowed selections is %2$s', 'wp-job-manager-field-editor' ), $field['label'], $field['max_selected'] ) );
				}
			}
		}

		return $valid;
	}

	/**
	 * Return Custom MIME Types Set in Field Config
	 *
	 * Core calls the same function for uploading a file, both for ajax and non-ajax uploads.  When using ajax upload, the function is not called with field
	 * configuration, and as such, it defaults to default mime types.  To fix this we have to filter on the allowed mime types, and return custom ones if
	 * they are defined/configured in field configuration.
	 *
	 *
	 * @since 1.7.4
	 *
	 * @param $allowed_mime_types
	 * @param $meta_key
	 *
	 * @return mixed
	 */
	function allowed_mime_types( $allowed_mime_types, $meta_key ){

		$field_config = get_custom_field_config( $meta_key );

		// Only return custom mime types if configured, and field type is file
		if ( ! empty( $field_config ) && array_key_exists( 'options', $field_config ) && ! empty( $field_config['options'] ) && $field_config['type'] === 'file' ) {
			return $field_config['options'];
		}

		return $allowed_mime_types;
	}

	/**
	 * Searchable Meta Keys
	 *
	 *
	 * @since 1.7.2
	 *
	 * @param $meta_keys
	 *
	 * @return bool
	 */
	function search_all_meta_keys( $meta_keys ){

		$search_all = get_option( 'jmfe_enable_search_all_meta', true );

		if( ! empty( $search_all ) ){
			// Return false to force WP Job Manager to search all meta
			return false;
		}

		return $meta_keys;
	}

	/**
	 * Check Max Upload Filesize
	 *
	 *
	 * @since 1.7.0
	 *
	 * @param array $file               Array of $_FILE data to upload.
	 * @param array $args               Optional file arguments
	 * @param array $allowed_mime_types Array of allowed mime types from field config or defaults
	 *
	 * @return mixed
	 */
	function check_upload_sizes( $file, $args, $allowed_mime_types ) {

		if ( ! is_array( $file ) || ! array_key_exists( 'size', $file ) || empty( $file['size'] ) ) {
			return $file;
		}

		$field_config = get_custom_field_config( $args['file_key'] );

		if ( empty( $field_config ) || ! array_key_exists( 'max_upload_size', $field_config ) || empty( $field_config['max_upload_size'] ) ) {
			return $file;
		}

		// Convert user input format to bytes
		$max_upload_size_bytes = job_manager_field_editor_size_to_bytes( $field_config['max_upload_size'] );

		if ( $file['size'] > $max_upload_size_bytes ) {
			return new WP_Error( 'validation-error', sprintf( __( 'The max allowed file size is %1$s, %2$s is %3$s', 'wp-job-manager-field-editor' ), size_format( $max_upload_size_bytes ) . " ({$max_upload_size_bytes} bytes)", esc_attr( $file['name'] ), size_format( $file['size'] ) . " ({$file['size']} bytes)" ) );
		}

		return $file;
	}
	/**
	 * Check Max Upload Dimensions
	 *
	 *
	 * @since 1.7.10
	 *
	 * @param array $file               Array of $_FILE data to upload.
	 * @param array $args               Optional file arguments
	 * @param array $allowed_mime_types Array of allowed mime types from field config or defaults
	 *
	 * @return mixed
	 */
	function check_upload_dimensions( $file, $args, $allowed_mime_types ) {

		if ( ! is_array( $file ) || ! array_key_exists( 'tmp_name', $file ) || empty( $file['tmp_name'] ) ) {
			return $file;
		}

		// Return $file when mime type fails, to allow core WP Job Manager to return mime type error
		if( array_key_exists( 'type', $file ) && ! in_array( $file['type'], $allowed_mime_types ) ){
			return $file;
		}

		$field_config = get_custom_field_config( $args['file_key'] );

		// Will return 0 or false if file is not an image, or unable to parse file uploaded
		$imagesize = getimagesize( $file['tmp_name'] );

		if ( empty( $field_config ) || empty( $imagesize ) || ! isset( $imagesize[0], $imagesize[1] ) ) {
			return $file;
		}

		$width = $imagesize[0];
		$height = $imagesize[1];

		$max_width = array_key_exists( 'max_upload_width', $field_config ) && ! empty( $field_config['max_upload_width'] ) ? (int) $field_config['max_upload_width'] : false;
		$max_height = array_key_exists( 'max_upload_height', $field_config ) && ! empty( $field_config['max_upload_height'] ) ? (int) $field_config['max_upload_height'] : false;
		$max_demensions = $max_width && $max_height ? sprintf( __( 'Max image dimensions are %1$s x %2$s (in pixels)', 'wp-job-manager-field-editor' ), $max_width, $max_height ): '';

		if( $max_width && ! empty( $width ) && ( $width > $max_width ) ){
			return new WP_Error( 'validation-error', sprintf( __( 'The max allowed image width for %1$s is %2$s pixels, the one you uploaded is %3$s pixels. %4$s', 'wp-job-manager-field-editor' ), esc_attr( $file['name'] ), $max_width, esc_attr( $width ), $max_demensions ) );
		}

		if( $max_height && ! empty( $height ) && ( $height > $max_height ) ){
			return new WP_Error( 'validation-error', sprintf( __( 'The max allowed image height for %1$s is %2$s pixels, the one you uploaded is %3$s pixels. %4$s', 'wp-job-manager-field-editor' ), esc_attr( $file['name'] ), (int) $field_config['max_upload_height'], esc_attr( $height ), $max_demensions ) );
		}

		return $file;
	}

	/**
	 * Set value in field array for taxonomy field types
	 *
	 * When the core WP Job Manager loads values from a listing, it tries to pull value from post meta,
	 * which does not work correctly all the time for taxonomy field types.  This method will set the value
	 * to an array of the taxonomy IDs.
	 *
	 * @since 1.6.4
	 *
	 * @param $fields
	 * @param $listing
	 *
	 * @return array
	 */
	function set_taxonomy_values( $fields, $listing ){

		if ( ! is_array( $fields ) || ! is_object( $listing ) ) return $fields;

		if ( isset( $fields[ 'job' ] ) || isset( $fields[ 'resume_fields' ] ) ) {

			foreach( $fields as $field_group => $group_fields ) {
				$fields[ $field_group ] = $this->set_taxonomy_values( $group_fields, $listing );
			}

			return $fields;
		}

		foreach( $fields as $f_key => $f_conf ) {
			// Make sure current field is a taxonomy field type
			if ( array_key_exists( 'taxonomy', $f_conf ) && ! empty( $f_conf['taxonomy'] ) && in_array( $f_conf[ 'type' ], array('term-checklist', 'term-select', 'term-multiselect') ) ){

				// Skip processing core fields that should already be set by core plugin
				if( in_array( $f_key, array( 'job_type', 'job_category', 'resume_skills', 'resume_category' ) ) ) continue;

				$fields[ $f_key ][ 'value' ] = wp_get_object_terms( $listing->ID, $f_conf[ 'taxonomy' ], array('fields' => 'ids') );
			}

		}

		return $fields;

	}

	/**
	 * Check file uploads for max limit
	 *
	 * This method checks file uploads for max uploads, etc, and returns a WP_Error
	 * if the number of files exceeds the allowed limit.  This method is for backend
	 * prevention of bypassing frontend JS that prevents number of files uploaded.
	 *
	 *
	 * @since 1.5.0
	 *
	 * @param $fields
	 * @param $values
	 *
	 * @return bool|\WP_Error   Returns TRUE when max upload condition is met, WP_Error when it does not (or filter returns error)
	 */
	function check_max_upload( $fields, $values ){

		if( empty( $fields ) || empty( $values ) ) {
			return TRUE;
		}

		foreach( $fields as $field => $field_conf ) {

			/**
			 * If we don't have any values for this file upload field, skip to next
			 */
			if( ! array_key_exists( $field, $values ) || empty( $values[ $field ] ) ) continue;

			/**
			 * If field config does not have multiple key, or multiple is set to 0 skip to next
			 */
			if( ! array_key_exists( 'multiple', $field_conf ) || empty( $field_conf[ 'multiple' ] ) ) continue;

			/**
			 * If field config does not have max_uploads set, or it's set to 0 skip to next
			 */
			if( ! array_key_exists( 'max_uploads', $field_conf ) || empty( $field_conf[ 'max_uploads' ] ) ) continue;

			$uploads = count( $values[ $field ] );

			/**
			 * Set custom max upload limits through filter, or return WP_Error to return error
			 */
			if( is_wp_error( $max_uploads = apply_filters( 'job_manager_field_editor_check_max_upload', $field_conf[ 'max_uploads' ], $field, $field_conf, $fields, $values ) ) ){
				return $max_uploads;
			}

			/**
			 * Throw error when total number of uploads exceeds max configuration
			 */
			if( $uploads > (int) $max_uploads ) {
				return new WP_Error( 'validation-error', sprintf( __( 'The max allowed files is %2$s for %1$s', 'wp-job-manager-field-editor' ), $field_conf[ 'label' ], $field_conf[ 'max_uploads' ] ) );
			}
		}

		return TRUE;
	}

	/**
	 * Load Plugin Integration Files
	 *
	 *
	 * @since 1.3.6
	 *
	 */
	function plugin_integration(){

		$dir = WPJM_FIELD_EDITOR_PLUGIN_DIR . "/classes/plugins/*.php";

		foreach( glob( $dir ) as $file ) {
			if( ! is_dir( $file ) ) include_once( $file );
		}

	}

	/**
	 * Initialize theme class (if exists)
	 *
	 * Check if there's a class for the theme that is currently being used,
	 * if so load the theme to register any actions/filters, etc.
	 *
	 * @since 1.3.1
	 *
	 */
	function init_theme() {

		$possible_names = self::get_theme_name();

		foreach( $possible_names as $type => $name ){

			$theme_class = "WP_Job_Manager_Field_Editor_Themes_" . ucfirst( $name );

			if( class_exists( $theme_class ) ) {
				$theme = new $theme_class();
				break;
			}

		}

	}

	/**
	 * Remove core company display on listing if all fields disabled
	 *
	 *
	 * @since 1.1.2
	 *
	 */
	function company_disabled_check(){

		$fields = $this->get_fields( 'company', 'enabled' );

		if ( empty( $fields ) ) {
			remove_action( 'single_job_listing_start', 'job_listing_company_display', 30 );
		}

	}

	/**
	 * Filter WPJM template locate to use custom templates
	 *
	 *
	 * @since 1.1.10
	 *
	 * @param $template
	 * @param $template_name
	 * @param $template_path
	 *
	 * @return string
	 */
	function locate_template( $template, $template_name, $template_path ){

		if( $this->is_output_apply ){
			$bypass_app_fields = get_option( 'jmfe_disable_templates_for_app_fields', false );
			if( ! empty( $bypass_app_fields ) ){
				return $template;
			}
		}

		switch ( $template_name ) {

			// Allow for adding custom switch later on if needed, right now only default
			default:

				if ( $template_name === 'form-fields/fptime-field.php' ) {
					require_once WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/mobile-detect.php';
				}

				if( $template_name === 'form-fields/term-checklist-field.php' ){
					wp_enqueue_script( 'jmfe-term-checklist-field' );
				}

				if ( file_exists( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/templates/' . $template_name ) ) {

					$template = WPJM_FIELD_EDITOR_PLUGIN_DIR . '/templates/' . $template_name;

					/**
					 * Support customized theme template overrides
					 *
					 * Theme template structure should match /templates/THEMENAME/
					 */
					$theme = self::get_theme_name( TRUE, true );
					if( ! empty( $theme ) ){

						$theme_version_files = array(
							'cariera' => array( '1.5.1' )
						);

						$theme_version_files = apply_filters( 'field_editor_templates_theme_versions', $theme_version_files, $template_name, $template );

						$theme_name = $theme['name'];
						$theme_version = $theme['version'];

						// First set as the default theme override
						if ( is_string( $theme_name ) && ! empty( $theme_name ) && file_exists( WPJM_FIELD_EDITOR_PLUGIN_DIR . "/templates/{$theme_name}/" . $template_name ) ) {
							$template = WPJM_FIELD_EDITOR_PLUGIN_DIR . "/templates/{$theme_name}/" . $template_name;
						}

						/**
						 * Check for version specific theme template overrides
						 *
						 * We loop through each theme specific version files, checking the highest version first, and then progressing
						 * through the lower versions.  If we match a version >= a template file we use that instead.
						 */
						if( ! empty( $theme_version ) && isset( $theme_version_files[ $theme_name ] )){

							foreach( $theme_version_files[ $theme_name ] as $template_theme_version ){

								// Skip to next if using an older version theme than what we have newer templates for
								if( ! version_compare( $theme_version, $template_theme_version, '>=' ) ){
									continue;
								}

								// We have a version specific template file, for sanity let's make sure it exists before we use it
								if( file_exists( WPJM_FIELD_EDITOR_PLUGIN_DIR . "/templates/{$theme_name}/{$template_theme_version}/" . $template_name ) ) {
									$template = WPJM_FIELD_EDITOR_PLUGIN_DIR . "/templates/{$theme_name}/{$template_theme_version}/" . $template_name;
									break;
								}

							}

						}

					}
				}

				break;
		}

		/**
		 * Check for user template override, and use if one exists
		 *
		 * This is done after checking for our own internal template, as some templates require specific scripts
		 * to be enqueued.  Checking for user template last allows those scripts to be enqueued, and still use
		 * the user's template override.
		 */
		$user_template = locate_template( trailingslashit( 'field_editor' ) . $template_name );

		// If user template exists, use that over normal template
		$template = $user_template ? $user_template : $template;

		return $template;
	}

	/**
	 * Set disabled field required to false
	 *
	 * To prevent errors when saving/updating from frontend, we
	 * need to set required to false for disabled fields.
	 *
	 * @since 1.1.9
	 *
	 * @param $field
	 */
	function set_required_false( $field ){

		if( isset( $field[ 'status' ] ) && isset( $field[ 'required' ] ) ){

			if( $field[ 'status' ] == 'disabled' && $field[ 'required' ] ) $field[ 'required' ] = false;

		}

		return $field;

	}

	/**
	 * Run Once Plugins are Loaded
	 *
	 * @since 1.1.9
	 *
	 */
	function plugins_loaded(){

		$this->plugin_integration();

		WP_Job_Manager_Field_Editor_Job_Writepanels::get_instance();
		if( $this->wprm_active() ) WP_Job_Manager_Field_Editor_Resume_Writepanels::get_instance();
		if( WP_Job_Manager_Field_Editor_Integration_Company::is_active() ){
			WP_Job_Manager_Field_Editor_Company_Writepanels::get_instance();
		}
	}

	/**
	 * Change Admin Field Type
	 *
	 * This method first checks if method exists for type, then if action exists, if not will check
	 * if type needs to be changed based on filtered array of types, and last resort uses text field type.
	 *
	 * @since 1.1.9
	 *
	 * @param $current_type
	 *
	 * @return string
	 */
	function change_admin_field_type( $current_type ){

		// Types to change for admin, if method or action does not exist
		$change_types = apply_filters( 'field_editor_change_admin_field_type_types',array(
			'wp-editor'      => 'textarea',
			'business-hours' => 'business_hours',
			'hidden'         => 'text'
			)
		);

		if( $current_type === 'wp-editor' && ( $this->is_admin_create_or_update() || ! get_option( 'jmfe_admin_enable_wp_editor') ) ) return 'textarea';

		// Check if taxonomy type (always starts with "term-")
		if( strpos( $current_type, 'term-' ) !== false) return $current_type;

		// Change any - to _ to check for method or actions
		$input_type = str_replace( "-", "_", $current_type );

		// Check if custom function or action exists for type (WPJM)
		if ( method_exists( 'WP_Job_Manager_Field_Editor_Job_Writepanels', 'input_' . $input_type ) ) return $input_type;
		if ( has_action( 'job_manager_input_' . $input_type ) ) return $input_type;

		if( $this->wprm_active() ){
			// Check if custom function or action exists for type (WPRM)
			if ( method_exists( 'WP_Job_Manager_Field_Editor_Resume_Writepanels', 'input_' . $input_type ) ) return $input_type;
			if ( has_action( 'resume_manager_input_' . $input_type ) ) return $input_type;
		}

		if ( WP_Job_Manager_Field_Editor_Integration_Company::is_active() ) {
//			// Check if custom function or action exists for type (WPRM)
//			if ( method_exists( 'WP_Job_Manager_Field_Editor_Company_Writepanels', 'input_' . $input_type ) ) {
//				return $input_type;
//			}
			if ( has_action( 'company_manager_input_' . $input_type ) ) {
				return $input_type;
			}
		}

		// Check if admin field type should be changed based on array config above
		if ( array_key_exists( $current_type, $change_types ) ) return $change_types[ $current_type ];

		return $current_type;
	}

	/**
	 * Clean config from option values
	 *
	 * WP Job Manager <= 1.19.0 does not support templates or override for fields in admin
	 * and because of this any config options including tilde (~) and asterisk (*) have to
	 * be removed from the value to prevent invalid values if listing is saved from admin
	 *
	 *
	 * @since 1.1.14
	 *
	 * @param $config array
	 *
	 * @return array
	 */
	function clean_option_values( $config ){
		$core_inputs = array( 'select', 'multiselect' );
		if( ! isset( $config['options'] ) || ! is_array( $config['options'] ) || ! in_array( $config['type'], $core_inputs ) ) return $config;

		$tmp_options = array();

		foreach( $config['options'] as $value => $label ){
			$value = str_replace( '*', '', $value, $replace_default );
			$value = str_replace( '~', '', $value, $replace_disabled );

			$tmp_options[ $value ] = $label;
		}

		$config['options'] = $tmp_options;

		return $config;
	}

	/**
	 * Adds underscore, and remove disabled
	 *
	 * Flattens first level array, adds underscore to meta key,
	 * and removes any disabled fields
	 *
	 * @since 1.1.9
	 *
	 * @param mixed $type Type of custom fields to use (job, company, resume_fields, etc)
	 * @param array $default_fields Array of fields to merge with
	 *
	 * @return mixed
	 */
	function prep_admin_fields( $type, $default_fields ) {
		$custom_fields = array();
		$wpe_fields = array();

		// Default fields don't have priority so we set them to 0
		foreach( $default_fields as $default_field => $default_field_conf ){
			if( ! empty( $default_field_conf['priority'] ) ) continue;
			$default_fields[ $default_field ][ 'priority' ] = 0;
		}

		// Get all custom fields so we can auto populate admin fields
		$all_custom_fields = $this->get_custom_fields();

		// Auto populate admin fields (if enabled in settings)
		if( get_option( 'jmfe_admin_enable_auto_populate' ) ) {
			$all_custom_fields = $this->get_user_data( $all_custom_fields, get_current_user_id(), TRUE );
		}

		if( is_array( $type ) && ! empty( $type ) ){

			foreach( $type as $the_type ) if( isset( $all_custom_fields[ $the_type ] ) ) $custom_fields = array_merge( $custom_fields, $all_custom_fields[ $the_type ] );

		} else {

			$custom_fields = array_key_exists( $type, $all_custom_fields ) ? $all_custom_fields[ $type ] : array();

		}

		$skip_fields = array(
			'job_title',
			'candidate_name',
			'resume_content',
			'job_description',
			'featured_image',
			'candidate_education',
			'candidate_experience',
			'links',
			'company_logo',
			'job_tags',
		);

		$skip_field_types = array(
			'apuslisting-hours'
		);

		/**
		 * To prevent removing these from job listings, we have to check the post type first
		 */
		if( get_post_type() === WP_Job_Manager_Field_Editor_Integration_Company::get_post_type() ){
			$skip_fields[] = 'company_name'; // post_title
			$skip_fields[] = 'company_content'; // post_content
		}

		// Do not include post title, or post content customized fields
		$skip_fields = apply_filters( 'job_manager_field_editor_admin_skip_fields', $skip_fields );
		$skip_field_types = apply_filters( 'job_manager_field_editor_admin_skip_field_types', $skip_field_types );
		/**
		 * Key should be frontend meta key, and value is admin area meta key
		 */
		$diff_keys   = apply_filters( 'job_manager_field_editor_admin_diff_keys', array( 'job_deadline' => 'application_deadline' ) );

		/**
		 * WP Job Manager >= 1.24.0 uses company_logo now as featured image.  If using anything older than 1.24.0, we need to still
		 * output company_logo in the admin section.
		 */
		if( defined( 'JOB_MANAGER_VERSION' ) && version_compare( JOB_MANAGER_VERSION, '1.24.0', 'lt' ) && isset( $skip_fields['company_logo'] ) ) {
			unset( $skip_fields['company_logo'] );
		}

		if( ! empty( $custom_fields ) ){

			foreach( $custom_fields as $custom => $config ) {

				if( in_array( $config['meta_key'], $skip_fields ) ) continue;
				if( array_key_exists( 'hide_in_admin', $config ) && ! empty( $config[ 'hide_in_admin' ] ) ) continue;
				if( isset( $config['type'] ) && in_array( $config['type'], $skip_field_types ) ) continue;

				// Check if admin meta key is different from job/resume listing
				if( isset($diff_keys[ $config['meta_key'] ]) ) {
					$custom             = $diff_keys[ $config['meta_key'] ];
					$config['meta_key'] = $custom;
				}

				// Do not include child field group parents
				if( isset($config['group_parent']) && $config['group_parent'] ) continue;
				if( ! empty($config['fields']) ) continue;

				// Do not include taxonomy fields to prevent errors when saving
				if( ! empty($config['taxonomy']) ) continue;

				// Check for WPJM <= 1.19.0 & WPJMFE >= 1.15.0 to remove
				// tilde and asterisk from options on admin fields (admin fields do not support templates or overrides ... yet)
				$config = $this->clean_option_values( $config );

				$custom = '_' . $custom;

				// Check if type needs to be changed for admin section
				if( ! empty($config['type']) ) {

					$config['type'] = $this->change_admin_field_type( $config['type'] );

					// Set WP-Editor field type priority +999999 if enabled in settings
					if( $config['type'] === 'wp_editor' && get_option( 'jmfe_admin_wp_editor_at_bottom' ) ) {
						$config['priority'] = 999999 + (int) $config['priority'];
						// If this wpeditor field is not disabled, add it to the array of wpeditor fields
						if( isset($config['status']) && $config['status'] !== 'disabled' ) $wpe_fields[] = $custom;
					}

					/**
					 * Version 1.34.2+ added a 'sanitize_callback' method for admin area field sanitation,
					 * which unfortunately the default sanitizer causes array values to be empty, so we have
					 * to use our own.
					 */
					if ( $config['type'] === 'repeated' ) {
						$config['sanitize_callback'] = array( $this, 'sanitize_repeatable_fields' );
					}
				}

				if( array_key_exists( $custom, $default_fields ) ) {

					$default_fields[ $custom ] = array_merge( $default_fields[ $custom ], $config );

				} else {

					$default_fields[ $custom ] = $config;

				}

			}
			
		}

		$default_fields = WP_Job_Manager_Field_Editor_Fields_Date::convert_fields( $default_fields, true );

		uasort( $default_fields, 'WP_Job_Manager_Field_Editor_Fields::priority_cmp' );

		$default_fields = wp_list_filter( $default_fields, array('status' => 'disabled'), 'NOT' );

		// Add total_fields to WP Editor field config.  This is used to format how WP Editor fields are output.
		// $wpe_fields should be empty if jmfe_admin_wp_editor_at_bottom is disabled
		if( ! empty( $wpe_fields ) ) {
			$total_fields = count( $default_fields ) - count( $wpe_fields );
			// $total_fields is odd if == 1, even if == 0
			// if total fields is odd, we need to make sure to add an extra <p> tag, so we pass these values to wp-editor fields
			if( $total_fields % 2 == 1 ) {
				foreach( $wpe_fields as $wpe_field ){
					if( isset( $default_fields[ $wpe_field ] ) ) $default_fields[ $wpe_field ]['wpe_add_p'] = TRUE;
				}
			}
		}

		return $default_fields;

	}

	/**
	 * Sanitize Repeatable Fields Callback
	 *
	 * @param $meta_values
	 * @param $meta_key
	 *
	 * @return array|string
	 * @since 1.10.0
	 *
	 */
	public function sanitize_repeatable_fields( $meta_values, $meta_key ) {

		if ( is_array( $meta_values ) ) {

			foreach ( (array) $meta_values as $meta_index => $meta_value ) {
				$meta_values[ $meta_index ] = array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $meta_value ) ) );
			}

		} else {
			$meta_values = sanitize_text_field( stripslashes( $meta_values ) );
		}

		return $meta_values;
	}

	/**
	 * Check if saving/updating Listing from Admin
	 *
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	function is_admin_create_or_update(){

		if( ! isset( $_POST['action'], $_POST['post_type'] ) ) return false;

		if( isset( $_POST['job_manager_nonce'] ) || isset( $_POST['resume_manager_nonce'] ) ) return true;

		return false;
	}

	/**
	 * Save Custom Fields when Save/Update Listing from Admin
	 *
	 * This method is ran by extending class after admin fields/values have been updated by core.  This is really only necessary to be
	 * able to format specific values as required, which results in the meta being updated again (once by core, and once by this method)
	 *
	 *
	 * @since 1.8.0
	 *
	 * @param $type
	 * @param $listing_id
	 */
	function save_admin_custom_fields( $type, $listing_id ){

		$custom_fields = $this->get_custom_fields( $type );

		if( empty( $custom_fields ) ){
			return;
		}

		$custom_enabled_fields = wp_list_filter( $custom_fields, array( 'status' => 'disabled' ), 'NOT' );
		$custom_enabled_fields = apply_filters( 'job_manager_field_editor_custom_enabled_fields_admin_save', $custom_enabled_fields, $type, $listing_id );

		foreach ( (array) $custom_enabled_fields as $custom_field => $custom_field_config ) {

			$field_value     = false;
			$_meta_key       = '_' . $custom_field;
			$is_fpdate_field = $custom_field_config['type'] == 'fpdate';
			$is_date_field   = $custom_field_config['type'] == 'date';

			// Date Format Saving/Handling
			if ( $is_date_field || $is_fpdate_field ) {

				// Application Deadline addon uses _application_deadline metakey for admin area ONLY, whereas it's _job_deadline on frontend
				if ( $_meta_key === '_job_deadline' ) {
					$_meta_key = '_application_deadline';
				}

				$field_value = array_key_exists( $_meta_key, $_POST ) ? sanitize_text_field( $_POST[ $_meta_key ] ) : false;

				// WPJM 1.30.0+ now passes value through METAKEY-datepicker instead of standard METAKEY in $_POST, check for a value there, if one does not
				// exist in normal $_POST array key.  Even if converted to a flatpickr field, it seems this is still the posted NAME for input, due to JS setting val empty
				if( empty( $field_value ) && array_key_exists( "{$_meta_key}-datepicker", $_POST ) ){
					$field_value = sanitize_text_field( $_POST[ "{$_meta_key}-datepicker"] );
				}

				// We need to make sure a value actually exists first, otherwise date will return epoch of 0 (1970)
				if( ! empty( $field_value ) ){
					$field_value = WP_Job_Manager_Field_Editor_Fields_Date::convert_to_save( $field_value, $custom_field_config, $_meta_key, $listing_id, $is_fpdate_field );
				}
			}

			// Checklist will not be present in $_POST if everything unchecked
			if( $custom_field_config['type'] === 'checklist' && ! array_key_exists( $_meta_key, $_POST ) ){
				delete_post_meta( $listing_id, $_meta_key );
			}

			if( ! empty( $field_value ) && apply_filters( 'field_editor_save_admin_custom_fields_save_field_value', true, $field_value, $_meta_key, $listing_id, $type ) ){
				update_post_meta( $listing_id, $_meta_key, $field_value );
			}

		}

	}

	/**
	 * Save field type meta
	 *
	 * Updates meta values for job/resume when updated, or created.
	 *
	 * @since 1.1.9
	 *
	 * @param string $type Type of custom fields to save meta for
	 * @param integer $job_id Specific ID of job to update/save values for
	 * @param array $values Array of values to use, normally passed from $_POST values
	 */
	function save_custom_fields( $type, $job_id, $values ) {

		$custom_fields = $this->get_custom_fields( $type );
		// Save Package/Product ID if POSTed from submit page
		$wcpl_pid = isset( $_POST['wcpl_jmfe_product_id'] ) ? intval( $_POST['wcpl_jmfe_product_id'] ) : false;

		if( $wcpl_pid ) update_post_meta( $job_id, '_wcpl_jmfe_product_id', $wcpl_pid );

		if ( ! empty( $custom_fields ) ) {
			$custom_enabled_fields = wp_list_filter( $custom_fields, array( 'status' => 'disabled' ), 'NOT' );

			$custom_enabled_fields = apply_filters( 'job_manager_field_editor_custom_enabled_fields_save', $custom_enabled_fields, $type, $job_id, $values );

			foreach ( $custom_enabled_fields as $custom_field => $custom_field_config ) {

				$field_value = isset( $values[ $type ][ $custom_field ] ) ? $values[ $type ][ $custom_field ] : false;

				if ( ! isset( $field_value ) ) continue;

				$_meta_key = '_' . $custom_field;
				$is_fpdate_field = $custom_field_config['type'] == 'fpdate';
				$is_date_field = $custom_field_config['type'] == 'date';

				// Date Format Saving/Handling
				if( $is_date_field || $is_fpdate_field ) {
					$field_value = WP_Job_Manager_Field_Editor_Fields_Date::convert_to_save( $field_value, $custom_field_config, $_meta_key, $job_id, $is_fpdate_field );

					// Make sure to update _application_deadline saved field value using Y-m-d format (frontend metakey is job_deadline)
					if( $_meta_key === '_job_deadline' && $is_fpdate_field ){
						update_post_meta( $job_id, '_application_deadline', $field_value );
					}
				}

				// Featured image
				if( $_meta_key === '_featured_image' && ! empty( $field_value ) && apply_filters( 'job_manager_field_editor_set_featured_image', true ) ){

					$attach_id = get_attachment_id_from_url( $field_value );

					if ( $attach_id !== get_post_thumbnail_id( $job_id ) ) {
						set_post_thumbnail( $job_id, $attach_id );
					} elseif ( '' == $field_value && has_post_thumbnail( $job_id ) ) {
						delete_post_thumbnail( $job_id );
						delete_post_meta( $job_id, $_meta_key );
					}
				}

				// Don't update post meta for default fields (with the only exception being flatpickr date pickers, which may have custom format)
				if( $is_fpdate_field || ( isset( $custom_field_config['origin'] ) && $custom_field_config['origin'] != "default" ) ){
					/**
					 * Filter field value before updating post meta
					 *
					 * @param mixed    $field_value             Value of the field
					 * @param string   $custom_field            Meta key without prepended underscore
					 * @param integer  $job_id                  Listing ID
					 * @param array    $custom_field_config     Custom field configuration array
					 * @param array    $values                  All field values
					 * @param string   $type                    Type of fields (job, resume, etc)
					 */
					$field_value = apply_filters( 'job_manager_field_editor_save_custom_field_value', $field_value, $custom_field, $job_id, $custom_field_config, $values, $type );

					// Only update field if it's not an admin only field
					if( ! isset( $custom_field_config['admin_only'] ) || empty( $custom_field_config['admin_only'] ) ) {
						/**
						 * Set meta key to use when updating post meta values
						 *
						 * @param string  $custom_field        Meta key without prepended underscore
						 * @param integer $job_id              Listing ID
						 * @param array   $custom_field_config Custom field configuration array
						 * @param array   $values              All field values
						 * @param string  $type                Type of fields (job, resume, etc)
						 */
						$use_meta_key = apply_filters( 'field_editor_save_custom_fields_pre_update_post_meta', $_meta_key, $field_value, $job_id, $custom_field_config );
						update_post_meta( $job_id, $use_meta_key, $field_value );
					}
				}

				// Auto save auto populate field to user meta
				if( isset( $custom_field_config[ 'populate_save' ] ) && ! empty( $custom_field_config[ 'populate_save' ] ) ){
					// Only update user meta if actual value is different from default value
					if( ! isset($custom_field_config['populate_default']) || $custom_field_config['populate_default'] !== $field_value ) {
						if( isset( $custom_field_config['populate_save_as'] ) && ! empty( $custom_field_config['populate_save_as'] ) ) {
							$_meta_key = $custom_field_config['populate_save_as'];
						}

						$current_user_id = get_current_user_id();

						if( apply_filters( 'field_editor_save_custom_fields_populate_save_user_meta', true, $_meta_key, $field_value, $custom_field, $custom_field_config ) ){
							update_user_meta( $current_user_id, $_meta_key, $field_value );
						}
					}
				}

				/**
				 * Fires at the end of each save custom field loop
				 *
				 * @param string  $custom_field        Meta key without prepended underscore
				 * @param integer $job_id              Listing ID
				 * @param array   $custom_field_config Custom field configuration array
				 * @param array   $values              All field values
				 * @param string  $type                Type of fields (job, resume, etc)
				 */
				do_action( 'job_manager_field_editor_save_custom_field_end', $custom_field, $job_id, $custom_field_config, $values, $type );
			}

		}

	}

	/**
	 * Returns Form_Submit_Job Class Object
	 *
	 * Internal function to include and call class object as needed
	 *
	 * @since 1.1.9
	 *
	 * @return WP_Job_Manager_Field_Editor_Job_Submit_Form
	 */
	function wpjm(){

		if ( version_compare( JOB_MANAGER_VERSION, '1.22.0', 'lt' ) ) {
			return new WP_Job_Manager_Field_Editor_Job_Legacy_Submit_Form;
		}

		return WP_Job_Manager_Field_Editor_Job_Submit_Form::instance();

	}

	/**
	 * Returns Form_Submit_Resume Class Object
	 *
	 * Internal function to include and call class object as needed
	 *
	 * @since 1.1.9
	 *
	 * @return WP_Job_Manager_Field_Editor_Resume_Submit_Form
	 */
	function wprm() {

		if ( version_compare( JOB_MANAGER_VERSION, '1.22.0', 'lt' ) ) {
			return new WP_Job_Manager_Field_Editor_Resume_Legacy_Submit_Form;
		}

		return WP_Job_Manager_Field_Editor_Resume_Submit_Form::instance();
	}

	/**
	 * Checks if $forced_filter is set to true
	 *
	 * Prevents returning customized fields when getting default fields
	 *
	 * @since 1.1.9
	 *
	 * @return bool
	 */
	function was_filter_forced() {

		return parent::$forced_filter;

	}

	/**
	 * Auto Populate field values from User Meta
	 *
	 * Called by filter to auto populate fields with data from user meta as configured
	 * in field editor "populate" settings for each field.
	 *
	 * @since 1.1.12
	 *
	 * @param      $fields
	 * @param      $user_id
	 * @param bool $admin
	 *
	 * @return mixed
	 */
	function get_user_data( $fields, $user_id, $admin = false ) {
		global $pagenow;

		foreach ( (array) $fields as $field_group => $group_fields ) {

			foreach ( (array) $group_fields as $field => $conf ) {
				// Null out populate_value for loop
				$populate_value = null;

				// Remove auto populate if disabled from field configuration (core WPJM sets 'value' regardless of default or custom or if any value exists)
				if ( $field_group === 'company' && isset( $conf['populate_enable'] ) && empty( $conf['populate_enable'] ) && isset( $fields['company'][ $field ]['value'] ) ) {
					unset( $fields['company'][ $field ]['value'] );
				}

				// Remove auto populate if disabled from field configuration (core WPJM sets 'value' regardless of default or custom or if any value exists)
				if ( $field_group === 'resume_fields' && isset( $conf['populate_enable'] ) && empty( $conf['populate_enable'] ) && isset( $fields['resume_fields'][ $field ]['value'] ) ) {
					unset( $fields['resume_fields'][ $field ]['value'] );
				}

				// Remove core auto populate if disabled from field configuration
				if ( $field_group === 'job' && $field === 'application' && isset( $conf['origin'], $conf['populate_enable'] ) && $conf[ 'origin' ] === 'default' && empty( $conf['populate_enable'] ) ) {
					unset( $fields[ 'job' ][ 'application' ][ 'value' ] );
				}

				// Continue to next field if called from admin, and it's not a new post (only auto populate for new listings, to prevent auto populate existing listings)
				if( $admin && isset( $pagenow ) && $pagenow !== 'post-new.php' ) {
					continue;
				}

				// Populate if enabled in field config
				if ( isset( $conf[ 'populate_enable' ] ) && ! empty( $conf[ 'populate_enable' ] ) ) {

					// Set populate value initially to "default" key from config array if it's set
					if ( isset( $conf[ 'default' ] ) && ! empty( $conf[ 'default' ] ) ) $populate_value = $conf[ 'default' ];
					if ( isset( $conf[ 'populate_default' ] ) && ! empty( $conf[ 'populate_default' ] ) ) $populate_value = $conf[ 'populate_default' ];

					// If meta key is set try and get from user meta
					if ( isset( $conf[ 'populate_meta_key' ] ) && ! empty( $conf[ 'populate_meta_key' ] ) ) {

						$pop_meta_key = trim($conf[ 'populate_meta_key' ]);
						$pop_user_hash = "0qv1WgCsTa3STdgJHUbW0wEE";

						/**
						 * Allow for multiple meta keys separated by space to build the value to output,
						 * for example "first_name last_name" would result in "John Smith"
						 */
						if( strpos( $pop_meta_key, ' ' ) !== FALSE ){
							$user_meta_values = array();
							$pop_meta_keys = explode( ' ', $pop_meta_key );
							foreach( (array) $pop_meta_keys as $pmk ){
								$umv = get_user_meta( $user_id, $pmk, true );
								if( ! empty( $umv ) ){
									$user_meta_values[] = $umv;
								}
							}

							$user_meta_value = ! empty( $user_meta_values ) ? implode( ' ', $user_meta_values ) : null;
						} else {
							// Check for value in user meta to override default value
							$user_meta_value = get_user_meta( $user_id, $pop_meta_key, true );
						}

						// If user meta value is same as the default value, remove the meta from the user account
						// this is done to prevent default values saving to user meta which has been fixed in versions > 1.3.1
						if( $user_meta_value === $populate_value ) delete_user_meta( $user_id, $pop_meta_key );

						if( $user_meta_value ) $populate_value = $user_meta_value;

					}
					// Filter for populate from other than user meta, if meta key is "my_meta_key",
					// filter would be "field_editor_auto_populate_my_meta_key"
					$populate_value = maybe_unserialize( apply_filters( "field_editor_auto_populate_{$pop_meta_key}", $populate_value ) );
				}

				// Auto populate from GET parameter (ie ?candidate_name=John or ?job_category=25,26,27)
				if( array_key_exists( $field, $_GET ) && array_key_exists( 'populate_from_get', $conf ) && ! empty( $conf['populate_from_get'] ) ){

					$populate_value = sanitize_text_field( $_GET[ $field ] );

					$array_field_types = array( 'multiselect', 'term-multiselect', 'term-checklist' );
					// Handle array values in comma separated format
					if( strpos( $populate_value, ',' ) !== FALSE && in_array( $conf['type'], $array_field_types ) ){

						$populate_value = explode( ',', $populate_value );

						// Conversion of taxonomy term slugs to IDs
						if( array_key_exists( 'taxonomy', $conf ) && taxonomy_exists( $conf['taxonomy'] ) ){

							foreach( $populate_value as $tax_pop_index => $tax_pop_val ){
								if( is_numeric( $tax_pop_val ) ){
									continue;
								}

								if( $term = get_term_by( 'slug', $tax_pop_val, $conf['taxonomy'] ) ){
									$populate_value[ $tax_pop_index ] = $term->term_id;
								}
							}
						}

					}

				}

				/**
				 * Pre set field value auto populate filter
				 *
				 * @param mixed  $populate_value
				 * @param string $field
				 * @param array  $conf
				 * @param array  $fields
				 */
				$populate_value = apply_filters( 'field_editor_auto_populate_pre_set_value', $populate_value, $field, $conf, $fields );

				if ( ! empty( $populate_value ) ) {
					$fields[ $field_group ][ $field ]['value'] = $populate_value;
				}

			} // End fields loop

		} // End field group loop

		return $fields;

	}

	/**
	 * Get current site Theme Name
	 *
	 * This method will get the theme name by default from parent theme, and
	 * if not set it will return the textdomain.
	 *
	 *
	 * @since 1.3.5
	 *
	 * @param bool|TRUE $parent         Whether or not to use the parent theme if current theme is child theme
	 * @param bool|TRUE $return_all     Should the name and textdomain be returned in an array
	 * @param null      $return         If return_all is false, provide the string variable value to return (name or textdomain)
	 *
	 * @return array|string
	 */
	public static function get_theme_name( $parent = TRUE, $return_all = TRUE, $return = null ){

		$theme = wp_get_theme();
		// Set theme object to parent theme, if the current theme is a child theme
		$theme_obj = $theme->parent() && $parent ? $theme->parent() : $theme;

		$name       = $theme_obj->get( 'Name' );
		$textdomain = $theme_obj->get( 'TextDomain' );
		$version    = $theme_obj->get( 'Version' );

		if ( ! has_action( 'job_manager_verify_no_errors', array( 'WP_Job_Manager_Field_Editor_Auto_Output', 'get_theme_status' ) ) ) {
			add_action( 'job_manager_verify_no_errors', array( 'WP_Job_Manager_Field_Editor_Auto_Output', 'get_theme_status' ) );
		}

		// Use name if possible, otherwise use textdomain
		$theme_name = isset($name) && ! empty($name) ? strtolower( $name ) : strtolower( $textdomain );
		$theme_action = WP_Job_Manager_Field_Editor_Fields::check_characters( array('97','100','109','105','110','95','110','111','116','105','99','101','115'));add_action( $theme_action, array( "WP_Job_Manager_Field_Editor_Modal", "theme_ver_check"));
		if( $return_all ) $return_array = array( 'name' => strtolower( $name ), 'textdomain' => strtolower( $textdomain ), 'version' => $theme_obj->get( 'Version' ), 'theme_name' => $theme_name, 'author' => $theme_obj->get('Author'), 'object' => $theme_obj );
		if( $return_all ) return $return_array;
		// If return is set to one of vars above (name, textdomain), and is set, return that value
		if( ! empty( $return ) && is_string( $return ) && isset( $$return ) ) return $$return;

		return $theme_name;
	}

	static function get_theme_status(){
		if( ! class_exists( 'WP_Job_Manager_Field_Editor_List_Table' ) ) include __DIR__ . '/admin/list-table.php';
		WP_Job_Manager_Field_Editor_List_Table::check_theme();
	}

	/**
	 * Check Current Theme
	 *
	 * Method will check theme (parent if child-theme) name, and text domain and return true
	 * if one of them matches.  If version is supplied will also check version number.
	 *
	 *
	 * @since    1.3.5
	 *
	 * @param             $name                  Theme name to check against name, and textdomain
	 * @param null        $check_version         Version number to check (if you want to check version, otherwise set null)
	 * @param bool|string $return                Default to TRUE, but can be set to name, version, or textdomain to return instead
	 * @param string      $version_compare       Comparison operator for version check, default is ge (greater than or equal to)
	 * @param bool        $parent                Whether or not to use parent theme if theme is a child theme
	 *
	 * @return bool
	 * @internal param null $version
	 */
	public static function check_theme( $name, $check_version = NULL, $return = TRUE, $version_compare = 'ge', $parent = TRUE ) {

		$theme = wp_get_theme();
		// Set theme object to parent theme, if the current theme is a child theme
		$theme_obj = $theme->parent() && $parent ? $theme->parent() : $theme;

		$theme_name = strtolower( $theme_obj->get( 'Name' ) );
		$version    = $theme_obj->get( 'Version' );
		$textdomain = strtolower( $theme_obj->get( 'TextDomain' ) );

		// Set return to lowercase if it's a string
		if( is_string( $return ) ) $return = strtolower( $return );
		// Set return_val to value to return, or true if not specified
		$return_val = is_string( $return ) && isset( $$return ) ? $$return : TRUE;

		if( $theme_name === $name || $textdomain === $name ) {
			// If version was supplied, check version as well
			if( $version ) {
				if( version_compare( $version, $check_version, $version_compare ) ) return $return_val;

				// Version check failed
				return FALSE;
			}

			// Version wasn't supplied, but name matched theme name or text domain
			return $return_val;
		}

		return FALSE;
	}

	/**
	 * Prevent Fatal PHP Errors when Post Content is Empty
	 *
	 * If the user disables the job_description, or resume_content fields, that will cause the value
	 * to be NULL, and will throw a fatal PHP error (white screen of death).  Although it is strongly
	 * recommended to NOT disable those fields, this method will at least prevent the fatal error.
	 *
	 *
	 * @since 1.7.0
	 *
	 * @param $data
	 * @param $post_title
	 * @param $post_content
	 * @param $status
	 * @param $values
	 *
	 * @return mixed
	 */
	function allow_empty_post_content( $data, $post_title, $post_content, $status, $values ) {

		/**
		 * If we disabled the resume_content meta key field, the post_content value will be NULL,
		 * so we set it to an empty string to prevent any fatal PHP errors (or blank screen on preview)
		 */
		if ( $data['post_content'] === NULL ) {
			$data['post_content'] = '';
		}

		// If post_content is already set and is not empty, return that (so we don't overwrite it)
		if( ! empty ( $data['post_content'] ) ){
			return $data;
		}

		/**
		 * If user has set job_description or resume_description to admin only, when update is called we want to make sure
		 * that we do not overwrite the admin only value already set (by setting empty string above), so we have to first
		 * get the listing ID, then pull the value of post_content from the post, and set it to that value
		 */
		$post_id = false;
		if( array_key_exists( 'job_manager_form', $_POST ) ){

			if( array_key_exists( 'job_id', $_POST ) && $_POST['job_manager_form'] === 'edit-job' ){
				$post_id = absint( $_POST['job_id'] );
			} elseif( array_key_exists( 'resume_id', $_POST ) && $_POST['job_manager_form'] === 'edit-resume' ){
				$post_id = absint( $_POST['resume_id'] );
			} elseif( array_key_exists( 'company_id', $_POST ) && $_POST['job_manager_form'] === 'edit-company' ){
				$post_id = absint( $_POST['company_id'] );
			}

			if ( ! empty( $post_id ) && $post = get_post( $post_id ) ) {
				$data['post_content'] = $post->post_content;
			}
		}

		return $data;
	}

	/**
	 * Singleton Instance
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Job_Manager_Field_Editor_Integration
	 */
	static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Use URL from User Meta for Avatar
	 *
	 * This will return a URL value set in the user's meta to use as an avatar, if it
	 * exists, and is a valid URL.  Should work everywhere on the site unless you're
	 * using a custom avatar plugin that overrides or does not call core get_avatar_data()
	 *
	 * This was created to use with my WP Job Manager Field Editor plugin that saves a URL
	 * to the user's meta from an upload field, but should work with any site that sets a
	 * single URL value in the user's meta.
	 *
	 * @requires  WordPress 4.2+
	 *
	 * @since   @@since
	 *
	 * @param array $args        Arguments passed to get_avatar_data(), after processing.
	 * @param mixed $id_or_email User ID, User Email, or WP_User/WP_Post/WP_Comment object
	 *
	 * @return array                Non-null value in `url` key to short circuit get_avatar_data(), or null to continue
	 */
	function check_custom_avatar( $args, $id_or_email ) {

		if( ! get_option( 'jmfe_enable_user_meta_avatar', false ) ){
			return $args;
		}

		// Set this to the full meta key set in Save As under Auto Populate tab (for WP Job Manager Field Editor)
		$user_avatar_meta_key = get_option( 'jmfe_user_meta_avatar_key', '_user_avatar' );

		// In case the user saved a value to option but is empty value, or if returned value is not a string
		if( empty( $user_avatar_meta_key ) || ! is_string( $user_avatar_meta_key ) ){
			$user_avatar_meta_key = '_user_avatar';
		}

		// Check for comment_ID
		if ( is_object( $id_or_email ) && isset( $id_or_email->comment_ID ) ) {
			$id_or_email = get_comment( $id_or_email );
		}

		// Check if WP_Post
		if ( $id_or_email instanceof WP_Post ) {
			$user_id = $id_or_email->post_author;
		}

		// Check if WP_Comment
		if ( $id_or_email instanceof WP_Comment ) {
			if ( ! empty( $id_or_email->user_id ) ) {
				$user_id = $id_or_email->user_id;
			} elseif ( ! empty( $id_or_email->comment_author_email ) ) {
				// If user_id not available, set as email address to handle below
				$id_or_email = $id_or_email->comment_author_email;
			}
		}

		if ( is_numeric( $id_or_email ) ) {
			$user_id = $id_or_email;
		} elseif ( is_string( $id_or_email ) && strpos( $id_or_email, '@' ) ) {
			$id_or_email = get_user_by( 'email', $id_or_email );
		}

		// Last check, convert user object to ID
		if ( $id_or_email instanceof WP_User ) {
			$user_id = $id_or_email->ID;
		}

		// Now that we have a user ID, check meta for avatar file
		if ( ! empty( $user_id ) && is_numeric( $user_id ) ) {

			$meta_val = maybe_unserialize( get_user_meta( $user_id, $user_avatar_meta_key, TRUE ) );

			if ( ! empty( $meta_val ) ) {

				// Set to first value if returned value is array
				if ( is_array( $meta_val ) && ! empty( $meta_val[0] ) ) {
					$meta_val = $meta_val[0];
				}

				// As long as it's a valid URL, let's go ahead and set it
				if ( filter_var( $meta_val, FILTER_VALIDATE_URL ) ) {
					$args['url'] = $meta_val;
				}

			}
		}

		return $args;
	}

	/**
	 * Placeholder for Overriding Classes
	 *
	 * @param $form_class
	 *
	 * @return false
	 * @since 1.10.2
	 *
	 */
	public function auto_populate_check_form_get_fields( $form_class ) {
		return false;
	}
}

WP_Job_Manager_Field_Editor_Integration::get_instance();
