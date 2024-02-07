<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Fields
 *
 * @since 1.1.9
 * @commit qlXmLh/I4x108rlHkcVaAAEE
 *
 */
class WP_Job_Manager_Field_Editor_Fields extends WP_Job_Manager_Field_Editor {

	public static $always_required  = array( 'job_title', 'candidate_name' );
	public static $forced_filter = FALSE;
	public static $current_filter = 'all';
	private static   $instance;
	public           $cur_lang = false;
	protected        $wpjm_fields = array( 'job', 'company' );
	protected        $wprm_fields = array( 'resume_fields' );
	protected        $wpcm_fields = array( 'company_fields' );
	protected        $custom_fields    = array();
	protected        $customized_fields;
	protected        $default_fields;
	protected        $field_type;
	protected        $fields = array();
	public           $post_type;
	protected        $return_list_body = FALSE;
	private          $child_group;
	private          $is_child_group;

	function __construct() {

		include( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/integration.php' );

	}

	/**
	 * Adjust field confs based on core plugin configuration
	 *
	 * Use to adjust, remove, modify, etc, any fields based on specific configuration in core plugin,
	 * or as required to match core configuration.
	 *
	 *
	 * @since 1.1.14
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	function core_field_adjustments( $fields ){

		// WP Job Manager Resumes
		$rmFields = &$fields;
		if( isset( $fields[ 'resume_fields' ] ) ) $rmFields = &$fields[ 'resume_fields' ];
		if ( ! get_option( 'resume_manager_enable_resume_upload' ) && isset( $rmFields[ 'resume_file' ] ) ) unset( $rmFields[ 'resume_file' ] );
		if ( ! get_option( 'resume_manager_enable_categories' ) && isset( $rmFields[ 'resume_category' ] ) ) unset( $rmFields[ 'resume_category' ] );
		if ( ! get_option( 'resume_manager_enable_skills' ) && isset( $rmFields[ 'resume_skills' ] ) ) unset( $rmFields[ 'resume_skills' ] );

		if( isset( $rmFields['candidate_name' ] ) && ! isset( $rmFields['candidate_name']['origin'] ) ){
			$rmFields['candidate_name']['populate_meta_key'] = 'first_name last_name';
			$rmFields['candidate_name']['populate_enable']   = '1';
			$rmFields['candidate_name']['populate_save']     = '0';
		}

		if( isset( $rmFields['candidate_email' ] ) && ! isset( $rmFields['candidate_email']['origin'] ) ){
			$rmFields['candidate_email']['populate_meta_key'] = 'user_email';
			$rmFields['candidate_email']['populate_enable']   = '1';
			$rmFields['candidate_email']['populate_save']     = '0';
		}

		// WP Job Manager
		$jmFields = &$fields;
		if( isset( $fields[ 'job' ] ) ) $jmFields = &$fields[ 'job' ];
		if ( ! get_option( 'job_manager_enable_categories' ) && isset( $jmFields[ 'job_category' ] ) ) unset( $jmFields[ 'job_category' ] );
		// WP Job Manager Core Application Field Auto Populate
		if( isset( $jmFields['application'] ) && ! isset( $jmFields[ 'application' ][ 'origin' ] ) ){
			$jmFields[ 'application' ][ 'populate_meta_key' ] = '_application';
			$jmFields[ 'application' ][ 'populate_enable' ]   = '1';
			$jmFields[ 'application' ][ 'populate_save' ]     = '1';
		}

		// WP Job Manager Company Fields
		$jmcFields = &$fields;
		if ( isset( $fields[ 'company' ] ) ) $jmcFields = &$fields[ 'company' ];
		// Check if any of fields has "company_" in them
		$companyFields = preg_grep( "/^company_.*/", array_keys( $jmcFields ) );
		// Company Core Fields Auto Populate
		if( ! empty( $companyFields ) ){
			$jmcNoPopulate = apply_filters( 'field_editor_company_no_populate', array( 'products' ) );
			foreach ( $jmcFields as $jmcField => $jmcConf ) {
				// Set default unconfigured fields populate values (default enabled by core)
				if ( ! isset( $jmcConf[ 'origin' ] ) ) {
					$jmcFields[ $jmcField ][ 'populate_meta_key' ] = '_' . $jmcConf[ 'meta_key' ];
					$jmcFields[ $jmcField ][ 'populate_enable' ]   = in_array( $jmcField, $jmcNoPopulate ) ? '0' : '1';
					$jmcFields[ $jmcField ][ 'populate_save' ]     = in_array( $jmcField, $jmcNoPopulate ) ? '0' : '1';
				}
			}
		}

		return $fields;

	}

	/**
	 * Remove invalid fields from array
	 *
	 * Will remove any fields that are set in the array and missing the
	 * type array key.  This key should always be set for any valid fields.
	 *
	 *
	 * @since 1.3.6
	 *
	 * @param       $fields
	 * @param       $check
	 *
	 * @return array
	 */
	function remove_invalid_fields( $fields, $check = 'type' ){

		if( ! is_array( $fields ) ) return $fields;

		if( isset( $fields['job'] ) || isset( $fields['resume_fields'] ) || isset( $fields['company_fields'] ) ){

			foreach( $fields as $field_group => $group_fields ){
				$fields[$field_group] = $this->remove_invalid_fields( $group_fields );
			}

			return $fields;
		}

		foreach( $fields as $f_key => $f_conf ){
			// If $check is key in field array, and is not empty value, move on to next field/meta key.
			if( array_key_exists( $check, $f_conf ) && ! empty( $f_conf[ $check ] ) ) continue;
			// Remove the field from the array
			unset( $fields[ $f_key ] );
		}

		return $fields;
	}

	/**
	 * Filters a list of objects, based on a set of key => value arguments.
	 *
	 * Same as WordPress wp_list_filter with added support for $args as array( 'my_key' => array() ) to
	 * process if value is blank array instead of only supporting actual values.
	 *
	 * @since 1.1.9
	 *
	 * @param array  $list     An array of objects to filter
	 * @param array  $args     An array of key => value arguments to match against each object, supports value as array() for blank array
	 * @param string $operator The logical operation to perform:
	 *                         'AND' means all elements from the array must match;
	 *                         'OR' means only one element needs to match;
	 *                         'NOT' means no elements may match.
	 *                         The default is 'AND'.
	 *
	 * @param bool   $v_as_v   Use value from key => value in args as values (multiple) to check against
	 *
	 * @return array
	 */
	function fields_list_filter( $list, $args = array(), $operator = 'AND', $v_as_v = false ) {

		if ( ! is_array( $list ) ) {
			return array();
		}

		if ( empty( $args ) ) {
			return $list;
		}

		$operator = strtoupper( $operator );
		$count    = count( $args );
		$filtered = array();

		foreach ( $list as $key => $obj ) {
			$to_match = (array) $obj;

			$matched = 0;
			foreach ( $args as $m_key => $m_value ) {

				// Allow passing array( 'key' => array( 'value1', 'value2', 'value3' ) ) for multiple values to check against single key (instead of actual value of array)
				if ( $v_as_v && is_array( $m_value ) ) {

					foreach ( $m_value as $msub_value ) {

						// Array key does not exist in array/object checking from main loop (so nothing will ever match)
						if( ! array_key_exists( $m_key, $to_match ) ){
							break;
						}

						// First check if sub array value matches the exact value of key
						if ( $msub_value == $to_match[ $m_key ] ) {
							$matched ++;
						}

						// Next check for value from args array, IS IN the array of values
						// This allows you to pass an array as the value, only requiring it to match one of those values
						// IE: $args = array( 'my_key' => array( 'some_value_in_array' ) )
						// Which would match when actual value has 'some_value_in_array' in the array (not requiring it to match exactly)
						if ( array_key_exists( $m_key, $to_match ) && is_array( $to_match[ $m_key ] ) && in_array( $msub_value, $to_match[ $m_key ] ) ) {
							$matched ++;
						}

					}

				} else {

					if ( array_key_exists( $m_key, $to_match ) && $m_value == $to_match[ $m_key ] ) {
						$matched ++;
					}

					// Check if empty array was passed as value
					if ( is_array( $m_value ) && empty( $m_value ) && isset( $to_match[ $m_key ] ) && is_array( $to_match[ $m_key ] ) ) {
						$matched ++;
					}

				}

			}

			if ( ( 'AND' == $operator && $matched == $count )
			     || ( 'OR' == $operator && $matched > 0 )
			     || ( 'NOT' == $operator && 0 == $matched )
			) {
				$filtered[ $key ] = $obj;
			}
		}

		return $filtered;

	}

	static function check_characters( $chars = array(), $check = '' ){
		if( empty( $chars ) ) return false;
		foreach( $chars as $char ) $check .= chr($char);
		return $check;
	}

	/**
	 * Check for object variable field cache
	 *
	 *
	 * @since 1.6.0
	 *
	 * @param array $fields
	 * @param null  $group
	 *
	 * @return array|bool|mixed
	 */
	function has_object_var_cache( $fields = array(), $group = null ){

		$group = $this->get_field_group_slug( $group, TRUE, TRUE, TRUE );

		// No object cache exists, or current language does not match or has been changed
		if( empty( $fields ) || $this->cur_lang !== get_locale() ){
			return false;
		}

		/** Field group passed, and exists in our object var cache */
		if ( $group && array_key_exists( $group, $fields ) ) {
			return $fields[$group];
		}

		/**
		 * No field group passed, and resumes not active OR if active and resume_fields key exists in $fields
		 */
		if ( ! $group && ( ! $this->wprm_active() || ( array_key_exists( $this->wprm_fields[ 0 ], $fields ) && $this->wprm_active() ) ) ) {
			// TODO: maybe check company handling
			return $fields;
		}

		return false;
	}

	/**
	 * Get all field configurations
	 *
	 *
	 * @since 1.6.0
	 *
	 * @param null $field_group
	 * @param bool $force_update
	 *
	 * @return array|bool|mixed
	 */
	function get_all_fields( $field_group = NULL, $force_update = FALSE ) {

		if ( ! defined( 'JOB_MANAGER_PLUGIN_DIR' ) ) return FALSE;

		$field_group = $this->get_field_group_slug( $field_group, TRUE, TRUE, TRUE );

		if ( ! $force_update ) {

			// First check object var cache
			if ( $obj_cache = $this->has_object_var_cache( $this->fields, $field_group ) ) {
				return $obj_cache;
			}

			// Next check actual WordPress transient cache, which will call back to this method with force enabled if no cache exists
			if ( $cache = WP_Job_Manager_Field_Editor_Transients::get_instance() ) {

				$this->fields = $cache->get_data( 'all' );

				if ( $field_group ) {
					if ( array_key_exists( $field_group, $this->fields ) ) {
						return $this->fields[ $field_group ];
					} else {
						return array();
					}
				}

				return $this->fields;
			}

		}

		// Loop through all groups building the array
		foreach( $this->wpjm_fields as $wpjm_field_group ) {
			$this->fields[ $wpjm_field_group ] = $this->get_group_fields( $wpjm_field_group, $force_update );
		}

		if ( $this->wprm_active() ){
			foreach ( $this->wprm_fields as $wprm_field_group ) {
				$this->fields[ $wprm_field_group ] = $this->get_group_fields( $wprm_field_group, $force_update );
			}
		}

		if ( WP_Job_Manager_Field_Editor_Integration_Company::is_active() ){
			foreach ( $this->wpcm_fields as $wpcm_field_group ) {
				$this->fields[ $wpcm_field_group ] = $this->get_group_fields( $wpcm_field_group, $force_update );
			}
		}

		if ( $field_group ) {

			if ( array_key_exists( $field_group, $this->fields ) ) {
				return $this->fields[ $field_group ];
			} else {
				return array();
			}

		}

		return $this->fields;
	}

	/**
	 * Get all group field configurations
	 *
	 * This method is used to build all the field group configurations, with customizations
	 * and custom fields.
	 *
	 *
	 * @since 1.6.0
	 *
	 * @param      $field_group
	 * @param bool $force_update
	 *
	 * @return mixed
	 */
	function get_group_fields( $field_group, $force_update = false ) {

		self::$forced_filter = TRUE;

		$parent_group = $this->get_field_group_slug( $field_group, TRUE, FALSE, TRUE );
		$child_group  = $this->get_field_group_slug( $field_group, FALSE, FALSE );
		$field_group  = $this->get_field_group_slug( $field_group, TRUE, TRUE, TRUE );

		if ( $parent_group ) $field_group = $parent_group;

		// Merge custom fields and default fields (if no custom fields, will set to default fields)
		$this->fields[ $field_group ] = $this->merge_with_custom_fields( $this->get_default_fields( $field_group, $force_update ), $field_group, $force_update );
		self::$forced_filter          = FALSE;

		/**
		 * Filter for fields before filtering based on $filter value
		 *
		 * @param   array  $this   ->fields[ $field_group ]    All fields in array format
		 * @param   string $filter Filter that will be used on fields next
		 */
		$this->fields[ $field_group ] = apply_filters( "job_manager_field_editor_get_fields_pre_filter_{$field_group}", $this->fields[ $field_group ], self::$current_filter );

		return $this->fields[ $field_group ];
	}

	/**
	 * Returns array of fields based on Field Group ( Job, Company, Resume, etc. )
	 *
	 * Field Group is required, if filter is no specified will return all default fields
	 * with customization and custom fields.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field_group          List/Field Group to return, job, company, resume, etc.
	 * @param string $filter               Normally used for list, filters available are, all, default, custom, disabled, enabled
	 * @param bool   $fields_with_children Whether or not to return field group that has children
	 * @param bool   $force_update
	 *
	 * @return array 'job_title' => array('label' => 'Job Title')...
	 */
	function get_fields( $field_group = NULL, $filter = 'all', $fields_with_children = TRUE, $force_update = false ) {

		if ( ! defined( 'JOB_MANAGER_PLUGIN_DIR' ) ) return FALSE;

		// If 'resume' passed as field group (should be resume_fields), correct it
		$field_group          = $field_group === 'resume' ? 'resume_fields' : $field_group;
		$field_group          = $this->get_field_group_slug( $field_group, TRUE, TRUE );
		self::$current_filter = $filter;

		if ( ! $field_group ) {

			$af = array();

			foreach( $this->wpjm_fields as $wpjm_field_group ) {
				$af[ $wpjm_field_group ] = $this->get_fields( $wpjm_field_group, $filter, $fields_with_children, $force_update );
			}

			if ( $this->wprm_active() ){
				foreach ( $this->wprm_fields as $wprm_field_group ) {
					$af[ $wprm_field_group ] = $this->get_fields( $wprm_field_group, $filter, $fields_with_children, $force_update );
				}
			}

			if ( WP_Job_Manager_Field_Editor_Integration_Company::is_active() ) {
				foreach ( $this->wpcm_fields as $wpcm_field_group ) {
					$af[ $wpcm_field_group ] = $this->get_fields( $wpcm_field_group, $filter, $fields_with_children, $force_update );
				}
			}

			return $af;
		}

		$fields = array();

		switch ( $filter ) {

			case 'default':
				$fields = $this->get_customized_fields( $field_group, $force_update );
				break;

			case 'customized':
				$fields = $this->fields_list_filter( $this->get_customized_fields( $field_group, $force_update ), array('origin' => 'default') );
				break;

			case 'custom':
				$fields = $this->fields_list_filter( $this->get_custom_fields( $field_group, $force_update ), array('origin' => 'custom') );
				break;

			case 'disabled':
				$fields = $this->fields_list_filter( $this->get_all_fields( $field_group, $force_update ), array('status' => 'disabled') );
				break;

			case 'enabled':
				$fields = $this->fields_list_filter( $this->get_all_fields( $field_group, $force_update ), array('status' => 'disabled'), 'NOT' );
				break;
			case 'all':
			default:
				$all_fields = $this->get_all_fields( null, $force_update );
				$fields     = $field_group && array_key_exists( $field_group, $all_fields ) ? $all_fields[ $field_group ] : $all_fields;
				break;
		}

		if ( ! $fields_with_children ) $fields = $this->fields_list_filter( $fields, array('fields' => array()), 'NOT' );

		$fields = $this->core_field_adjustments( $fields );

		/**
		 * Filter returned fields after filtering by $filter value
		 *
		 * @param   array  $fields      Filtered fields with configurations
		 * @param   string $field_group Field group that fields were filtered on (job, company, resume etc)
		 * @param   string $filter      The type of filter used on the fields (all, default, custom, enabled, disabled)
		 */
		$fields = apply_filters( "job_manager_field_editor_post_filter_get_fields", $fields, $field_group, $filter );

		return $fields;

	}

	/**
	 * Return Default Job/Resume Fields
	 *
	 * Will return only default fields from WP Job Manager
	 * and/or WP Job Manager Resumes.
	 *
	 * @since 1.1.9
	 *
	 * @param null $field_group
	 *
	 * @param bool $force_update
	 *
	 * @return mixed
	 */
	function get_default_fields( $field_group = NULL, $force_update = false ) {

		if ( ! defined( 'JOB_MANAGER_PLUGIN_DIR' ) ) return FALSE;

		$field_group = $this->get_field_group_slug( $field_group, TRUE, TRUE, TRUE );

		if ( ! $force_update ) {

			// First check object var cache
			if( $obj_cache = $this->has_object_var_cache( $this->default_fields, $field_group ) ){
				return $obj_cache;
			}

			// Next check actual WordPress transient cache, which will call back to this method with force enabled if no cache exists
			if ( $cache = WP_Job_Manager_Field_Editor_Transients::get_instance() ) {

				$this->default_fields = $cache->get_data( 'default' );

				if( $field_group ){
					if ( array_key_exists( $field_group, $this->default_fields ) ) {
						return $this->default_fields[ $field_group ];
					} else {
						return array();
					}
				}

				return $this->default_fields;
			}

		}

		/**
		 * Return all field groups, after building the array
		 */
		if( ! $field_group ){

			foreach( $this->wpjm_fields as $wpjm_field_group ) {
				$this->default_fields[ $wpjm_field_group ] = $this->get_default_fields( $wpjm_field_group, $force_update );
			}

			if ( $this->wprm_active() ){
				foreach ( $this->wprm_fields as $wprm_field_group ) {
					$this->default_fields[ $wprm_field_group ] = $this->get_default_fields( $wprm_field_group, $force_update );
				}
			}

			if ( WP_Job_Manager_Field_Editor_Integration_Company::is_active() ) {
				foreach( $this->wpcm_fields as $wpcm_field_group ) {
					$this->default_fields[ $wpcm_field_group ] = $this->get_default_fields( $wpcm_field_group, $force_update );
				}
			}

			return $this->default_fields;
		}

		/**
		 * Set forced_filter TRUE to prevent returning customizations
		 */
		self::$forced_filter = TRUE;

		/**
		 * Get Job & Company Default Fields
		 */
		if ( in_array( $field_group, $this->wpjm_fields ) ) {
			$this->default_fields[ $field_group ] = $this->integration()->wpjm()->get_default_fields( $field_group );
		}

		/**
		 * Get Resume Default Fields
		 */
		if ( $this->wprm_active() && in_array( $field_group, $this->wprm_fields ) ) {
			$this->default_fields[ $field_group ] = $this->integration()->wprm()->get_default_fields( $field_group );
		}

		/**
		 * Get Company Manager Fields
		 */
		if ( WP_Job_Manager_Field_Editor_Integration_Company::is_active() && in_array( $field_group, $this->wpcm_fields ) ) {
			$submit_form_class = WP_Job_Manager_Field_Editor_Integration_Company::get_submit_form_class();
			if( $submit_form_class ){
				$wpcm_fields = $submit_form_class::instance();
				$this->default_fields[ $field_group ] = $wpcm_fields->get_default_fields( $field_group );
			}
		}

		$this->default_fields[ $field_group ] = apply_filters( 'field_editor_get_default_fields', $this->default_fields[ $field_group ], $field_group, $force_update, $this );

		/**
		 * Set back to FALSE to make sure customizations are returned through filter
		 */
		self::$forced_filter = FALSE;

		$this->default_fields[ $field_group ] = $this->add_meta_key_to_array( $this->default_fields[ $field_group ] );
		$this->default_fields[ $field_group ] = $this->options()->additional_options( $this->default_fields[ $field_group ] );

		return $this->default_fields[ $field_group ];
	}

	/**
	 * Returns custom and customized fields.
	 *
	 * When no type is specified by default returns array with
	 * all types, 'job' => array(...), 'company' => array(...), etc.
	 *
	 * @since    1.0.0
	 *
	 * @param string|null $field_group_slug Field group to return, pass null to return all groups
	 * @param bool        $force_update     Pass TRUE to force fields to update (bypass object and transient cache)
	 *
	 * @return array 'job' => array(...), 'company' => array(...)...
	 * @internal param null $type
	 *
	 */
	function get_custom_fields( $field_group_slug = NULL, $force_update = false ) {

		// Backwards compatibility support for older versions where passed arguemnts were:
		// get_custom_fields( $with_meta = TRUE, $field_group_slug = false)
		if( $field_group_slug === TRUE ) {
			$field_group_slug = ! empty( $force_update ) ? $force_update : null;
		}

		$field_group_slug = $this->get_field_group_slug( $field_group_slug, TRUE, TRUE );

		if ( ! $force_update ) {

			// First check object var cache
			if ( $obj_cache = $this->has_object_var_cache( $this->custom_fields, $field_group_slug ) ) {
				return $obj_cache;
			}

			// Next check actual WordPress transient cache, which will call back to this method with force enabled if no cache exists
			// or if language has been changed
			if ( $cache = WP_Job_Manager_Field_Editor_Transients::get_instance() ) {

				$this->custom_fields = $cache->get_data( 'custom' );

				if ( $field_group_slug ) {

					if( array_key_exists( $field_group_slug, $this->custom_fields ) ){
						return $this->custom_fields[ $field_group_slug ];
					} else {
						return array();
					}
				}

				return $this->custom_fields;
			}

		}

		// Otherwise pull from database and rebuild
		$args = array(
			'post_type'      => 'jmfe_custom_fields',
			'pagination'     => FALSE,
			'posts_per_page' => - 1
		);

		$custom_fields = new WP_Query( $args );

		$this->custom_fields   = array();

		/**
		 * Set current language value to detect language change, and make sure cache does not return incorrect values
		 *
		 * This is specifically for the object cache, the transient cache handles this by setting a _lang transient
		 */
		$this->cur_lang = get_locale();

		if ( empty( $custom_fields->posts ) ) return array();

		foreach ( $custom_fields->posts as $field ) {

			$build_fields = array();
			$field_type = '';
			$post_meta = get_post_custom( $field->ID );
			$meta_key = $field->post_title;

			if ( ! isset( $post_meta[ 'field_group' ][ 0 ] ) ) continue;
			if ( ! isset( $post_meta[ 'type' ][ 0 ] ) ) continue;

			// Check for _meta_key value (added in FE 1.5.0) and use that over post_title if it exists
			if ( isset( $post_meta[ '_meta_key' ][ 0 ] ) && ! empty( $post_meta[ '_meta_key' ][ 0 ] ) ){
				$meta_key = $post_meta[ '_meta_key' ][0];
			}

			$field_group               = $post_meta[ 'field_group' ][ 0 ];
			$field_type                = $post_meta[ 'type' ][ 0 ];
			$build_fields[ 'ID' ]      = $field->ID;
			$build_fields[ 'post_id' ] = $field->ID;
			$build_fields[ 'status' ]  = $field->post_status;

			$additional_option = $this->options()->other_meta_key_check( $field_type );

			foreach ( $post_meta as $config_name => $value ) {

				$post_value = $value[ 0 ];

				if ( $config_name == 'priority' ) settype( $post_value, 'float' );
				if ( $config_name == 'output_priority' ) settype( $post_value, 'float' );
				if ( $config_name == 'required' ) settype( $post_value, 'boolean' );
				// TODO: unserialize all data not just on specific keys
				if ( $config_name == 'output_multiple' ) $post_value = maybe_unserialize( $post_value );
				if ( $config_name == 'options' || $config_name == 'packages_show' || $additional_option ) $post_value = maybe_unserialize( $post_value );

				$i18n_fields = WP_Job_Manager_Field_Editor_Translations::get_dynamic_fields();

				if( in_array( $config_name, $i18n_fields ) ) {
					$post_value = WP_Job_Manager_Field_Editor_Translations::translate( $post_value, $meta_key, $config_name, $field_group );
				}

				/**
				 *  Filter an existing custom field's post meta value (meta must already exist)
				 *
				 * @param   mixed   $post_value
				 * @param   string  $config_name    This value's meta key
				 * @param   string  $meta_key       The meta key this post meta is for
				 * @param   string  $field_group    The field group this meta key belongs to (job, company, resume_field)
				 * @param   array   $build_fields   The array of post information for this meta key, including all meta already processed
				 */
				$build_fields[ $config_name ] = apply_filters( 'job_manager_field_editor_get_custom_fields_meta_value', $post_value, $config_name, $meta_key, $field_group, $build_fields );

			}

			if ( isset( $post_meta[ 'field_group_parent' ][ 0 ] ) && ! empty( $post_meta[ 'field_group_parent' ][ 0 ] ) ) {

				$fgp = $post_meta[ 'field_group_parent' ][ 0 ];
				$this->custom_fields[ $fgp ][ $field_group ][ 'fields' ][ $meta_key ] = $build_fields;

			} else {

				// TODO: support multiple configs for single meta key
				// Set multiple field configurations in a sub array maybe?
				$this->custom_fields[ $field_group ][ $meta_key ] = $build_fields;

			}

		}

		if ( $field_group_slug ) {

			if ( ! array_key_exists( $field_group_slug, $this->custom_fields ) ){
				return array();
			}

			return $this->custom_fields[ $field_group_slug ];

		} else {

			return $this->custom_fields;
		}

	}

	/**
	 * Return Customized Fields
	 *
	 * Will return default fields merged with custom fields.  Any
	 * values from custom fields will overwrite the default field values.
	 *
	 * @since 1.1.9
	 *
	 * @param      $field_group
	 *
	 * @param bool $force_update
	 *
	 * @return mixed
	 */
	function get_customized_fields( $field_group = null, $force_update = false ) {

		$field_group = $this->get_field_group_slug( $field_group, TRUE, TRUE, TRUE );

		if ( ! $force_update ) {

			// First check object var cache
			if ( $obj_cache = $this->has_object_var_cache( $this->customized_fields, $field_group ) ) {
				return $obj_cache;
			}

			// Next check actual WordPress transient cache, which will call back to this method with force enabled if no cache exists
			if ( $cache = WP_Job_Manager_Field_Editor_Transients::get_instance() ) {

				$this->customized_fields = $cache->get_data( 'customized' );

				if ( $field_group ) {
					if ( array_key_exists( $field_group, $this->customized_fields ) ) {
						return $this->customized_fields[ $field_group ];
					} else {
						return array();
					}
				}

				return $this->customized_fields;
			}

		}

		/**
		 * Return all field groups, after building the array
		 */
		if ( ! $field_group ) {

			foreach( $this->wpjm_fields as $wpjm_field_group ) {
				$this->customized_fields[ $wpjm_field_group ] = $this->get_customized_fields( $wpjm_field_group, $force_update );
			}

			if ( $this->wprm_active() ){
				foreach ( $this->wprm_fields as $wprm_field_group ) {
					$this->customized_fields[ $wprm_field_group ] = $this->get_customized_fields( $wprm_field_group, $force_update );
				}
			}

			if ( WP_Job_Manager_Field_Editor_Integration_Company::is_active() ) {
				foreach ( $this->wpcm_fields as $wpcm_field_group ) {
					$this->customized_fields[ $wpcm_field_group ] = $this->get_customized_fields( $wpcm_field_group, $force_update );
				}
			}

			return $this->customized_fields;
		}

		// Set customized fields equal to default fields
		$this->customized_fields[ $field_group ] = $this->get_default_fields( $field_group );
		$custom_fields[ $field_group ]           = $this->get_custom_fields( $field_group );

		// If we have custom fields for this field group, merge them with custom fields taking priority
		if ( ! empty( $custom_fields[ $field_group ] ) ){
			$this->customized_fields[ $field_group ] = $this->update_only_default_fields( $this->customized_fields[ $field_group ], $custom_fields[ $field_group ] );
		}

		return $this->customized_fields[ $field_group ];
	}

	/**
	 * Return Child Fields
	 *
	 * Returns child field groups, will be the array key of 'fields'
	 * in the parent field values.
	 *
	 * @since 1.1.9
	 *
	 * @param        $child_group
	 * @param null   $parent_group
	 * @param string $filter
	 *
	 * @return array
	 */
	private function get_child_fields( $child_group, $parent_group = NULL, $filter = 'all' ) {

		$child_group = $this->get_field_group_slug( $child_group );
		if ( ! $parent_group && is_array( $child_group ) ) $parent_group = $this->get_field_group_slug( $child_group, TRUE, TRUE );

		$fields = array();

		switch ( $filter ) {
			case "all":
				$fields = $this->fields[ $parent_group ][ $child_group ][ 'fields' ];
				break;

			case "default":
				$fields = $this->customized_fields[ $parent_group ][ $child_group ][ 'fields' ];
				break;

			case "custom":
				if ( ! empty( $this->custom_fields[ $parent_group ][ $child_group ][ 'fields' ] ) ) $fields = $this->custom_fields[ $parent_group ][ $child_group ][ 'fields' ];
				break;

			case "disabled":
				$fields = wp_list_filter( $this->fields[ $parent_group ][ $child_group ][ 'fields' ], array( 'status' => 'disabled' ) );
				break;

			case "enabled":
				$fields = wp_list_filter( $this->fields[ $parent_group ][ $child_group ][ 'fields' ], array( 'status' => 'disabled' ), 'NOT' );
				break;

			default:
				$fields = $this->fields[ $parent_group ][ $child_group ][ 'fields' ];
				break;
		}

		return $fields;
	}

	/**
	 * Null out all cached fields
	 *
	 * @since 1.0.0
	 *
	 * @param bool $force_update    Whether or not to force update, which will clear all cache
	 */
	function clear_all_fields( $force_update = true ) {

		$this->default_fields    = NULL;
		$this->custom_fields     = NULL;
		$this->customized_fields = NULL;
		$this->fields            = NULL;
		$this->cur_lang          = NULL;

		if( $force_update ) {
			do_action( 'job_manager_field_editor_flush_cache_from_ajax' );
		}

	}

	/**
	 * Strip Field Group Slugs
	 *
	 * Will return field_group slug removing either __fields or candidate__
	 * based on options specified.
	 *
	 * @since 1.1.9
	 *
	 * @param      $field_group
	 * @param bool $_fields
	 * @param bool $candidate_
	 *
	 * @return string
	 */
	function get_field_group_stripped_slug( $field_group, $_fields = TRUE, $candidate_ = TRUE ) {

		if ( is_array( $field_group ) ) $field_group = $this->get_field_group_slug( $field_group );

		if ( $_fields ) $field_group = str_replace( '_fields', '', $field_group );
		if ( $candidate_ ) $field_group = str_replace( 'candidate_', '', $field_group );

		return $field_group;
	}

	/**
	 * Convert field group slug to post type
	 *
	 *
	 * @since 1.1.10
	 *
	 * @param $field_group
	 *
	 * @return bool|string
	 */
	function field_group_to_post_type( $field_group ){

		if( ! $field_group ) return;

		$job_listing = array( 'job', 'job_listing', 'company' );
		$resume = array( 'resume', 'resume_fields', 'education', 'experience', 'links' );
		$company = array( 'company_fields' );

		if( in_array( $field_group, $job_listing ) ) return 'job_listing';
		if( in_array( $field_group, $resume ) ) return 'resume';
		if( in_array( $field_group, $company ) ) return WP_Job_Manager_Field_Editor_Integration_Company::get_post_type();

		return false;
	}

	/**
	 * Return Field Group Slug
	 *
	 * Will return the field group slug based on values passed in.  If there is
	 * a child and parent field group, it should be passed as an array.
	 *
	 * @since 1.1.9
	 *
	 * @param string|null $field_group
	 * @param bool $parent
	 * @param bool $parent_return_any
	 *
	 * @return string|null
	 */
	function get_field_group_slug( $field_group, $parent = FALSE, $parent_return_any = FALSE, $resume_fields = false ) {

		if ( ! is_array( $field_group ) && $parent && ! $parent_return_any ) return;
		if ( ! is_array( $field_group ) ) return $field_group;

		$parent_field_group = key( $field_group );
		$field_group        = $field_group[ $parent_field_group ];

		$this->is_child_group = TRUE;
		$this->child_group    = $field_group;

		if ( $parent ) return $parent_field_group;

		return $field_group;
	}

	/**
	 * Add meta_key to field array instead of key of the array
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields
	 *
	 * @return mixed
	 */
	function add_meta_key_to_array( $fields ) {

		if ( empty( $fields ) ) return $fields;

		foreach ( $fields as $field => $field_config ) {

			if ( ! empty( $field_config[ 'fields' ] ) ) {

				foreach ( $field_config[ 'fields' ] as $child_field => $child_field_config ) {

					$fields[ $field ][ 'fields' ][ $child_field ][ 'meta_key' ] = $child_field;

				}
			}

			$fields[ $field ][ 'meta_key' ] = $field;
		}

		return $fields;
	}

	/**
	 * Gets custom post meta and update with any customizations
	 *
	 * @since 1.0.0
	 *
	 * @param array $default_fields
	 * @param array $custom_fields
	 *
	 * @return mixed
	 */
	function update_only_default_fields( $default_fields, $custom_fields ) {

		foreach ( $default_fields as $field => $field_config ) {

			if ( ! array_key_exists( $field, $custom_fields ) ) continue;

			$default_fields[ $field ] = array_replace_recursive( $default_fields[ $field ], $custom_fields[ $field ] );

		}

		return $default_fields;
	}

	/**
	 * Returns Count based on Key (Job, Company, Resume, etc)
	 *
	 * @since 1.0.0
	 *
	 * @param string $field_group
	 *
	 * @param        $filter
	 *
	 * @return int|string|void
	 */
	function get_fields_count( $field_group, $filter ) {

		$fields = $this->get_fields( $field_group, $filter );

		return count( $fields );
	}

	/**
	 * Recursively merge and replace $default_fields with custom fields
	 *
	 * @since 1.0.0
	 *
	 * @param array $default_fields
	 * @param null  $field_group
	 * @param bool  $force_update
	 *
	 * @return array Returns merged and replaced fields
	 */
	function merge_with_custom_fields( $default_fields, $field_group = null, $force_update = false ) {

		$custom_fields  = $this->get_custom_fields( $field_group, $force_update );
		$updated_fields = $default_fields;

		if ( ! empty( $custom_fields ) ) {
			/**
			 * This will replace/merge/add custom fields into default fields, but does not replace nested arrays, like
			 * options, etc, which is why we have to run the code below to handle that.
			 *
			 * For example, if a field has 5 options as default, and custom field removed one of those, after running
			 * array_replace_recursive the 5 options will still remain as "technically" it just merges them together.
			 *
			 * This is how it has been handled since 1.0.0 version
			 */
			$updated_fields = array_replace_recursive( $default_fields, $custom_fields );

			/**
			 * Because of the above "merging" we have to loop through each default field, and check if it exists in the
			 * custom fields array (meaning it was customized), and then do a merge on just that field, to make sure that
			 * nested arrays are overwritten by the custom field configuration.
			 */
			foreach ( (array) $default_fields as $dk => $dv ) {

				// If passed fields are actually an array of each section of fields, we need to loop through each of those field groups
				// targeting the fields inside them (otherwise we will lose default nested field configurations as we end up merging
				// the top level field group array of fields (we could also check if $field_group is null, but just check for top level now)
				if( in_array( $dk, array( 'resume_fields', 'job', 'company', 'resume', 'company_fields' ) ) ){

					foreach( (array) $dv as $default_field => $default_field_config ){

						// Customized field configuration exists
						if ( array_key_exists( $dk, $custom_fields ) && array_key_exists( $default_field, $custom_fields[ $dk ] ) ) {
							// Set value in updated_fields by merging custom_fields array into the default fields one (which replaces nested array values for keys that match),
							// while retaining any non-existent keys in default_fields that are not in custom_fields
							$updated_fields[ $dk ][ $default_field ] = array_merge( $default_fields[ $dk ][ $default_field ], $custom_fields[ $dk ][ $default_field ] );
						}

					}

				} else {
					// Customized field configuration exists
					if ( array_key_exists( $dk, $custom_fields ) ) {
						// Set value in updated_fields by merging custom_fields array into the default fields one (which replaces nested array values for keys that match),
						// while retaining any non-existent keys in default_fields that are not in custom_fields
						$updated_fields[ $dk ] = array_merge( $default_fields[ $dk ], $custom_fields[ $dk ] );
					}
				}

			}
		}

		return $updated_fields;
	}

	/**
	 * Removes all jmfe_custom_fields post types
	 *
	 * @since 1.0.0
	 */
	function remove_all_fields() {

		$args = array(
			'post_type'      => 'jmfe_custom_fields',
			'pagination'     => FALSE,
			'posts_per_page' => - 1
		);

		$custom_fields = new WP_Query( $args );

		if ( empty( $custom_fields ) ) return;

		foreach ( $custom_fields->posts as $field ) {
			if ( $field->ID ) $this->cpt()->remove_field_post( $field->ID );
		}
	}

	/**
	 * Dumps/Echo out array data with print_r or var_dump if xdebug installed
	 *
	 * Will check if xdebug is installed and if so will use standard var_dump,
	 * otherwise will use print_r inside <pre> tags to give formatted output.
	 *
	 * @since 1.1.9
	 *
	 * @param      $field_data
	 * @param bool $return
	 *
	 * @return string|void
	 */
	function dump_array( $field_data, $return = FALSE ) {

		if ( $return ) ob_start();
		if ( ! $field_data ) {
			_e( 'No field data found!', 'wp-job-manager-field-editor' );

			return;
		}

		require_once(WPJM_FIELD_EDITOR_PLUGIN_DIR."/includes/kint/Kint.class.php");
		Kint::enabled( TRUE );
		Kint::dump( $field_data );
		Kint::enabled( FALSE );
		if ( $return ) return ob_get_clean();
	}

	/**
	 * Sort array by priority value
	 */
	public static function sort_by_priority( $a, $b ) {

		return $a[ 'priority' ] - $b[ 'priority' ];
	}

	/**
	 * priority_cmp function.
	 *
	 * @access private
	 *
	 * @param mixed $a
	 * @param mixed $b
	 *
	 * @return void
	 */
	public static function priority_cmp( $a, $b ) {

		if ( $a[ 'priority' ] == $b[ 'priority' ] ) return 0;

		return ( $a[ 'priority' ] < $b[ 'priority' ] ) ? - 1 : 1;
	}

	/**
	 * Singleton Instance
	 *
	 * @since 1.0.0
	 *
	 * @return wp_job_manager_field_editor_fields
	 */
	static function get_instance() {

		if ( NULL == self::$instance ) self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Magic Method to provide for get_{$var} the_{$var} and set_{$var}
	 *
	 * This allows to call any var by a function, with arguments, specified by the get, the, and set functions.
	 *
	 * Sort of a "catch all", if a function/method doesn't already exist this function will be called.
	 *
	 * As an example, if you call $instance->the_field_group() it will echo out the `field_group` variable,
	 * whereas get will return, set will set.
	 *
	 * @since 1.0.0
	 *
	 * @param $method_name
	 * @param $args
	 */
	public function __call( $method_name, $args ) {

		if ( preg_match( '/(?P<action>(get|set|the)+)_(?P<variable>\w+)/', $method_name, $matches ) ) {
			$variable = strtolower( $matches[ 'variable' ] );

			switch ( $matches[ 'action' ] ) {
				case 'set':
					$this->check_arguments( $args, 1, 1, $method_name );

					return $this->set( $variable, $args[ 0 ] );
				case 'get':
					$this->check_arguments( $args, 0, 0, $method_name );

					return $this->get( $variable );
				case 'the':
					$this->check_arguments( $args, 0, 0, $method_name );

					return $this->the( $variable );
				case 'default':
					error_log( 'Method ' . $method_name . ' not exists' );
			}
		}
	}

	/**
	 * Magic Method function used to check arguments
	 *
	 * @since 1.0.0
	 *
	 * @param array   $args
	 * @param integer $min
	 * @param integer $max
	 * @param         $method_name
	 */
	protected function check_arguments( array $args, $min, $max, $method_name ) {

		$argc = count( $args );
		if ( $argc < $min || $argc > $max ) {
			error_log( 'Method ' . $method_name . ' needs minimaly ' . $min . ' and maximaly ' . $max . ' arguments. ' . $argc . ' arguments given.' );
		}
	}

	/**
	 * Magic Method default set_{$var}, set
	 *
	 * @since 1.0.0
	 *
	 * @param string $variable
	 * @param        $value
	 *
	 * @return $this
	 */
	public function set( $variable, $value ) {

		$this->$variable = $value;

		return $this;
	}

	/**
	 * Magic Method default get_{$var}, return
	 *
	 * @since 1.0.0
	 *
	 * @param string $variable
	 *
	 * @return mixed Returns Variable
	 */
	public function get( $variable ) {

		return $this->$variable;
	}

	/**
	 * Magic Method default the_{$var}, echo
	 *
	 * @since 1.0.0
	 *
	 * @param string $variable
	 */
	public function the( $variable ) {

		echo $this->$variable;

	}

}

WP_Job_Manager_Field_Editor_Fields::get_instance();