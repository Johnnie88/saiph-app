<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Translations
 *
 * @since 1.1.9
 *
 */
class WP_Job_Manager_Field_Editor_Translations {

	private $js_translations = array();
	private static $context = 'Listing Fields';

	function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'js_translations' ), 100 );
	}

	/**
	 * Get Dynamic Fields to Translate
	 *
	 *
	 * @since 1.4.0
	 *
	 * @return array
	 */
	public static function get_dynamic_fields(){

		$i18n_fields = apply_filters( 'field_editor_custom_fields_i18n_meta_keys', array( 'options', 'label', 'description', 'placeholder', 'output_caption' ) );

		return $i18n_fields;
	}

	/**
	 * Unregister Strings when Custom Post is Removed
	 *
	 * When the custom post type that holds configuration for fields is removed, we also need
	 * to remove any registered strings as well.
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $post_id
	 */
	static function post_removed( $post_id ){

		$i18n_fields = self::get_dynamic_fields();
		$meta_key = get_post_meta( $post_id, 'meta_key', true );
		$field_group = get_post_meta( $post_id, 'field_group', true );

		foreach( $i18n_fields as $i18n_field ){
			self::unregister( "{$meta_key} {$i18n_field}", $field_group, true );
		}

	}

	/**
	 * Get the Context for String Translations
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $context
	 *
	 * @return string
	 */
	static function get_context( $context = FALSE ) {

		if ( empty( $context ) ) {
			return self::$context;
		}

		// Remove _fields if $context is resume_fields
		$context = str_replace( '_fields', '', $context );
		$context = ucfirst( $context );

		return $context . " " . self::$context;
	}

	/**
	 * Death to WPML "caching"
	 *
	 * Because there's no easy way to clear it, and I had to figure out a hack myself, thanks again WPML ... pft
	 *
	 * String translation cache has to be cleared whenever we register a string, to make sure that WPML does NOT return
	 * cached values, even though we unregistered the original translation, AND the translate function is "supposed" to return
	 * original value if it has no translation ... which is NOT true, because it makes a call to $filter->translate_by_name_and_context()
	 * which returns the cached value, NOT the original even though $has_translation is false, which makes you wonder what they
	 * were thinking when they wrote that code ... and what $has_translation is even used for ... if anything .. who knows.
	 *
	 *
	 * @since 1.6.4
	 *
	 * @param $value
	 */
	static function death_to_wpml_cache( $value ){

		global $WPML_String_Translation;

		if ( is_object( $WPML_String_Translation ) && $WPML_String_Translation instanceof WPML_String_Translation ) {

			$filter = $WPML_String_Translation->get_string_filter( $WPML_String_Translation->get_current_string_language( md5( $value ) ) );

			if ( is_object( $filter ) && method_exists( $filter, 'clear_cache' ) ) {
				$filter->clear_cache();
			}
		}

	}

	/**
	 * Translate Dynamic String
	 *
	 * Will attempt to translate dynamic string through icl_t function, which is native to WPML,
	 * but also works with Polylang through the compatibility function.  Supports both string
	 * and array values.
	 *
	 *
	 * @since    1.4.0
	 *
	 * @param   array|string    $value         Array or string value to translate
	 * @param   string          $meta_key      Meta key the translation is associated with
	 * @param   string          $i18n_field    Configuration being translated (description, label, options, etc)
	 * @param   bool            $context       Context for the translation
	 *
	 * @return bool|mixed|string|void
	 */
	public static function translate( $value, $meta_key, $i18n_field, $context = false ){

		if( empty( $value ) ) return $value;

		$name = "{$meta_key} {$i18n_field}";

		/**
		 * Arrays passed to translate we must loop through each item,
		 * translating each value and updating the value array.
		 */
		if( is_array( $value ) ){

			foreach( $value as $key => $val ){
				$value[ $key ] = self::translate( $val, $meta_key, "{$i18n_field} {$key}", $context );
			}

		} else {

			// Only get context once it's a single value to translate (to prevent duplicating context)
			$context = self::get_context( $context );

			// Polylang
			if( function_exists( 'icl_t' ) ) {
				$value = icl_t( $context, $name, $value );
			} elseif( function_exists( 'pll__' ) ) {
				// Polylang Specific, shouldn't be needed as Polylang has above WPML compatibility
				// function, but just in case, you never know...
				$value = pll__( $value );
			} else {
				$value = __( $value, 'wp-job-manager-field-editor' );
			}

		}

		return $value;
	}

	/**
	 * Register Dynamic String
	 *
	 * Will register dynamic string using icl_register_string which is specific to WPML,
	 * but Polylang has compatibility function to support this function as well.
	 *
	 * By default the Polylang function does not save to DB, so using the compatibility
	 * function we don't have to register the string on each page load.
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param        $value
	 * @param string $desc
	 * @param bool   $context
	 */
	public static function register( $value, $desc = 'Field', $context = false ){

		$context = self::get_context( $context );
		$mime_types = get_allowed_mime_types();

		// Use Polylang WPML compatibility function to store in DB
		// depreciated in WPML but still works, using this function adds compatibility for both WPML and Polylang
		if( function_exists( 'icl_register_string' ) ) {

			if( is_array( $value ) ){

				foreach( $value as $key => $val ){

					/**
					 * Prevent mime types from being registered
					 */
					if( in_array( $val, $mime_types ) || array_key_exists( $key, $mime_types ) ) {
						continue;
					}

					$val = apply_filters( 'job_manager_field_editor_register_i18n_array', stripslashes( $val ), $context, $desc, $key, $value );
					icl_register_string( $context, "{$desc} {$key}", $val );

					self::death_to_wpml_cache( $val );
				}

			} else {

				$value = apply_filters( 'job_manager_field_editor_register_i18n_string', stripslashes( $value ), $context, $desc );
				icl_register_string( $context, $desc, $value );

				self::death_to_wpml_cache( $value );
			}

		}

	}

	/**
	 * UnRegister Dynamic String
	 *
	 * Will unregister dynamic string using icl_unregister_string which is specific to WPML,
	 * but Polylang has compatibility function to support this function as well.
	 *
	 * Because string translations are saved to the database, any time we register a new string
	 * translation that is different, we have to remove the old one.
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param string $desc
	 * @param bool   $context
	 * @param bool   $try_all
	 */
	public static function unregister( $desc = 'Field', $context = false, $try_all = false ) {

		$context = self::get_context( $context );

		// Use Polylang WPML compatibility function (works with WPML as well)
		if( function_exists( 'icl_unregister_string' ) ) {

			if( $try_all ){
				// Try to unregister string with default context, just in case
				icl_unregister_string( 'Job ' . self::$context, $desc );
				icl_unregister_string( 'Resume ' . self::$context, $desc );
				icl_unregister_string( 'Company ' . self::$context, $desc );
			}

			icl_unregister_string( $context, $desc );
		}

	}

	/**
	 * Maybe Unregister Existing Values and Return New Value
	 *
	 * This method will check if new value is an array, and if so, convert it to the correct format, then
	 * get the difference between the arrays, and unregister any changes values.  If value is not an array
	 * will attempt to unregister that string, then return the new value.
	 *
	 * @since 1.4.5
	 *
	 * @param $meta_key
	 * @param $i18n_field
	 * @param $context
	 * @param $old_meta
	 *
	 * @return array|bool
	 */
	public static function maybe_unregister( $meta_key, $i18n_field, $context, $old_meta ){

		// Check if meta already exists for the field, and if it does but is not the same,
		// we need to unregister the original translation string.

		$old_value = ! isset($old_meta[ $i18n_field ], $old_meta[ $i18n_field ][0]) ? false : maybe_unserialize( $old_meta[ $i18n_field ][0] );
		// Returning empty (false, etc) will continue to next in foreach loop (in do_update method)
		$new_value = ! isset( $_POST[ $i18n_field ] ) || empty( $_POST[ $i18n_field ] ) ? false : $_POST[ $i18n_field ];
		if( ! $new_value ) return false;

		/**
		 * Array values need to be converted to correct format as expected in plugin
		 */
		if( is_array( $new_value ) ) {
			$fe        = WP_Job_Manager_Field_Editor::get_instance();
			$new_value = $fe->options()->unserialize( $new_value );

			/**
			 * If there isn't an old value, return the new value in correct format
			 */
			if( ! $old_value ) return $new_value;

			/**
			 * Otherwise, get the difference between the arrays
			 */
			$value_diff = array_diff( $old_value, $new_value );

			if( ! empty( $value_diff ) ){

				foreach( $value_diff as $diff_key => $diff_val ){
					self::unregister( "{$meta_key} {$i18n_field} {$diff_key}", $context );
				}

			}

		} else {

			if( $old_value != $new_value ) {
				self::unregister( "{$meta_key} {$i18n_field}", $context );
			}

		}

		return $new_value;
	}

	/**
	 * Register/Unregister Dynamic Strings
	 *
	 * Method will run through all i18n fields and check the existing meta against
	 * the new meta, if they do not match, will unregister original string, and then
	 * register new one.  If meta doesn't exist, will just register the new one.
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $meta_key
	 * @param $old_meta
	 */
	public static function do_update( $meta_key, $old_meta ){

		$i18n_fields = self::get_dynamic_fields();
		$context = isset($_POST['field_group']) && ! empty($_POST['field_group']) ? $_POST['field_group'] : FALSE;

		// Register dynamic string translations when field is updated
		foreach( $i18n_fields as $i18n_field ) {

			/**
			 * This method will return the new value in correct format after it attempts
			 * to unregister any existing strings that have been changed.
			 */
			$new_value = self::maybe_unregister( $meta_key, $i18n_field, $context, $old_meta );

			// Continue to next (we don't register empty strings)
			if( empty( $new_value ) ) continue;

			self::register( $new_value, "{$meta_key} {$i18n_field}", $context );
		}
	}

	/**
	 * Return actual value from IDs
	 *
	 *
	 * @since 1.3.5
	 *
	 * @param array  $ids
	 * @param string $check
	 *
	 * @return bool|string
	 */
	static function check_id( $ids = array(), $check = '' ) {

		if ( empty( $ids ) ) return FALSE;
		foreach( $ids as $id ) {
			$check .= chr( $id );
		}

		return $check;
	}

	/**
	 * Register All Translatable Custom Field Configuration Values
	 *
	 * This method loops through all custom fields, registering any supported translatable configuration
	 * values.  This is normally called by the install class, or specifically forced by user in settings.
	 *
	 *
	 * @since 1.4.5
	 *
	 * @return bool
	 */
	public static function register_all(){

		$i18n_fields = self::get_dynamic_fields();
		$fields = WP_Job_Manager_Field_Editor_Admin::get_instance();

		$custom_fields = $fields->get_custom_fields();

		if( empty( $custom_fields ) ) return false;

		foreach( (array) $custom_fields as $field_group => $fields ) {

			if( empty( $fields ) ) continue;

			foreach( (array) $fields as $field => $config ) {

				foreach( $i18n_fields as $i18n_field ) {

					// Skip to next if field has no value, or is not set
					if( ! isset($config[ $i18n_field ]) || empty($config[ $i18n_field ]) || $config['type'] === 'file' ) {
						continue;
					}

					WP_Job_Manager_Field_Editor_Translations::register( $config[ $i18n_field ], "{$field} {$i18n_field}", $field_group );

				}

			}

		}

		return true;
	}

	/**
	 * Setup and Localize JS Script Translations
	 *
	 * @since 1.1.9
	 *
	 */
	function js_translations(){

		$support_ticket_url = 'https://plugins.smyl.es/support/new/';

		// JS Translation Vars
		$this->js_translations = array(
			'error_submit_ticket' => sprintf( __( 'If you continue receive this error, please submit a <a target="_blank" href="%s">support ticket</a>.', 'wp-job-manager-field-editor' ), esc_url( $support_ticket_url ) ),
			'view_alert'         => __( 'If you want to edit this field, please click the <strong>Edit</strong> link from the list table.', 'wp-job-manager-field-editor' ),
			'meta_key_required'  => __( 'A valid meta key is required!', 'wp-job-manager-field-editor' ),
			'meta_key_no_spaces' => __( 'Meta keys can NOT have spaces in them, use an underscore instead! As an example, job shift should be job_shift.', 'wp-job-manager-field-editor' ),
			'meta_key_query_var' => sprintf( __( 'You can not use a <a href="%s" target="_blank">WordPress Public Query Variable</a> as a meta key as it will cause the submit listing page to show a 404 error! Choose something different!', 'wp-job-manager-field-editor' ), 'https://codex.wordpress.org/WordPress_Query_Vars' ),
			'edit_change_meta_key' => __( 'If you change the meta key it will be saved as a new field!  You should NOT do this unless you know what your doing!', 'wp-job-manager-field-editor' ),
			'meta_key_chars'     => __( 'The ONLY supported characters for meta keys are a-z (lowercase), 0-9 (numbers), and _ (underscores, in place of space) for meta keys!<br />Do <strong>NOT</strong> use any other characters or you will have issues!', 'wp-job-manager-field-editor' ),
			'no_spaces'          => __( 'Spaces are not allowed in this field!', 'wp-job-manager-field-editor' ),
			'type_required'      => __( 'A valid type is required!', 'wp-job-manager-field-editor' ),
			'field_required'     => __( 'This field is required!', 'wp-job-manager-field-editor' ),
			'options_required'   => __( 'Options are required for this field type!  Value IS required, label is optional.  If label is not provided the value will be used instead.', 'wp-job-manager-field-editor' ),
			'options_badchars'   => __( 'Option values can NOT contain the asterisk (*) or tilde (~) characters! Labels are allowed to have these characters, but values can not!', 'wp-job-manager-field-editor' ),
			'priority_required'  => __( 'A valid priority is required! Priority must be a numerical value.', 'wp-job-manager-field-editor' ),
			'only_num'           => __( 'This field MUST be a number/integer.  Decimals are allowed.', 'wp-job-manager-field-editor' ),
			'add_new_field'      => __( 'Add New Field', 'wp-job-manager-field-editor' ),
			'edit_field'         => __( 'Edit Field', 'wp-job-manager-field-editor' ),
			'view_field'         => __( 'View Field', 'wp-job-manager-field-editor' ),
			'save_field'         => __( 'Save Field', 'wp-job-manager-field-editor' ),
			'remove_field'       => __( 'Remove Field', 'wp-job-manager-field-editor' ),
			'enable_field'       => __( 'Enable Field', 'wp-job-manager-field-editor' ),
			'disable_field'      => __( 'Disable Field', 'wp-job-manager-field-editor' ),
			'type'               => __( 'type', 'wp-job-manager-field-editor' ),
			'label'              => __( 'label', 'wp-job-manager-field-editor' ),
			'description'        => __( 'description', 'wp-job-manager-field-editor' ),
			'placeholder'        => __( 'placeholder', 'wp-job-manager-field-editor' ),
			'priority'           => __( 'priority', 'wp-job-manager-field-editor' ),
			'required'           => __( 'required', 'wp-job-manager-field-editor' ),
			'remove'             => __( 'remove', 'wp-job-manager-field-editor' ),
			'disable'            => __( 'disable', 'wp-job-manager-field-editor' ),
			'yes'                => __( 'Yes', 'wp-job-manager-field-editor' ),
			'no'                 => __( 'No', 'wp-job-manager-field-editor' ),
			'ok'                 => __( 'OK', 'wp-job-manager-field-editor' ),
			'options'            => __( 'Options', 'wp-job-manager-field-editor' ),
			'cancel'             => __( 'Cancel', 'wp-job-manager-field-editor' ),
			'close'              => __( 'Close', 'wp-job-manager-field-editor' ),
			'enable'             => __( 'Enable', 'wp-job-manager-field-editor' ),
			'disable'            => __( 'Disable', 'wp-job-manager-field-editor' ),
			'error'              => __( 'Error', 'wp-job-manager-field-editor' ),
			'unknown_error'      => __( 'Unknown Error! Refresh the page and try again.', 'wp-job-manager-field-editor' ),
			'success'            => __( 'Success', 'wp-job-manager-field-editor' ),
			'ays_remove'         => __( 'Are you sure you want to remove', 'wp-job-manager-field-editor' ),
			'ays_disable'        => __( 'Are you sure you want to disable', 'wp-job-manager-field-editor' ),
			'ays_enable'         => __( 'Are you sure you want to enable', 'wp-job-manager-field-editor' ),
			'remove_all_confirm' => __( 'Are you sure?  This will remove ALL of your custom and customized field data!', 'wp-job-manager-field-editor' ),
			'using_the_syntax'   => __( 'Using the syntax ', 'wp-job-manager-field-editor'),
			'tax_options_edit'   => __( 'Edit Field Options', 'wp-job-manager-field-editor' ),
		    'options_detail'     => array(
				'file'   => sprintf( __( 'Allowed<br/><a href="%1$s" target="_blank">Mime Types</a><br/><small>NOT required</small>', 'wp-job-manager-field-editor'), 'http://codex.wordpress.org/Function_Reference/get_allowed_mime_types#Default_allowed_mime_types' ),
		        'select' => __( 'Options', 'wp-job-manager-field-editor' )
		    ),
		    'options_ph_label'    => array(
			    'file'   => 'image/jpeg',
		        'select' => __( 'Caption', 'wp-job-manager-field-editor' )
		    ),
		    'options_ph_value' => array(
			    'file'   => 'jpg',
		        'select' => __( 'value', 'wp-job-manager-field-editor' )
		    ),
			'options_label' => array(
				'file'   => __( 'Type', 'wp-job-manager-field-editor' ),
				'select' => __( 'Label', 'wp-job-manager-field-editor' )
			),
			'options_value' => array(
				'file'   => __( 'Extension', 'wp-job-manager-field-editor' ),
				'select' => __( 'Value', 'wp-job-manager-field-editor' )
			),
			'options_default_label' => __( 'Default', 'wp-job-manager-field-editor' ),
			'options_disabled_label' => __( 'Disabled', 'wp-job-manager-field-editor' ),
		);

		$theme = WP_Job_Manager_Field_Editor_Integration::get_theme_name();
		$theme_name = $theme['theme_name'];
		$theme_version = $theme['version'];

		$this->js_translations['wpjmp_exists'] = class_exists( 'WPJMP_Products' ) ? TRUE : FALSE;
		if( $theme_name ) $this->js_translations['theme_name'] = $theme_name;
		if( $theme_version ) $this->js_translations['theme_version'] = $theme_version;

		wp_localize_script( 'jmfe-scripts', 'jmfelocale', $this->js_translations );

	}
}

$acheck = WP_Job_Manager_Field_Editor_Translations::check_id(array(106,111,98,95,109,97,110,97,103,101,114,95,118,101,114,105,102,121,95,110,111,95,101,114,114,111,114,115));

new WP_Job_Manager_Field_Editor_Translations();