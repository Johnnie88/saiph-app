<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/mobile-detect.php';

/**
 * Class WP_Job_Manager_Field_Editor_Conditionals
 *
 * @since 1.7.10
 *
 */
class WP_Job_Manager_Field_Editor_Conditionals {

	/**
	 * @var \WP_Job_Manager_Field_Editor
	 */
	private $core;

	/**
	 * @var string Slug representing type (job/resume)
	 */
	public $slug;
	/**
	 * @var array|boolean Logic configuration
	 */
	public $logic = null;

	/**
	 * @var array|boolean Listing fields
	 */
	public $fields;

	/**
	 * @var array
	 */
	public $js_config = array();

	/**
	 * @var \WP_Job_Manager_Field_Editor_Conditionals_Compare
	 */
	public $compare;

	/**
	 * @var null|boolean Whether or not current device is Mobile Device or not
	 */
	public $mobile_device = null;

	/**
	 * @var array Fields that were removed from the fields array because they were hidden by conditional logic
	 */
	public $removed_logic_fields = array();

	/**
	 * WP_Job_Manager_Field_Editor_Conditionals constructor.
	 *
	 * @param $core \WP_Job_Manager_Field_Editor
	 */
	public function __construct( $core ) {

		$this->core = $core;
		$this->slug = $this->get_slug();
		$this->hooks();
		$this->compare = new WP_Job_Manager_Field_Editor_Conditionals_Compare( $this );

		add_action( 'wp', array( $this, 'add_fields_filter' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_filter( 'job_manager_get_posted_term_select_field', array( $this, 'get_posted_term_select_handler' ), 9999 );
	}

	/**
	 * Return Term Select Get Value Handler
	 *
	 *
	 * @param $nohandler
	 *
	 * @return array
	 * @since 1.8.5
	 *
	 */
	public function get_posted_term_select_handler( $nohandler ) {

		return array( $this, 'get_posted_term_select' );
	}

	/**
	 * Get POSTed Term Select Values
	 *
	 * This method is used to return values for term-select field types, as when using dynamic child dropdowns, single
	 * selects are modified to allow multiple selections (by adding [] to name), and as such, we have to check if the
	 * POSTed value is an array, and sanitize/return correctly to prevent "METKEY is invalid" due to core sanitizing
	 * the field incorrectly (since it thinks it's a single select)
	 *
	 *
	 * @param $key
	 * @param $field
	 *
	 * @return array|int|string
	 * @since 1.8.5
	 *
	 */
	public function get_posted_term_select( $key, $field ) {

		// Empty array by default, which is OK to use for term-select, as in validation in core, after obtaining a value,
		// it actually converts it to an array anyways.
		$value = array();

		if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) {
			// Loop through all terms and remove any empty values in array
			$selected_terms = array();
			foreach ( (array) $_POST[ $key ] as $term_id ) {
				if ( ! empty( $term_id ) ) {
					$selected_terms[] = $term_id;
				}
			}

			$value = ! empty( $selected_terms ) ? array_map( 'absint', $selected_terms ) : array();
		} else {
			$value = ! empty( $_POST[ $key ] ) && $_POST[ $key ] > 0 ? absint( $_POST[ $key ] ) : '';
		}

		return $value;
	}

	/**
	 *  Form Output
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function form() {

		$this->output_dynamic_taxonomies();

		if ( ! $logic = $this->get_logic() ) {
			return;
		}

		$this->output_hidden_fields();

		$this->localize( $logic, $this->get_fields() );
		wp_enqueue_script( 'jmfe-conditionals' );

		if ( get_option( 'jmfe_logic_show_use_velocity', false ) || get_option( 'jmfe_logic_hide_use_velocity', false ) ) {
			wp_enqueue_script( 'jmfe-vendor-velocity' );
		}
	}

	/**
	 * Build and Output JS for Dynamic Taxonomies
	 *
	 * This method will generate the required JavaScript for any configured dynamic taxonomies, and output as JSON
	 * to be used on frontend of a site.
	 *
	 *
	 * @since 1.8.5
	 *
	 */
	public function output_dynamic_taxonomies() {

		global $wp_version;

		$meta_keys = $this->get_fields();

		if ( empty( $meta_keys ) ) {
			return;
		}

		$meta_keys        = wp_list_filter( $meta_keys, array( 'tax_show_child' => '1' ) );
		$tax_fields       = array();
		$listing_id       = $this->get_listing_id();
		$optional_default = WP_Job_Manager_Field_Editor_Job_Fields::get_optional_string();

		if ( ! empty( $meta_keys ) ) {

			$tax_field_types = array( 'term-multiselect', 'term-select' );

			foreach ( (array) $meta_keys as $meta_key => $config ) {

				if ( ( isset( $config['type'] ) && in_array( $config['type'], $tax_field_types ) ) && array_key_exists( 'taxonomy', $config ) && ! empty( $config['taxonomy'] ) ) {

					// First make sure taxonomy is valid, and has children
					if ( ! taxonomy_exists( $config['taxonomy'] ) || ! is_taxonomy_hierarchical( $config['taxonomy'] ) ) {
						continue;
					}

					// False by default (default for WPJM)
					$show_required = false;

					$required = array_key_exists( 'required', $config ) && ! empty( $config['required'] ) ? true : false;

					$required_label = wp_kses_post( apply_filters( 'submit_job_form_required_label', true ? '' : ' <small>' . $optional_default . '</small>', $config ) );
					$optional_label = wp_kses_post( apply_filters( 'submit_job_form_required_label', false ? '' : ' <small>' . $optional_default . '</small>', $config ) );

					if ( $required && ! empty( $required_label ) ) {
						$show_required = true;
					}

					$h = $this->get_taxonomy_with_children( $config['taxonomy'] );

					$existing_value = array();

					/**
					 * We have to sort existing values based on parent, which was not added in wp_get_object_terms until WP 4.7.0
					 *
					 * So if version of WP is older than 4.7.0, we have to manually pull terms using get_terms, and then
					 * sort the array accordingly.
					 */
					$version = ! $wp_version || empty( $wp_version ) ? get_bloginfo( 'version' ) : $wp_version;
					$old_wp  = version_compare( $version, '4.7.0', '<' );

					if ( ! empty( $listing_id ) ) {
						// WordPress 4.7.0+ works with 'parent', older versions have to use compatibility sorting below
						$existing_orderby = $old_wp ? 'term_id' : 'parent';
						$existing_value   = wp_get_object_terms( $listing_id, $config['taxonomy'], array( 'fields' => 'ids', 'orderby' => $existing_orderby ) );
					}

					// Auto populate from GET if no existing values are set
					if ( empty( $existing_value ) && array_key_exists( 'populate_from_get', $config ) && ! empty( $config['populate_from_get'] ) ) {
						$existing_value = job_manager_field_editor_get_autopopulate_get_value( $meta_key, $config['type'], $config );
						if ( ! empty( $existing_value ) ) {
							$existing_value = array_map( 'absint', $existing_value );
						}
					}

					// Sort terms for old WordPress versions (older than 4.7.0)
					if ( ! empty( $existing_value ) && $old_wp ) {

						$term_order_args = array(
							'hide_empty' => false,
							'orderby'    => 'parent',
							'fields'     => 'ids'
						);

						$term_ids_order = get_terms( $config['taxonomy'], $term_order_args );

						// Return terms_id_order with only the matching values in existing_value
						$existing_value = array_intersect( $term_ids_order, $existing_value );
						// Then reindex the array (since it will still have term_ids_order keys index) -- just in case
						$existing_value = array_values( $existing_value );
					}

					$tax_fields[ $meta_key ] = array(
						'terms'          => $h,
						'exclude'        => array_key_exists( 'tax_exclude_terms', $config ) ? explode( ',', $config['tax_exclude_terms'] ) : array(),
						'type'           => $config['type'],
						'required'       => $required,
						'required_label' => $required_label,
						'optional_label' => $optional_label,
						'show_required'  => $show_required,
						'max_selections' => array_key_exists( 'max_selected', $config ) ? $config['max_selected'] : false,
						'existing_value' => $existing_value
					);

				}

			}

		}

		$tax_fields = apply_filters( 'field_editor_conditionals_get_dynamic_tax_fields', $tax_fields, $meta_keys, $this );

		$i18n = array(
			'placeholder' => __( 'Please select from the list below...', 'wp-job-manager-field-editor' ),
		);

		wp_localize_script( 'jmfe-dynamic-tax', 'jmfe_dynamic_tax_fields', $tax_fields );
		wp_localize_script( 'jmfe-dynamic-tax', 'jmfe_dynamic_tax_i18n', $i18n );
		wp_localize_script( 'jmfe-dynamic-tax', 'jmfe_dynamic_tax_config', array( 'is_mobile' => $this->is_mobile_device(), 'chosen' => '' ) );
	}

	/**
	 * Recursively get taxonomy and its children
	 *
	 * @param string     $taxonomy
	 * @param string|int $parent Empty string to get all tax terms, 0 for only top level, or int of parent to get children terms
	 *
	 * @return array
	 */
	public function get_taxonomy_with_children( $taxonomy, $parent = '' ) {

		$defaults = array(
			'parent'     => $parent,
			'hide_empty' => false
		);

		$terms = get_terms( $taxonomy, $defaults );

		$tax_terms = array();

		// Loop through all terms (both parent and children) building array of them
		foreach ( $terms as $index => $term ) {

			$term_meta = maybe_unserialize( get_option( "taxonomy_{$term->term_id}", array() ) );

			// Call back on this fn to get array of child IDs for THIS term id
			$term->children          = get_terms( $taxonomy, array( 'parent' => $term->term_id, 'hide_empty' => false, 'fields' => 'ids' ) );
			$term->child_dropdown    = isset( $term_meta['fe_child_dropdown'] ) ? $term_meta['fe_child_dropdown'] : 'inherit';
			$term->child_max         = isset( $term_meta['fe_child_max'] ) ? $term_meta['fe_child_max'] : '';
			$term->child_required    = isset( $term_meta['fe_child_required'] ) && ! empty( $term_meta['fe_child_required'] ) ? $term_meta['fe_child_required'] : 'inherit';
			$term->child_placeholder = isset( $term_meta['fe_child_placeholder'] ) ? $term_meta['fe_child_placeholder'] : '';

			// add the term to our new array
			$tax_terms[ $term->term_id ] = $term;
		}

		// send the results back to the caller
		return $tax_terms;
	}

	/**
	 * Output hidden inputs for repeatable fields
	 *
	 *
	 * @return void
	 * @since 1.8.0
	 *
	 */
	public function output_hidden_fields() {

		$repeatables = $this->get_repeatable_fields();

		if ( empty( $repeatables ) ) {
			return;
		}

		foreach ( (array) $repeatables as $repeatable ) {
			$id = esc_attr( $repeatable ) . '-is-visible';
			// Everything is considered "visible" at first, once page is loaded then logic is applied, and will update this value (even if hidden by default)
			echo '<input type="hidden" name="' . $id . '" id="' . $id . '" value="yes" />';
		}

	}

	/**
	 * Check if meta key value exists on form submit
	 *
	 * To check the conditional logic on the frontend, fields will have values present (even if empty) in $_POST, otherwise if they are hidden,
	 * there will be no values (at least key) in POST or FILES.  This also checks for repeatable fields, which are the exception to this rule,
	 * and as such, there are hidden inputs that are added to handle these.
	 *
	 * One exception to this would be an empty taxonomy multi-select, dropdown, etc, where if nothing was selected, nothing will be in the $_POST
	 *
	 * @param $meta_key
	 * @param $config
	 *
	 * @return bool
	 * @since 1.8.0
	 *
	 */
	public function field_present_in_submit( $meta_key, $config ) {

		// Standard fields will be in $_POST under meta key as the key
		if ( array_key_exists( $meta_key, $_POST ) ) {
			return true;
		}

		// File fields will be in $_FILES not $_POST
		if ( is_array( $_FILES ) && array_key_exists( $meta_key, $_FILES ) && $config['type'] === 'file' ) {
			return true;
		}

		// Repeatable fields will have a value in $_POST under "repeated-row-METAKEY" signifying the index for each repeatable item
		// We're not really concerned with those values as we will just let core handle validation, we just check if the key exists that means
		$repeatable_fields = $this->get_repeatable_fields();

		if ( ! empty( $repeatable_fields ) && in_array( $meta_key, $repeatable_fields ) ) {

			if ( $this->repeatable_is_visible( $meta_key ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if repeatable field is visible or not
	 *
	 *
	 * @param $meta_key
	 *
	 * @return bool
	 * @since 1.8.0
	 *
	 */
	public function repeatable_is_visible( $meta_key ) {

		// Repeatable is visible as at least one entry was showing
		if ( array_key_exists( "repeated-row-{$meta_key}", $_POST ) ) {
			return true;
		}

		// If repeatable is visible, but no repeatable fields have been added/exist, we have to check for our hidden
		// field to see if the logic resulted in the field being visible or not.
		if ( array_key_exists( "{$meta_key}-is-visible", $_POST ) && $_POST["{$meta_key}-is-visible"] == 'yes' ) {
			return true;
		}

		return false;
	}

	/**
	 * Evaluate Conditional Logic
	 *
	 * This method is used to evaluate logic in the backend, based on the passed logic sections, associated with
	 * a specific meta key.  This is used to check if we need to set a field to NOT required to bypass core WPJM
	 * validation.  $this->logic_not_required_evaluate() calls this method to evaluate sections.
	 *
	 *
	 * @param $sections
	 * @param $meta_key
	 *
	 * @return mixed|void
	 * @since 1.8.5
	 *
	 */
	public function evaluate_logic( $sections, $meta_key ) {

		$section_success = false;
		$case_sensitive  = $this->compare()->case_sensitive;

		/**
		 * Multiple ROWS is AND conditional logic; multiple SECTIONS is OR conditional logic
		 *
		 * Each top level array in the 'logic' array key, is a SECTION, which uses OR logic (only one section needs to evaluate to true)
		 * Each array inside a top level array, is a ROW, which uses AND logic (all rows must evaluate true to consider section to evaluate true)
		 */

		// Loop through each OR logic section, only one needs to eval to true
		foreach ( (array) $sections as $section_id => $rows ) {

			$rows_failed = false;

			// Loop through each logic row in that section
			foreach ( (array) $rows as $row_id => $logic ) {

				$mk       = $logic['check'];
				$compare  = $logic['compare'];
				$expected = $logic['value'];

				// Custom logic evaluation handler (for backend)
				$handler = apply_filters( "field_editor_conditional_logic_evaluate_logic_{$meta_key}", false, $logic, $sections, $this );

				if ( $case_sensitive ) {
					$expected = strtolower( $expected );
				}

				if ( $handler ) {
					$row_success = call_user_func( $handler, $mk, $expected, $case_sensitive, $this );
				} elseif ( method_exists( $this->compare(), $compare ) ) {
					$row_success = call_user_func( array( $this->compare(), $compare ), $mk, $expected );
				} else {
					$row_success = false;
				}

				if ( ! $row_success ) {
					$rows_failed = true;
					break; // No need to check other rows, since this one failed
				}

			} // close each row

			// If all rows were success in last section, no need to check other ones (since rows are AND, sections are OR)
			if ( ! $rows_failed ) {
				$section_success = true;
				break;
			}

		} // close each logic section

		return apply_filters( 'field_Editor_conditional_logic_evaluate_logic_results', $section_success, $meta_key, $this );
	}

	/**
	 * Loop through all logic, only returning those that have configuration for a specific meta key
	 *
	 *
	 * @param      $meta_key
	 * @param bool $logic
	 *
	 * @return array
	 * @since 1.8.5
	 *
	 */
	public function get_logic_with_meta_key( $meta_key, $logic = false ) {

		if ( ! $logic && ! $logic = $this->get_logic() ) {
			return array();
		}

		$mklogic = array();

		foreach ( (array) $logic as $lslug => $lconf ) {

			if ( ! array_key_exists( 'fields', $lconf ) || ! in_array( $meta_key, $lconf['fields'] ) ) {
				continue;
			}

			$mklogic[ $lslug ] = $lconf;
		}

		return $mklogic;
	}

	/**
	 * Check if logic should set field as not required
	 *
	 * This method should check if, based on evaluated logic, a field should be set as not required,
	 * meaning it may be a required field, that is not showing based on logic.
	 *
	 * @param $meta_key
	 * @param $config
	 * @param $logic
	 *
	 * @return bool
	 * @since 1.7.10
	 *
	 */
	public function logic_not_required( $meta_key, $config, $logic ) {

		$not_required = false;

		$js_config      = $this->get_js_config( $logic );
		$default_hidden = $js_config['default_hidden'];

		foreach ( (array) $logic as $slug => $lcfg ) {

			if ( ! in_array( $meta_key, $lcfg['fields'] ) ) {
				continue;
			}

			$hide_by_default = is_array( $default_hidden ) && in_array( $meta_key, $default_hidden );

			// Now we need to check if any logic evaluates to true, and "shows" this field

			// Nothing will be set in POST if field is ACTUALLY hidden (whereas it would be empty string if nothing was entered) -- meaning logic resulted in hiding that field
			// Only caveat is with file uploads, which will be under $_FILES instead of $_POST
			// and Chosen Taxonomy multiselect, select, etc
			if ( ! $this->field_present_in_submit( $meta_key, $config ) ) {

				// If meta key is found in a group with an action/type that would hide the field, set required false in listing field config
				if ( ( $hide_by_default && $lcfg['type'] === 'show' ) || ( ! $hide_by_default && $lcfg['type'] === 'hide' ) ) {
					$not_required = true;
					break;
				}

			}

		}

		return $not_required;

	}

	/**
	 * Evaluate conditional logic to check if we should modify the required config on a field
	 *
	 * This method differs from $this->logic_not_required() as this method actually evaluates the logic,
	 * whereas $this->logic_not_required() only checks for values in $_POST.
	 *
	 * Initially this is specifically for doing evaluation on the backend to fix issues with taxonomies, which
	 * do not send any values (even empty ones) when a field is showing, but nothing is selected.
	 *
	 * @param $meta_key
	 * @param $config
	 * @param $logic
	 *
	 * @return mixed|void
	 * @since 1.8.5
	 *
	 */
	public function logic_not_required_evaluate( $meta_key, $config, $logic ) {

		$not_required    = false; // Everything is required by default (since only required meta key config are passed)
		$js_config       = $this->get_js_config( $logic );
		$default_hidden  = $js_config['default_hidden'];
		$hide_by_default = is_array( $default_hidden ) && in_array( $meta_key, $default_hidden );

		$mklogic = $this->get_logic_with_meta_key( $meta_key );

		foreach ( (array) $mklogic as $gslug => $gconf ) {

			if ( $gconf['status'] === 'disabled' ) {
				continue;
			}

			$sections = $gconf['logic'];
			$type     = $gconf['type'];

			$group_success = $this->evaluate_logic( $sections, $meta_key );

			if ( $group_success ) {

				if ( ! $hide_by_default && $type === 'hide' ) {
					$not_required = true;
				}

			} else {

				if ( $hide_by_default && $type === 'show' ) {
					$not_required = true;
				}

			}

			if ( $not_required ) {
				break;
			}
		}

		return apply_filters( 'field_editor_conditional_logic_not_required_evaluate', $not_required, $meta_key, $mklogic, $config, $logic, $this );
	}

	/**
	 * Remove Hidden Logic Fields on Save Custom Fields
	 *
	 * This method is called via a filter added in the set_required_false() method, when we remove a field from the array of fields
	 * because that field was hidden by conditional logic.  Because Field Editor does call a method to save custom fields we use this
	 * method to remove those same fields from the array of fields used to save custom meta values, to prevent empty values being saved
	 * to field meta that does not exist.
	 *
	 *
	 * @param $custom_enabled_fields
	 * @param $type
	 * @param $job_id
	 * @param $values
	 *
	 * @return array|mixed|void
	 * @since 1.9.0
	 *
	 */
	public function save_custom_fields_remove_logic_fields( $custom_enabled_fields, $type, $job_id, $values ) {

		if ( empty( $this->removed_logic_fields ) || ! apply_filters( 'field_editor_conditional_logic_save_custom_fields_remove_logic_fields', true, $custom_enabled_fields, $type, $job_id, $values ) ) {
			return $custom_enabled_fields;
		}

		/**
		 * Use array_dif_key to only return values in $custom_enabled_fields that are NOT in array
		 * of removed logic fields.
		 */
		$custom_enabled_fields = array_diff_key( $custom_enabled_fields, $this->removed_logic_fields );

		return apply_filters( 'field_editor_conditional_logic_save_custom_fields_remove_logic_fields_fields', $custom_enabled_fields, $this->removed_logic_fields, $type, $job_id, $values );
	}

	/**
	 * Set Required Fields False
	 *
	 *
	 * @param $listing_fields
	 *
	 * @return mixed
	 * @since 1.7.10
	 *
	 */
	public function set_required_false( $listing_fields ) {

		if ( ! $logic = $this->get_logic() ) {
			return $listing_fields;
		}

		$filter_set                 = false;
		$active_fields              = $this->get_group_fields();
		$listing_id                 = $this->get_listing_id();
		$this->removed_logic_fields = array();

		$keep_field_types = apply_filters( 'field_editor_conditional_logic_keep_field_types', array(
			'header',
			'html',
			'actionhook'
		), $listing_fields, $this );

		// Loop through groups (job, company, resume_fields)
		foreach ( (array) $listing_fields as $group => $fields ) {

			$chosen_fields = $this->get_chosen_fields( $fields );

			// Loop through meta keys
			foreach ( (array) $fields as $meta_key => $config ) {

				if ( ! in_array( $meta_key, $active_fields ) ) {
					continue;
				}

				// We want to skip specific field types from being processed
				if ( in_array( $config['type'], $keep_field_types ) ) {
					continue;
				}

				// False by default
				$remove_from_fields = false;
				$set_required_false = false;
				$is_default_field = ! array_key_exists( 'origin', $config ) || $config['origin'] !== 'custom';

				/**
				 * Check if current active logic field is not present in $_POST, meaning it's a field that was hidden by logic
				 */
				if ( ! $is_default_field && ! $this->field_present_in_submit( $meta_key, $config ) ) {

					// By default, we will remove the field from fields array if a value is not present in $_POST
					$remove_from_fields = true;

					/**
					 * But first, check if there is a listing ID associated with this field, to check if there is an
					 * existing value saved to the listing.  If there is, we need to leave the field in the
					 * array of fields, so it is updated with an empty value when core does post meta updates
					 */
					if ( $listing_id ) {

						$old_value = get_post_meta( $listing_id, "_{$meta_key}", true );

						if ( ! empty( $old_value ) ) {
							$remove_from_fields = false;
						}

					}

					// Remove the field from the array of fields passed back to core, pass FALSE to filter below to prevent removing from fields array
					if ( $remove_from_fields && apply_filters( 'field_editor_conditional_logic_set_required_false_remove_field', $meta_key, $listing_fields[ $group ], $listing_id, $config ) ) {

						unset( $listing_fields[ $group ][ $meta_key ] );

						// Add this meta key to array of fields that were removed (used in filter below)
						$this->removed_logic_fields[ $meta_key ] = true;

						/**
						 * Set filter if not already set for when save_custom_fields() is called, to remove
						 * these same fields from the array of fields when Field Editor saves custom field values
						 */
						if ( ! $filter_set ) {
							add_filter( 'job_manager_field_editor_custom_enabled_fields_save', array( $this, 'save_custom_fields_remove_logic_fields' ), 10, 4 );
							$filter_set = true;
						}
					}
				}

				// Set required false if one of our meta keys is in active logic configuration (to prevent core from handling validation)
				$required = array_key_exists( 'required', $config ) && $config['required'] === true;

				// Make sure field is required, and is still set in the listing fields array
				if ( $required && array_key_exists( $meta_key, $listing_fields[ $group ] ) ) {

					// For now, we only want to do backend validation for Chosen fields (maybe add other fields later)
					$backend_evaluate = ! empty( $chosen_fields ) && in_array( $meta_key, $chosen_fields );
					$not_req_evaluate = apply_filters( 'field_editor_conditional_logic_set_required_false_eval_logic', $backend_evaluate, $meta_key, $config, $logic, $this );

					$set_required_false = $not_req_evaluate ? $this->logic_not_required_evaluate( $meta_key, $config, $logic ) : $this->logic_not_required( $meta_key, $config, $logic );

					if ( $set_required_false ) {
						$listing_fields[ $group ][ $meta_key ]['required'] = false;
					}

				}

			} // end foreach metakey

		} // end foreach group

		return $listing_fields;
	}

	/**
	 * Get Conditional Group Fields
	 *
	 *
	 * @param bool $logic
	 * @param bool $type_only
	 *
	 * @return array
	 * @since 1.7.10
	 *
	 */
	public function get_group_fields( $logic = false, $type_only = false ) {

		if ( ! $logic ) {
			$logic = $this->get_logic();
		}

		$group_fields = array();

		foreach ( (array) $logic as $group => $gcfg ) {

			if ( ! $type_only || ( $type_only && $gcfg['type'] === $type_only ) ) {
				$group_fields = array_merge( $group_fields, $gcfg['fields'] );
			}

		}

		return $group_fields;
	}

	/**
	 * Get Fields to Hide by Default
	 *
	 * By default, if a field has "show" configuration, it will be added to the list of
	 * default hidden fields.  When using conditional logic, the majority of the time it will be
	 * to "show" fields under certain situations, that is why by default fields are hidden if they
	 * have logic configuration.  You can return false to the filter to fields as shown by default.
	 *
	 * @param $logic
	 *
	 * @return array|bool      An array of fields to hide by default, or false to show fields by default
	 * @since 1.7.10
	 *
	 */
	public function default_hidden( $logic ) {

		$hidden_fields = $this->get_group_fields( $logic, 'show' );

		return apply_filters( 'field_editor_conditionals_default_hidden_fields', $hidden_fields, $this );
	}

	/**
	 * Get Velocity.JS Show Config
	 *
	 *
	 * @return bool|mixed|void
	 * @since 1.8.1
	 *
	 */
	public function get_show_method() {

		if ( ! get_option( 'jmfe_logic_show_use_velocity', false ) ) {
			return false;
		}

		$show_method = array(
			'duration' => (int) get_option( 'jmfe_logic_show_method_duration', 400 ),
			'easing'   => get_option( 'jmfe_logic_show_method_easing', 'spring' ),
			'method'   => get_option( 'jmfe_logic_show_method', 'slideDown' )
		);

		return apply_filters( 'field_editor_conditionals_get_show_method_config', $show_method, $this );
	}

	/**
	 * Get Velocity.JS Hide Config
	 *
	 *
	 * @return bool|mixed|void
	 * @since 1.8.1
	 *
	 */
	public function get_hide_method() {

		if ( ! get_option( 'jmfe_logic_hide_use_velocity', false ) ) {
			return false;
		}

		$hide_method = array(
			'duration' => (int) get_option( 'jmfe_logic_hide_method_duration', 400 ),
			'easing'   => get_option( 'jmfe_logic_hide_method_easing', 'spring' ),
			'method'   => get_option( 'jmfe_logic_hide_method', 'slideUp' )
		);

		return apply_filters( 'field_editor_conditionals_get_hide_method_config', $hide_method, $this );
	}

	/**
	 * Get JS Conditional Config
	 *
	 *
	 * @param bool|array $logic
	 * @param bool|array $meta_keys
	 *
	 * @return array
	 * @since 1.7.10
	 *
	 */
	public function get_js_config( $logic = false, $meta_keys = false ) {

		if ( $logic ) {
			$logic = $this->get_logic();
		}

		$default_hidden = $this->default_hidden( $logic );
		$case_sensitive = get_option( 'jmfe_logic_case_sensitive', false ) == 1 ? true : false;

		$this->js_config = array(
			'delay'          => get_option( 'jmfe_logic_debounce_delay', 250 ),
			// debounce delay on input (amount of time to wait on each input change before checking logic) -- should be in milliseconds (1000ms = 1s)
			'group_types'    => self::get_group_types( $default_hidden ),
			'case_sensitive' => $case_sensitive,
			'chosen_fields'  => $this->get_chosen_fields( $meta_keys ),
			'default_hidden' => $default_hidden,
			'repeatables'    => $this->get_repeatable_fields(),
			'show_method'    => $this->get_show_method(),
			'hide_method'    => $this->get_hide_method(),
			'custom_values'  => $this->get_custom_values()
		);

		return apply_filters( 'field_editor_conditionals_front_js_config', $this->js_config, $this );
	}

	/**
	 * Get Custom Values to use in Logic Configuration
	 *
	 * This method pulls custom values to use in logic on frontend, when an input element may not be available to obtain a value
	 * from.  See below for example array format that should be returned in filter called.
	 *
	 *      $custom_values = array(
	 *          'meta_key_for_check' => array(
	 *              'value' => 'some_static_value'
	 *          ),
	 *          'admin_meta_key_check' => array(
	 *              'value'  => 'some default value if not available in listing meta',
	 *              'source' => 'listing'
	 *          )
	 *      );
	 *
	 * @since 1.8.1
	 *
	 */
	public function get_custom_values() {

		$admin_values = $this->get_admin_only_values();

		// For now only admin values are automatically included, but this could be changed later on
		$custom_values = apply_filters( 'field_editor_conditionals_front_end_custom_values', $admin_values, $this->slug, $this );

		foreach ( (array) $custom_values as $meta_key => $config ) {

			if ( array_key_exists( 'source', $config ) ) {

				// Attempt to pull value from listing, or use default value passed (empty string if not passed)
				if ( $config['source'] === 'listing' ) {
					$default_value                       = array_key_exists( 'value', $config ) ? $config['value'] : '';
					$custom_values[ $meta_key ]['value'] = $this->get_custom_value_from_listing( $meta_key, $default_value );
				}

			}

		}

		return apply_filters( 'field_editor_conditionals_front_end_custom_values_processed', $custom_values, $this->slug, $this );
	}

	/**
	 * Get admin only field values
	 *
	 * This method calls the same filter that is called in the admin area to allow including admin only fields in logic.  This is
	 * done here for frontend to automatically use the same filter, to determine what those meta keys are, and automatically try
	 * and get the value to set in a javascript object so the JS can check the value on the frontend logic.
	 *
	 *
	 * @return array
	 * @since 1.8.1
	 *
	 */
	public function get_admin_only_values() {

		/**
		 * Single or multi-dimensional arrays can be passed to this filter (this is the same filter from admin area).
		 *
		 * Value passed back can be simple flat array: array( 'some_admin_meta_key' )
		 * OR
		 * Value passed can be multi-dimensional array, specifying a default value to use if nothing set on listing yet: array( 'some_admin_meta_key' => array( 'default' => 'xxx' ) );
		 *
		 * The DEFAULT value will be used whenever there is a new listing, or there is no value saved on existing ones
		 */
		$admin_only_fields = apply_filters( "field_editor_conditional_logic_custom_value_{$this->slug}_admin_fields", array(), $this );

		$admin_custom_values = array();

		foreach ( (array) $admin_only_fields as $maybe_index => $maybe_config ) {

			// Multi-dimensional array passed
			if ( is_string( $maybe_index ) ) {

				$admin_custom_values[ $maybe_index ] = array(
					'source' => 'listing',
				);

				// If default was passed in array, set the value to that initially (for use as default)
				if ( array_key_exists( 'default', $maybe_config ) ) {
					$admin_custom_values[ $maybe_index ]['value'] = $maybe_config['default'];
				}

			} else {

				// Flat array was passed, all we set is source, no default
				$admin_custom_values[ $maybe_config ] = array( 'source' => 'listing' );

			}

		}

		return $admin_custom_values;
	}

	/**
	 * Attempt to get custom value from listing meta
	 *
	 *
	 * @param        $meta_key
	 * @param string $default
	 *
	 * @return mixed|string
	 * @since 1.8.1
	 *
	 */
	public function get_custom_value_from_listing( $meta_key, $default = '' ) {

		$listing_id = $this->get_listing_id();

		if ( ! empty( $listing_id ) ) {

			$custom_value = get_post_meta( $listing_id, $meta_key, true );

			if ( empty( $custom_value ) ) {
				// If no value pulled from listing, try prepending underscore (as user may have entered meta key without underscore)
				$custom_value = get_post_meta( $listing_id, "_{$meta_key}", true );
			}

			// If still unable to get any type of value from listing, and the default value is not empty string, use that value
			if ( empty( $custom_value ) && ! empty( $default ) ) {
				$custom_value = $default;
			}

		} else {

			$custom_value = $default;

		}

		return apply_filters( 'field_editor_conditionals_front_end_get_custom_value', $custom_value, $meta_key, $default, $this->slug, $this );
	}

	/**
	 * Attempt to get an existing listing ID
	 *
	 *
	 * @return bool
	 * @since 1.8.1
	 *
	 */
	public function get_listing_id() {

		$listing_id = false;

		// Check if action is to edit a listing
		$is_edit_action = ( array_key_exists( 'action', $_GET ) && $_GET['action'] === 'edit' );

		if ( $is_edit_action ) {
			$listing_id = $this->get_edit_listing_id();
		}

		// Try to get from class object as last resort
		if ( ! $listing_id ) {
			$listing_id = $this->get_class_listing_id();
		}

		return $listing_id;
	}

	/**
	 * Check if current device is Mobile Device
	 *
	 *
	 * @return bool|null
	 * @since 1.9.0
	 *
	 */
	public function is_mobile_device() {

		if ( $this->mobile_device !== null ) {
			return $this->mobile_device;
		}

		$detect              = new WP_Job_Manager_Field_Editor_Mobile_Detect();
		$this->mobile_device = (bool) $detect->isMobile();

		return $this->mobile_device;
	}

	/**
	 * Return All Meta Keys that are Chosen Field Types
	 *
	 *
	 * @param bool $meta_keys
	 *
	 * @return array|bool
	 * @since 1.7.10
	 *
	 */
	public function get_chosen_fields( $meta_keys = false ) {

		$enable_chosen = WP_Job_Manager_Field_Editor::enable_chosen();
		$chosen_enabled = apply_filters( 'job_manager_chosen_enabled', $enable_chosen );

		if ( ! $chosen_enabled ) {
			return false;
		}

		// Chosen does not work on mobile devices, so if this is mobile device, don't return and chosen fields
		if ( $this->is_mobile_device() ) {
			return false;
		}

		if ( ! $meta_keys ) {
			$meta_keys = $this->get_fields();
		}

		$chosen_field_types  = array( 'term-multiselect', 'multiselect', 'term-select' );
		$addon_chosen_fields = array( 'job_region', 'resume_region' );

		$chosen_fields = array();

		foreach ( (array) $meta_keys as $meta_key => $config ) {

			if ( in_array( $meta_key, $addon_chosen_fields ) || ( isset( $config['type'] ) && in_array( $config['type'], $chosen_field_types ) ) ) {
				$chosen_fields[] = $meta_key;
			}

		}

		return apply_filters( 'field_editor_conditionals_get_chosen_fields', $chosen_fields, $meta_keys, $this );
	}

	/**
	 * Localize JS
	 *
	 *
	 * @param $logic
	 * @param $meta_keys
	 *
	 * @since 1.7.10
	 *
	 */
	public function localize( $logic, $meta_keys ) {

		wp_localize_script( 'jmfe-conditionals', 'jmfe_js_logic_config', $this->get_js_config( $logic, $meta_keys ) );

		wp_localize_script( 'jmfe-conditionals', 'jmfe_conditional_logic', $logic );
		wp_localize_script( 'jmfe-conditionals', 'jmfe_logic_meta_keys', $this->build_meta_keys_js( $logic, $meta_keys ) );

	}

	/**
	 * Build Meta Key JS Configurations
	 *
	 * This method will loop through all group/logic configuration, and build an array of data using the
	 * structure below, which will be converted to JSON for use in the javascript on the frontend.
	 *
	 * This is used for handling jQuery callbacks on input changes for meta keys, which is built using
	 * the configuration returned from this method.
	 *
	 * Example:
	 *
	 * 'meta_key' => array(
	 *     'type' => 'text',
	 *     'logic' => array(
	 *          array(
	 *              'group' => 'logic_group',
	 *              'section' => section_array_index,
	 *              'row' => row_array_index
	 *          ),
	 *          array(
	 *              'group' => 'logic_group_2',
	 *              'section' => section_array_index_2,
	 *              'row' => row_array_index_2
	 *          ),
	 *      )
	 * )
	 *
	 * @param array $config           Group logic configuration array
	 * @param array $meta_keys_config Meta key configuration array (should be array with meta keys as array keys)
	 *
	 * @return array    Will return array with meta key as array key, and logic under logic key in array (see doc for example)
	 * @since 1.7.10
	 *
	 */
	public function build_meta_keys_js( $config, $meta_keys_config ) {

		$mk_js         = array();
		$custom_values = ! empty( $this->js_config ) && array_key_exists( 'custom_values', $this->js_config ) ? (array) $this->js_config['custom_values'] : (array) $this->get_custom_values();

		// Loop through each group configuration
		foreach ( (array) $config as $group => $gcfg ) {

			// Group doesn't have any logic configuration
			if ( ! array_key_exists( 'logic', $gcfg ) || empty( $gcfg['logic'] ) ) {
				continue;
			}

			// Loop through each logic section
			foreach ( (array) $gcfg['logic'] as $section_id => $rows ) {

				// Loop through each logic row
				foreach ( (array) $rows as $row_id => $logic ) {

					$meta_key = $logic['check'];

					// May not be a meta key logic, or meta key may no longer exist (removed, etc)
					if ( ! array_key_exists( $meta_key, $meta_keys_config ) ) {
						continue;
					}

					// If meta key not already setup by previous logic config, set defaults now
					if ( ! array_key_exists( $meta_key, $mk_js ) ) {

						$mk_js[ $meta_key ] = array(
							'type'  => str_replace( '-', '_', $meta_keys_config[ $meta_key ]['type'] ),
							'logic' => array(),
						);

						// Set value in array if this is a custom value (non-input pulled value)
						if ( array_key_exists( $meta_key, $custom_values ) ) {
							$mk_js[ $meta_key ]['type'] = 'custom_value';
						}

					}

					// Add logic config for meta key to logic array of arrays
					$mk_js[ $meta_key ]['logic'][] = array(
						'group'   => $group,
						'section' => $section_id,
						'row'     => $row_id
					);

				} // close each logic row

			} // close each logic section

		} // close each group config

		return apply_filters( 'field_editor_conditionals_front_meta_keys_js', $mk_js, $this );
	}

	/**
	 * Register Scripts and Styles
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function register_assets() {

		$enable_chosen = WP_Job_Manager_Field_Editor::enable_chosen();
		$version = $enable_chosen ? '' : '-s2';

		$debugging = defined( 'SMYLES_DEVN' ) && SMYLES_DEVN === TRUE ? time() : WPJM_FIELD_EDITOR_VERSION;

		if ( defined( 'WPJMFE_LOGIC_DEBUG' ) && WPJMFE_LOGIC_DEBUG == true ) {
			$cjs = 'build/conditionals.js';
		} else {
			$cjs = 'conditionals.min.js';
		}

		if ( defined( 'WPJMFE_DT_DEBUG' ) && WPJMFE_DT_DEBUG == true ) {
			$dtjs  = "build/dynamictax{$version}.js";
			$dtcss = "build/dynamictax{$version}.css";
		} else {
			$dtjs  = "dynamictax{$version}.min.js";
			$dtcss = "dynamictax{$version}.min.css";
		}

		wp_register_script( 'jmfe-conditionals', WPJM_FIELD_EDITOR_PLUGIN_URL . "/assets/js/{$cjs}", array( 'jquery' ), $debugging, true );
		wp_register_script( 'jmfe-vendor-velocity', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/velocity.min.js', array( 'jquery' ), $debugging, true );
		wp_register_script( 'jmfe-dynamic-tax', WPJM_FIELD_EDITOR_PLUGIN_URL . "/assets/js/{$dtjs}", array( 'jquery' ), $debugging, true );
		wp_register_style( 'jmfe-dynamic-tax', WPJM_FIELD_EDITOR_PLUGIN_URL . "/assets/css/{$dtcss}", array(), $debugging );
	}

	/**
	 * Get Group Types
	 *
	 *
	 * @param bool $default_hidden
	 *
	 * @return mixed|void
	 * @since 1.7.10
	 *
	 */
	public static function get_group_types( $default_hidden = false ) {

		$types = apply_filters( 'field_editor_conditionals_group_types', array(
			'show'    => array(
				'label'    => __( 'Show', 'wp-job-manager-field-editor' ),
				'icon'     => 'unhide',
				'opposite' => 'hide',
			),
			'hide'    => array(
				'label'    => __( 'Hide', 'wp-job-manager-field-editor' ),
				'icon'     => 'hide',
				'opposite' => 'show',
			),
			'disable' => array(
				'label'    => __( 'Disable', 'wp-job-manager-field-editor' ),
				'icon'     => 'lock',
				'default'  => 'enable',
				'opposite' => 'enable'
			),
			'enable'  => array(
				'label'    => __( 'Enable', 'wp-job-manager-field-editor' ),
				'icon'     => 'unlock',
				'opposite' => 'disable'
			),
		) );

		if ( $default_hidden ) {
			$types['show']['default'] = 'hide';
		} else {
			$types['hide']['default'] = 'show';
		}

		return $types;
	}

	/**
	 * Get Fields Placeholder
	 *
	 *
	 * @return bool
	 * @since 1.7.10
	 *
	 */
	public function get_fields() { return array(); }

	/**
	 * Get listing ID when editing listing placeholder
	 *
	 *
	 * @return bool
	 * @since 1.8.1
	 *
	 */
	public function get_edit_listing_id() { return false; }

	/**
	 * Get listing ID from class object placeholder
	 *
	 *
	 * @return bool
	 * @since 1.8.5
	 *
	 */
	public function get_class_listing_id() { return false; }

	/**
	 * Get Logic Placeholder
	 *
	 *
	 * @return null
	 * @since 1.7.10
	 *
	 */
	public function get_logic() { return null; }

	/**
	 * Get Repeatable Fields Placeholder
	 *
	 *
	 * @return array
	 * @since 1.8.0
	 *
	 */
	public function get_repeatable_fields() { return array(); }

	/**
	 * Get Slug (job/resume)
	 *
	 *
	 * @return string
	 * @since 1.8.1
	 *
	 */
	public function get_slug() {

		return 'unknown';
	}

	/**
	 * Comparison Class Object
	 *
	 *
	 * @return \WP_Job_Manager_Field_Editor_Conditionals_Compare
	 * @since 1.8.5
	 *
	 */
	public function compare() {

		return $this->compare;
	}
}