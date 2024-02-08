<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_ShortCodes
 *
 * @since 1.1.9
 *
 */
class WP_Job_Manager_Field_Editor_ShortCodes {

	private $current_each_field = array();

	function __construct() {

		add_shortcode( 'job_field', array( $this, 'shortcode_output' ) );
		add_shortcode( 'company_field', array( $this, 'shortcode_output' ) );
		add_shortcode( 'custom_field', array( $this, 'shortcode_output' ) );
		add_shortcode( 'resume_field', array( $this, 'shortcode_output' ) );
		add_shortcode( 'each_custom_field', array( $this, 'each_handler' ) );
		add_shortcode( 'each_custom_field_value', array( $this, 'each_value_handler' ) );

		add_shortcode( 'if_custom_field', array( $this, 'if_shortcode_handler' ) );
		add_shortcode( 'if_job_field', array( $this, 'if_shortcode_handler' ) );
		add_shortcode( 'if_resume_field', array( $this, 'if_shortcode_handler' ) );


		// Execute shortcodes from Text Widget (4.8+ added WP Editor in text widget)
		if( ! has_action( 'widget_text', 'do_shortcode' ) ){
			add_action( 'widget_text', 'do_shortcode' );
		}
	}

	/**
	 * [each_custom_field_value key="xxx"]Label:[value][/each_custom_field_value]
	 *
	 *
	 * @param        $atts
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string|string[]
	 * @since 1.10.0
	 *
	 */
	public function each_value_handler( $atts, $content = '', $tag = 'each_value_handler' ) {
		if( empty( $this->current_each_field ) || ! isset( $atts['key'] ) || empty( $atts['key'] ) || empty( $content ) ){
			return '';
		}

		$default_atts = array(
			'key' => ''
		);

		$args = is_array( $atts ) && ! empty( $atts ) ? array_merge( $default_atts, $atts ) : $default_atts;

		// Pass through shortcode filter for attributes
		$args = apply_filters( "shortcode_atts_{$tag}", $args, $default_atts, $atts, $tag );

		$sub_key = $args['key'];

		if( ! isset( $this->current_each_field[ $sub_key ] ) || empty( $this->current_each_field[ $sub_key ] ) ){
			return '';
		}

		return str_replace( '[value]', $this->current_each_field[ $sub_key ], $content );
	}

	/**
	 * [each_custom_field][value][/each_custom_field] Handler
	 *
	 *
	 * @param array  $args
	 * @param string $content
	 * @param string $tag
	 *
	 * @return null|string
	 * @since 1.10.0
	 *
	 */
	public function each_handler( $atts, $content = '', $tag = 'each_custom_field' ) {

		$default_atts = array(
			'key'            => '',
			'field'          => '',
			'listing_id'     => $this->get_listing_ID( $atts ),
		);

		$output = '';

		try {

			$args = is_array( $atts ) && ! empty( $atts ) ? array_merge( $default_atts, $atts ) : $default_atts;

			// Pass through shortcode filter for attributes
			$args = apply_filters( "shortcode_atts_{$tag}", $args, $default_atts, $atts, $tag );

			if ( empty( $args['key'] ) && empty( $args['field'] ) ) {
				throw new Exception( __( 'Meta Key was not specified!', 'wp-job-manager-field-editor' ) );
			}

			if ( empty( $args['listing_id'] ) ) {
				throw new Exception( __( 'Unable to determine correct job/resume/post ID!', 'wp-job-manager-field-editor' ) );
			}

			if ( ! empty( $args['key'] ) ) {
				$meta_key = $args['key'];
			}

			if ( ! empty( $args['field'] ) ) {
				$meta_key = $args['field'];
			}

			if( empty( $content ) ){
				throw new Exception( __( 'You must have content inside the each_custom_field shortcode!', 'wp-job-manager-field-editor' ) );
			}

			$field_values = get_custom_field( $meta_key, $args['listing_id'], $args );

			if( empty( $field_values ) || ! is_array( $field_values ) ){
				return '';
			}

			foreach( (array) $field_values as $field_value ){

				if( is_array( $field_value ) ){

					$single_content = $content;

					// sub field values are replaced using the [each_custom_field_value] shortcode
					// as using the meta key as a shortcode could cause conflicts
					$this->current_each_field = $field_value;
					$single_content = do_shortcode( $single_content );
					$output .= $single_content;
				} else {

					$output .= str_replace( '[value]', $field_value, $content );

				}

			}

		} catch ( Exception $error ) {
			error_log( 'Shortcode output error: ' . $error->getMessage() );
		}

		return do_shortcode( $output );
	}

	/**
	 * Output for Shortcode
	 *
	 * @since 1.1.9
	 *
	 * @param $atts
	 *
	 * @return mixed|null
	 */
	function shortcode_output( $atts, $content = '', $tag = 'jmfe' ) {
		global $job_preview, $resume_preview, $company_preview;

		$listing_id = absint( get_the_ID() );

		$default_atts = array(
			'key'                  => '',
			'field'                => '',
			'listing_id'           => $listing_id,
			'if_equals'            => '',
			'if_contains'          => '',
			'has_value'            => '',
			'has_value_containing' => '',
			'field_group'          => '',
			'case_sensitive'       => FALSE
		);

		// If job preview step, try and pull ID from the submit job class object
		if ( ! empty( $_POST['submit_job'] ) && ! empty( $job_preview ) && class_exists( 'WP_Job_Manager_Form_Submit_Job' ) ) {

			$wpjmsj = WP_Job_Manager_Form_Submit_Job::instance();
			$job_id = $wpjmsj->get_job_id();

			if ( ! empty( $job_id ) ) {
				$default_atts['listing_id'] = $job_id;
			}
		// If resume preview step, try and pull ID from the submit job class object
		} elseif ( ! empty( $_POST['submit_resume'] ) && ! empty( $resume_preview ) && class_exists( 'WP_Resume_Manager_Form_Submit_Resume' ) ) {

			$wprmsr    = WP_Resume_Manager_Form_Submit_Resume::instance();
			$resume_id = $wprmsr->get_resume_id();

			if ( ! empty( $resume_id ) ) {
				$default_atts['listing_id'] = $resume_id;
			}
		// If company preview step, try and pull ID from the submit job class object
		} elseif ( ! empty( $_POST['submit_company'] ) && ! empty( $company_preview ) && class_exists( 'WP_Company_Manager_Form_Submit_Company' ) ) {

			$wpcmsr    = WP_Company_Manager_Form_Submit_Company::instance();
			$company_id = $wpcmsr->get_company_id();

			if ( ! empty( $company_id ) ) {
				$default_atts['listing_id'] = $company_id;
			}
		} elseif ( ! empty( $_POST['submit_job'] ) && ! empty( $_COOKIE['wp-job-manager-submitting-job-id'] ) && ! empty( $_COOKIE['wp-job-manager-submitting-job-key'] ) ) {

			$cookie_id = absint( $_COOKIE['wp-job-manager-submitting-job-id'] );

			if ( get_post_meta( $cookie_id, '_submitting_key', TRUE ) === $_COOKIE['wp-job-manager-submitting-job-key'] ) {
				// Prefer the cookie set ID over the loop ID as long as it's a guest posting, or author matches current user ID
				$default_atts['listing_id'] = absint( $_COOKIE['wp-job-manager-submitting-job-id'] );
			}

		// No value set for listing_id, let's try and use query object to get ID
		} elseif( empty( $default_atts['listing_id'] ) ) {
			// Loop ID take priority over query object ID
			$qo = get_queried_object();

			if ( is_object( $qo ) && isset( $qo->ID ) ) {

				$post_types = array( 'job_listing', 'resume' );
				$post_types[] = WP_Job_Manager_Field_Editor_Integration_Company::get_post_type();

				$shortcode_post_types = apply_filters( 'job_manager_field_editor_shortcode_output_post_types', $post_types );

				// If queried object post type is supported post type, set listing_id to query object ID
				if ( in_array( get_post_type( $qo->ID, $shortcode_post_types ) ) ) {
					$default_atts['listing_id'] = $qo->ID;
				}

			}

		}

		// Check if post ID was passed as post_id, listing_id, or just id, and override current set value
		if ( isset( $atts['post_id'] ) && ! empty( $atts['post_id'] ) ) {
			$atts['listing_id'] = $atts['post_id'];
		}
		if ( isset( $atts['listing_id'] ) && ! empty( $atts['listing_id'] ) ) {
			$atts['listing_id'] = $atts['listing_id'];
		}
		if ( isset( $atts['id'] ) && ! empty( $atts['id'] ) ) {
			$atts['listing_id'] = $atts['id'];
		}

		try {

			$args = is_array( $atts ) && ! empty( $atts ) ? array_merge( $default_atts, $atts ) : $default_atts;

			// Pass through shortcode filter for attributes
			$args = apply_filters( "shortcode_atts_{$tag}", $args, $default_atts, $atts, $tag );

			// Replace listing_id with resume_id if passed in arguments
			if ( array_key_exists( 'resume_id', $args ) && ! empty( $args['resume_id'] ) ) {
				$args['listing_id'] = $args['resume_id'];
			}

			// Replace listing_id with job_id if passed in arguments
			if( array_key_exists( 'job_id', $args ) && ! empty( $args['job_id'] ) ){
				$args['listing_id'] = $args['job_id'];
			}

			if ( empty( $args['key'] ) && empty( $args['field'] ) ) {
				throw new Exception( __( 'Meta Key was not specified!', 'wp-job-manager-field-editor' ) );
			}

			if ( empty( $args['listing_id'] ) ) {
				throw new Exception( __( 'Unable to determine correct job/resume/post ID!', 'wp-job-manager-field-editor' ) );
			}

			if ( $args['key'] ) {
				$meta_key = $args['key'];
			}
			if ( $args['field'] ) {
				$meta_key = $args['field'];
			}

			/**
			 * When content is not empty, means it's being used as a sort-of "if" statement for a field, to only output what
			 * is inside the shortcode content area if the field has a value.
			 */
			if( ! empty( $content ) ){

				$content_if = $content_else = '';
				$conditional_check = FALSE;
				$field_value = get_custom_field( $meta_key, $args['listing_id'], $args );

				// Separate out content if there is an [else] inside
				if ( strpos( $content, '[else]' ) !== FALSE ) {
					list( $content_if, $content_else ) = explode( '[else]', $content, 2 );
				} else {
					$content_if = $content;
				}

				// Assume logic check is true if no logic args are passed for checking against, and the field has a value
				if( ! empty( $field_value ) && '' === $args['if_equals'] && '' === $args['if_contains'] && '' === $args['has_value'] && '' === $args['has_value_containing'] ){

					$conditional_check = true;

				} else {

					if ( is_array( $field_value ) ) {

						foreach ( $field_value as $fval ) {

							// Match found, either exact or containing
							if ( $this->value_conditional( $fval, $args, true ) ) {
								$conditional_check = true;
								// Break from foreach loop after finding match
								break;
							}

						}

					} else {
						$conditional_check = $this->value_conditional( $field_value, $args );
					}

				}

				// Check for "NOT" to negate (reverse) the statement
				$att_values = array_values( $args );
				$negate     = in_array( 'NOT', $att_values ) || in_array( 'not', $att_values );

				if( $negate ){
					$conditional_check = ! $conditional_check;
				}

				// Set output content equal to if or else content, based on the conditional check
				$output_content = $conditional_check ? $content_if : $content_else;

				// No value means we output nothing
				if ( empty( $output_content ) ) {
					return '';
				}

				// Return and run do_shortcode() for any nested shortcodes
				return do_shortcode( $output_content );
			}

			ob_start();
			the_custom_field( $meta_key, $args['listing_id'], $args );
			$shortcode_output = ob_get_contents();
			ob_end_clean();

			return $shortcode_output;

		} catch ( Exception $error ) {

			error_log( 'Shortcode output error: ' . $error->getMessage() );

		}

		// Return empty string as last resort if nothing else worked
		return '';
	}

	/**
	 * Get the listing ID
	 *
	 * This method first attempts to get the listing ID from job preview step (if available), then tries resume preview step, then
	 * uses core WordPress get_the_ID(), and as last resort attempts to pull ID from queried object
	 *
	 *
	 * @since 1.8.1
	 *
	 * @param array $atts
	 *
	 * @return bool|int|mixed
	 */
	public function get_listing_ID( $atts = array() ){

		// First check if listing ID was passed in attributes
		if( array_key_exists( 'listing_id', $atts ) ){
			return $atts['listing_id'];
		}

		$post_types   = array( 'job_listing', 'resume' );
		$post_types[] = WP_Job_Manager_Field_Editor_Integration_Company::get_post_type();

		$shortcode_post_types = apply_filters( 'job_manager_field_editor_shortcode_output_post_types', $post_types, $this );

		// If not, check if user viewing preview step, and pull from class or cookie
		if( ! $listing_id = $this->get_job_preview_ID() ){

			// If that doesn't work, check the same as above, but for resumes
			if( ! $listing_id = $this->get_resume_preview_ID() ){

				// If that doesn't work, check the same as above, but for company
				if ( ! $listing_id = $this->get_company_preview_ID() ) {

					// Otherwise try using standard get_the_ID() -- which sometimes does not work correctly if other query loops on page/preview
					$listing_id = absint( get_the_ID() );

					// Last resort, can't get the ID from get_the_ID() -- or is not a supported post type
					if ( empty( $listing_id ) || ! in_array( get_post_type( $listing_id ), $shortcode_post_types ) ) {

						// Then use query object
						$listing_id = $this->get_queried_object_ID( $shortcode_post_types );

					}

				}

			}

		}

		return $listing_id;
	}

	/**
	 * Attempt to get Company ID from Class Object
	 *
	 *
	 * @return bool|int
	 * @since 1.10.0
	 *
	 */
	public function get_company_preview_ID() {

		global $company_preview;

		$listing_id = false;

		if ( ! empty( $_POST['submit_resume'] ) && ! empty( $company_preview ) && class_exists( 'WP_Company_Manager_Form_Submit_Company' ) ) {

			$wpcmsr    = WP_Company_Manager_Form_Submit_Company::instance();
			$company_id = $wpcmsr->get_company_id();

			if ( ! empty( $company_id ) ) {
				$listing_id = $company_id;
			}

		}

		return $listing_id;
	}

	/**
	 * Attempt to get Resume ID from Class Object
	 *
	 *
	 * @since 1.8.1
	 *
	 * @return bool|int
	 */
	public function get_resume_preview_ID(){

		global $resume_preview;

		$listing_id = false;

		if ( ! empty( $_POST['submit_resume'] ) && ! empty( $resume_preview ) && class_exists( 'WP_Resume_Manager_Form_Submit_Resume' ) ) {

			$wprmsr    = WP_Resume_Manager_Form_Submit_Resume::instance();
			$resume_id = $wprmsr->get_resume_id();

			if ( ! empty( $resume_id ) ) {
				$listing_id = $resume_id;
			}

		}

		return $listing_id;
	}

	/**
	 * Attempt to get Job ID from Class Object or Cookie
	 *
	 *
	 * @since 1.8.1
	 *
	 * @return bool|int
	 */
	public function get_job_preview_ID(){

		global $job_preview;

		$listing_id = false;

		// If job preview step, try and pull ID from the submit job class object
		if ( ! empty( $_POST['submit_job'] ) ) {

			if ( ! empty( $job_preview ) && class_exists( 'WP_Job_Manager_Form_Submit_Job' ) ) {

				$wpjmsj = WP_Job_Manager_Form_Submit_Job::instance();
				$job_id = $wpjmsj->get_job_id();

				if ( ! empty( $job_id ) ) {
					$listing_id = $job_id;
				}

			} elseif ( ! empty( $_COOKIE['wp-job-manager-submitting-job-id'] ) && ! empty( $_COOKIE['wp-job-manager-submitting-job-key'] ) ) {

				$cookie_id = absint( $_COOKIE['wp-job-manager-submitting-job-id'] );

				if ( get_post_meta( $cookie_id, '_submitting_key', true ) === $_COOKIE['wp-job-manager-submitting-job-key'] ) {
					// Prefer the cookie set ID over the loop ID as long as it's a guest posting, or author matches current user ID
					$listing_id = absint( $_COOKIE['wp-job-manager-submitting-job-id'] );
				}

			}

		}

		return $listing_id;
	}

	/**
	 * Get listing ID from Queried Object (last resort)
	 *
	 *
	 * @since 1.8.1
	 *
	 * @param array $shortcode_post_types
	 *
	 * @return bool
	 */
	public function get_queried_object_ID( $shortcode_post_types = array() ){

		$listing_id = false;

		// Loop ID take priority over query object ID
		$qo = get_queried_object();

		if ( is_object( $qo ) && isset( $qo->ID ) ) {

			// If queried object post type is supported post type, set listing_id to query object ID
			if ( in_array( get_post_type( $qo->ID ), $shortcode_post_types ) ) {
				$listing_id = $qo->ID;
			}

		}

		return $listing_id;
	}

	/**
	 * [if_custom_field] handler
	 *
	 *
	 * @since 1.8.1
	 *
	 * @param        $atts
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public function if_shortcode_handler( $atts, $content = '', $tag = 'jmfe' ) {

		$default_atts = array(
			'key'            => '',
			'field'          => '',
			'listing_id'     => $this->get_listing_ID( $atts ),
			'equals'         => '',
			'contains'       => '',
			'case_sensitive' => false,
			'field_group'    => ''
		);

		try {

			$args = is_array( $atts ) && ! empty( $atts ) ? array_merge( $default_atts, $atts ) : $default_atts;

			// Pass through shortcode filter for attributes
			$args = apply_filters( "shortcode_atts_{$tag}", $args, $default_atts, $atts, $tag );

			if ( empty( $args['key'] ) && empty( $args['field'] ) ) {
				throw new Exception( __( 'Meta Key was not specified!', 'wp-job-manager-field-editor' ) );
			}

			if ( empty( $args['listing_id'] ) ) {
				throw new Exception( __( 'Unable to determine correct job/resume/post ID!', 'wp-job-manager-field-editor' ) );
			}

			if ( ! empty( $args['key'] ) ) $meta_key = $args['key'];
			if ( ! empty( $args['field'] ) ) $meta_key = $args['field'];

			/**
			 * When content is not empty, means it's being used as a sort-of "if" statement for a field, to only output what
			 * is inside the shortcode content area if the field has a value.
			 */
			if ( ! empty( $content ) ) {

				$content_if        = $content_else = '';
				$field_value       = get_custom_field( $meta_key, $args['listing_id'], $args );

				// Separate out content if there is an [else] inside
				if ( strpos( $content, '[else]' ) !== false ) {
					list( $content_if, $content_else ) = explode( '[else]', $content, 2 );
				} else {
					$content_if = $content;
				}

				// If nothing passed for equals AND contains, assume any value as true
				if( '' === $args['equals'] && '' === $args['contains'] ){
					$conditional_check = ! empty( $field_value );
				} else {
					$conditional_check = $this->check_conditional( $field_value, $args );
				}

				// Check for "NOT" to negate (reverse) the statement
				$att_values = array_values( $args );
				$negate     = in_array( 'NOT', $att_values ) || in_array( 'not', $att_values );

				if ( $negate ) {
					$conditional_check = ! $conditional_check;
				}

				// Set output content equal to if or else content, based on the conditional check
				$output_content = $conditional_check ? $content_if : $content_else;

				// No value means we output nothing
				if ( empty( $output_content ) ) {
					return '';
				}

				// Return and run do_shortcode() for any nested shortcodes
				return do_shortcode( $output_content );
			}

		} catch ( Exception $error ) {

			error_log( 'Shortcode output error: ' . $error->getMessage() );
		}

		// Return empty string as last resort if nothing else worked
		return '';
	}

	/**
	 * Check conditional output based on value and arguments
	 *
	 *
	 * @since 1.8.1
	 *
	 * @param $value
	 * @param $args
	 *
	 * @return bool
	 */
	public function check_conditional( $value, $args ){

		$check_conditional = false;

		if ( strlen( $args['contains'] ) > 0 ) {

			$check_conditional = $this->does_contain( $value, $args );

		} elseif ( strlen( $args['equals'] ) > 0 ) {

			$check_conditional = $this->does_equal( $value, $args );

		}

		return $check_conditional;
	}

	/**
	 * Check if passed value does contain value from $args['contains']
	 *
	 *
	 * @since 1.8.1
	 *
	 * @param string|array $value   String or array value to check, if array passed will loop through each looking for a match
	 * @param array        $args    Arguments from shortcode method
	 *
	 * @return bool
	 */
	public function does_contain( $value, $args ){

		$conditional_success = false;

		// Handle passed arrays
		if( is_array( $value ) ){

			foreach ( (array) $value as $sub_val ) {

				if ( $this->does_contain( $sub_val, $args ) ) {
					// Match found, either exact or containing
					$conditional_success = true;
					// Break from foreach loop after finding match
					break;
				}

			}

			return $conditional_success;
		}

		$case_sensitive      = ! empty( $args['case_sensitive'] );
		$check_value         = ! $case_sensitive ? strtolower( $value ) : $value;

		$if_contains = ! $case_sensitive ? strtolower( $args[ 'contains' ] ) : $args[ 'contains' ];

		if ( strpos( $check_value, $if_contains ) !== false ) {
			$conditional_success = true;
		}

		return $conditional_success;
	}

	/**
	 * Check if passed value equals exactly value from `equals` key in $args
	 *
	 * When an array is passed for the value, this method will ONLY return true when there is only one item in the array,
	 * and that value equals the value in $args['equals'], otherwise it will always return false.  The meaning is that when
	 * checking arrays with "equals", it sorta mean "if this field ONLY EQUALS"
	 *
	 * @since 1.8.1
	 *
	 * @param array|string $value   String or array to check against. If array passed, will only return true when length is 1 and that item equals $args['equals'] value
	 * @param array        $args    Passed args from shortcode method
	 *
	 * @return bool
	 */
	public function does_equal( $value, $args ){

		$conditional_success = false;

		if( is_array( $value ) ){

			// removes all NULL, FALSE and Empty Strings but leaves 0 (zero) values
			$clean_array = array_filter( $value, 'strlen' );
			if( empty( $clean_array ) || count( $clean_array ) > 1 ){
				// If array has more than one entry after cleaning, it could never "EQUAL" anything, as there are multiple values (that should be done with CONTAIN)
				return false;
			}

			// Do recursive call on first array item, and return result
			return $this->does_equal( $clean_array[0], $args );
		}

		$case_sensitive      = ! empty( $args['case_sensitive'] );
		$check_value         = ! $case_sensitive ? strtolower( $value ) : $value;

		$if_equals = ! $case_sensitive ? strtolower( $args[ 'equals' ] ) : $args[ 'equals' ];

		if ( $check_value == $if_equals ) {
			$conditional_success = true;
		}

		return $conditional_success;
	}

	/**
	 * Check Value Conditional Statements
	 *
	 * This method handles checking for `has_value` or `has_value_containing` for array fields,
	 * or `if_equals` and `if_contains` for non array fields, and returns boolean based on the
	 * check.   Does NOT handle negate statement, that should be handled in main shortcode handler.
	 *
	 *
	 * @since 1.7.0
	 * @deprecated 1.8.1
	 *
	 * @param      $value
	 * @param      $args
	 * @param bool $array
	 *
	 * @return bool
	 */
	public function value_conditional( $value, $args, $array = false ){

		$conditional_success = FALSE;
		$equals_key = $array ? 'has_value' : 'if_equals';
		$contains_key = $array ? 'has_value_containing' : 'if_contains';
		$case_sensitive = ! empty( $args['case_sensitive'] );

		$check_value = ! $case_sensitive ? strtolower( $value ) : $value;

		// if_equals or has_value
		if ( ! empty( $args[ $equals_key ] ) ) {

			$if_equals = ! $case_sensitive ? strtolower( $args[ $equals_key ] ) : $args[ $equals_key ];

			if ( $check_value == $if_equals ) {
				$conditional_success = TRUE;
			}
		// if_contains or has_value_containing
		} elseif ( ! empty( $args[ $contains_key ] ) ) {

			$if_contains = ! $case_sensitive ? strtolower( $args[ $contains_key ] ) : $args[ $contains_key ];

			if ( strpos( $check_value, $if_contains ) !== FALSE ) {
				$conditional_success = TRUE;
			}

		}

		return $conditional_success;
	}
}

new WP_Job_Manager_Field_Editor_ShortCodes();