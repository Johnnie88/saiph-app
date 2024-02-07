<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Fields_Options
 *
 * @since 1.1.9
 *
 */
class WP_Job_Manager_Field_Editor_Fields_Options extends WP_Job_Manager_Field_Editor_Fields {

	public $additional_options_meta_key;
	public $add_quotes = false;

	function __construct() {

		$this->additional_options_meta_key = array(
			'allowed_mime_types' => 'file'
		);

	}

	/**
	 * Unserialize array from Ajax POST
	 *
	 * js serialize is used on the form submitted through ajax and
	 * the array needs to be reformatted to match the format expected
	 * by the rest of the plugin.  If array is not in expected serialized
	 * format it will return the original array.
	 *
	 *
	 * @since 1.2.1
	 *
	 * @param $soptions array
	 *
	 * @return array
	 */
	function unserialize( $soptions ){

		if( ! is_array($soptions) || ! isset( $soptions['option_value'] ) ) return $soptions;

		/**
		 * If there are no options to process, return empty array
		 *
		 * This checks to make sure there are options to process, including a check to make sure that options are
		 * processed even when the first value is 0 (is_numeric)
		 */
		if( empty( $soptions['option_value'][0] ) && ! is_numeric( $soptions['option_value'][0] ) && empty( $soptions['option_label'][0] ) ) {
			return array();
		}

		$options = array();

		foreach( $soptions['option_value'] as $index => $value ){
			// Remove any ~ or * set in value if they snuck through somehow
			$value = str_replace( '*', '', $value );
			$value = str_replace( '~', '', $value );
			$value = stripslashes( $value );

			// If no label was set, use the value for label
			$label = isset( $soptions['option_label'][$index] ) && $soptions[ 'option_label' ][ $index ] !== '' ? $soptions[ 'option_label' ][ $index ] : $value;

			// Add ~ if current option has key set same as the index
			if ( isset( $soptions['option_disabled'][$index] ) ) {
				$value .= '~';
			}

			// Add * if current option has key set same as the index
			if ( isset( $soptions['option_default'][$index] ) ) {
				$value .= '*';
			}

			$options[ $value ] = $label;
		}

		return $options;
	}

	/**
	 * Used to convert dropdown options to/from Array/CSV
	 *
	 * Expects string to be in this format:
	 * value1||Caption 1,value2||Caption2 ....
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $convert_options
	 * @param bool         $to_array
	 * @param bool         $add_slashes
	 * @param bool         $add_quotes
	 *
	 * @return array|string
	 */
	function convert( $convert_options, $to_array = false, $add_slashes = false, $add_quotes = false ) {
		$options = '';
		$defaultSelect = null;
		$this->add_quotes = $add_quotes;

		if ( $to_array ) {

			if( is_array( $convert_options ) ) return $this->unserialize( $convert_options );

			$options   = array();

			if( function_exists('str_getcsv') ){
				$structure = str_getcsv( $convert_options, ',', '"', '\\');
			} else {
				$structure = explode( ',', $convert_options );
			}

			foreach ( $structure as & $option ) {

				if ( false !== strpos( $option, '||' ) ) {

					$parts                  = explode( '||', $option );
					$options[ $parts[ 0 ] ] = $parts[ 1 ];

					if ( false !== stripos( $option, '*' ) ) {

						$defaultSelect = $parts[ 0 ];

					}

				} else {

					$options[ $option ] = ucwords( $option );

					if ( false !== stripos( $option, '*' ) ) {

						$defaultSelect = $option;

					}
				}
			}

		} else {

			if ( is_array( $convert_options ) ) {

				// Check if maybe array is from js serialize
				$options = $this->unserialize( $convert_options );

				if( $add_slashes ) {
					$convert_options = $this->add_slashes( $convert_options );
				}

				$convert_options = array_map( array( $this, 'add_separator' ), $convert_options, array_keys( $convert_options ) );
				$options = implode( ',', $convert_options );

			}

		}

		return $options;

	}

	/**
	 * Add slashes for commas in array key and value
	 *
	 *
	 * @since 1.2.6
	 *
	 * @param $options
	 *
	 * @return array
	 */
	function add_slashes( $options ){
		$slashed_options = array();
		foreach( $options as $key => $value ){
			$slashed_options[ str_replace( ',', '\\,', $key ) ] = str_replace( ',', '\\,', $value );
		}

		return $slashed_options;
	}

	/**
	 * Add Separator ( || ) Between Two Values
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $v
	 * @param $k
	 *
	 * @return string
	 */
	function add_separator( $v, $k ) {
		$value =  $k . '||' . $v;
		if( $this->add_quotes ){
			$value = "\"${value}\"";
		}

		return $value;
	}

	/**
	 * Check for other fields that require option value
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $type
	 *
	 * @return bool|mixed
	 */
	function other_meta_key_check( $type ){

		// Add additional meta meta for specific options values
		$additional_meta_key = array_search( $type, $this->additional_options_meta_key );
		if( $additional_meta_key ) return $additional_meta_key;

		return false;
	}

	/**
	 * Set Additional Fields with Options values
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function additional_options( $fields ){

		if( empty( $fields ) ) return $fields;

		foreach( $fields as $field => $field_config ){

			$additional_option = $this->other_meta_key_check( $field_config[ 'type' ] );
			if( $additional_option && ! empty( $field_config[ $additional_option ] ) ){
				$fields[ $field ][ 'options' ] = $field_config[ $additional_option ];
			}

		}

		return $fields;

	}

	/**
	 * Maybe Return Option Label
	 *
	 * This method will return the label from an option as long as it's enabled in the settings,
	 * the field type matches one of the supported types, and there isn't any $arg config missing.
	 *
	 * @since 1.5.0
	 *
	 * @param string|array $value String value to use to get label (will be used as array key)
	 * @param array        $args  Array of field configuration values (options, meta_key, and type keys are required)
	 *
	 * @return string             Translated option label, or original passed value if error
	 */
	public static function maybe_get_label( $value, $args ){

		/**
		 * Exit if $value is empty, or $args isn't array or is empty
		 */
		if ( empty( $value ) || ! is_array( $args ) || empty( $args ) ) {
			return $value;
		}

		$output_label_in_args = ( array_key_exists( 'label_over_value', $args ) && ! empty( $args['label_over_value'] ) );
		$output_label = get_option( 'jmfe_fields_options_output_label', FALSE );

		/**
		 * Exit if not enabled in settings, or in specific field configuration.
		 */
		if ( empty( $output_label ) && empty( $output_label_in_args ) ) {
			return $value;
		}

		$option_label_types = apply_filters( 'field_editor_get_custom_field_option_label_types', array( 'select', 'multiselect', 'radio', 'checklist' ), $value, $args );

		/**
		 * Exit if this field type is not a supported one, does not have options, or options key is empty
		 */
		if ( ! array_key_exists( 'type', $args ) || ! array_key_exists( 'options', $args ) || empty( $args[ 'options' ] ) || ! in_array( $args[ 'type' ], $option_label_types ) ) {
			return $value;
		}

		/**
		 * Loop through each value if it's an array of values
		 */
		if( is_array( $value ) ){

			foreach( (array) $value as $sub_val_key => $sub_val_val ){
				$value[ $sub_val_key ] = self::maybe_get_label( $sub_val_val, $args );
			}

			return $value;
		}

		/**
		 * Return passed value if we can't find a label in the options array for the value/key
		 */
		if ( ! array_key_exists( $value, $args['options'] ) || empty( $args[ 'options' ][$value] ) ) {

			// Try removing ~ and * from option keys since we couldn't find a matching key
			$args = self::clean_option_keys( $args );

			// Test again, and return the passed value if we still can't find a key
			/** @noinspection NotOptimalIfConditionsInspection */
			if( ! array_key_exists( $value, $args[ 'options' ] ) || empty( $args[ 'options' ][ $value ] ) ){
				return $value;
			}

		}

		/**
		 * At this point we do have a matching ( value => label ) in our options
		 */
		$translated_value = WP_Job_Manager_Field_Editor_Translations::translate( $args['options'][ $value ], $args['meta_key'], "options {$value}", $args['field_group'] );

		return $translated_value;
	}

	/**
	 * Clean config from option keys
	 *
	 * Option keys include the (~) or (*) to signify if they are default or disabled options,
	 * sometimes this may need to be removed if we need to check the array for specific key, etc.
	 * This method will remove any (~) or (*) from the keys and return.
	 *
	 * @since @@since
	 *
	 * @param array $config
	 * @param bool $in_options_key
	 *
	 * @return array
	 */
	public static function clean_option_keys( $config, $in_options_key = TRUE ) {

		if( ! is_array( $config ) ){
			return $config;
		}

		if ( $in_options_key && ( ! isset( $config[ 'options' ] ) || ! is_array( $config[ 'options' ] ) ) ) {
			return $config;
		}

		$the_options = &$in_options_key ? $config['options'] : $config;

		if( $in_options_key ){
			$the_options = &$config['options'];
		} else {
			$the_options = &$config;
		}

		$tmp_options = array();

		foreach( $the_options as $value => $label ) {

			$value = str_replace( '*', '', $value );
			$value = str_replace( '~', '', $value );

			$tmp_options[ $value ] = $label;
		}

		$the_options = $tmp_options;

		return $config;
	}
}