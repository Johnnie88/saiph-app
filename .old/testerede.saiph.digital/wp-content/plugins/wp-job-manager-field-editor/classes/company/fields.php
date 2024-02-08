<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Company_Fields
 *
 * @since 1.10.0sion
 *
*/
class WP_Job_Manager_Field_Editor_Company_Fields extends WP_Job_Manager_Field_Editor_Integration {

	private $validate_errors = false;
	private $force_validate = false;

	function __construct() {

		add_filter( 'submit_company_form_fields', array( $this, 'init_fields' ), 100 );
		add_filter( 'submit_company_form_fields_get_company_data', array( $this, 'get_company_data' ), 100, 2 );
		add_filter( 'company_manager_company_fields', array( $this, 'admin_fields' ), 100 );

		add_action( 'company_manager_update_company_data', array( $this, 'save_fields' ), 100, 2 );

		/**
		 * Astoundify Company Listings / Techbrise Company Listings
		 *
		 * Because the plugins file is loaded after plugins_loaded() it does not catch the "Fields" class filter like it does for themes,
		 * so we define this here as a backup (since should only be called by those plugins above).
		 */
		add_action( 'company_listings_update_company_data', array( $this, 'save_fields' ), 100, 2 );
		/**
		 * Same for MAS Company Listings
		 */
		add_action( 'mas_job_manager_company_update_company_data', array( $this, 'save_fields' ), 100, 2 );

		add_action( 'company_manager_save_company', array( $this, 'save_admin_fields' ), 100, 2 );
		add_action( 'deleted_term_relationships', array( $this, 'maybe_keep_deleted_term_relationships' ), 9999, 3 );

		add_filter( 'submit_company_form_fields_get_user_data', array( $this, 'get_user_data' ), 100, 2 );
		add_filter( 'submit_company_form_required_label', array( $this, 'custom_required_label' ), 100, 2 );
		add_filter( 'submit_company_form_submit_button_text', array( $this, 'custom_submit_button' ), 100 );
		add_action( 'submit_company_form_start', array($this, 'company_package_field') );

		add_filter( 'submit_company_form_save_company_data', array( $this, 'allow_empty_post_content' ), 99999, 5 );

		add_filter( 'submit_company_form_validate_fields', array( $this, 'check_uploads' ), 9999, 3 );

		add_filter( 'job_manager_get_form_action', array( $this, 'check_auto_get_populate_params' ), 10, 2 );
	}

	/**
	 * Maybe Keep Deleted Term Relationships
	 *
	 * If a taxonomy has specific terms hidden from the frontend, this is most likely to only allow an Admin to set these
	 * terms from the admin side.  This handling will re-add those terms when they are deleted if they were set on the listing (being deleted)
	 * and set as hidden in field configuration.
	 *
	 * @param $post_id
	 * @param $tt_ids
	 * @param $taxonomy
	 *
	 * @since 1.12.10
	 *
	 */
	function maybe_keep_deleted_term_relationships( $post_id, $tt_ids, $taxonomy ) {

		if ( ! isset( $_POST['company_manager_form'] ) || $_POST['company_manager_form'] !== 'edit-company' ) {
			return;
		}

		$keep_terms = apply_filters( 'field_editor_maybe_keep_company_deleted_term_relationships', true, $post_id, $taxonomy, $tt_ids );
		if ( ! $keep_terms ) {
			return;
		}

		$fields = $this->get_fields( 'company_fields' );

		$taxonomy_fields = wp_list_filter( $fields, array( 'taxonomy' => $taxonomy ) );

		$exclude_tax_ids = array();

		if ( ! empty( $taxonomy_fields ) ) {
			foreach ( $taxonomy_fields as $field ) {
				if ( isset( $field['tax_exclude_terms'] ) ) {
					$exclude_tax_ids = explode( ',', $field['tax_exclude_terms'] );
				}
			}
		}

		$term_ids_to_readd = array();
		foreach ( $tt_ids as $tt_id ) {
			if ( ! in_array( $tt_id, $exclude_tax_ids ) ) {
				continue;
			}
			$term_ids_to_readd[] = $tt_id;
		}

		if ( ! empty( $term_ids_to_readd ) ) {
			$term_ids_to_readd = array_map( 'absint', $term_ids_to_readd );
			wp_set_object_terms( $post_id, $term_ids_to_readd, $taxonomy, true );
		}
	}

	/**
	 * Get company Field Data
	 *
	 * Called by submit() in both edit and submit form classes and includes all
	 * fields with the value set in the field array config.
	 *
	 * Submit form class calls this method when editing a listing already previewed (so the listing is draft)
	 * Edit form class class this method when editing a listing to populate the values
	 *
	 *
	 * @since 1.10.0
	 *
	 * @param $fields
	 * @param $company
	 *
	 * @return mixed
	 */
	function get_company_data( $fields, $company ) {

		$fields = $this->new_company_fields( $fields );
		$fields = $this->remove_invalid_fields( $fields );
		$fields = WP_Job_Manager_Field_Editor_Fields_Date::convert_fields( $fields );

		$fields = $this->set_taxonomy_values( $fields, $company );

		return $fields;

	}

	/**
	 * Update Custom company Fields Post Meta
	 *
	 * Called after WPJM updates company post meta with default fields
	 *
	 * @since 1.10.0
	 *
	 * @param integer $company_id
	 * @param array   $values
	 *
	 */
	function save_fields( $company_id, $values ) {
		$this->save_custom_fields( 'company_fields', $company_id, $values );
	}

	/**
	 * Save/Process Custom Fields on Admin Save
	 *
	 *
	 * @since 1.10.0
	 *
	 * @param $company_id
	 * @param $post
	 */
	function save_admin_fields( $company_id, $post ) {
		$this->save_admin_custom_fields( 'company_fields', $company_id );
	}

	/**
	 * Output company fields in Admin
	 *
	 * Called by WP company Manager filter on admin side to
	 * return company fields with user customization.
	 *
	 * @since 1.10.0
	 *
	 * @param array $fields
	 *
	 * @return mixed
	 */
	function admin_fields( $fields ) {
		return $this->prep_admin_fields( 'company_fields', $fields );
	}

	/**
	 * Add a hidden field with product id to form
	 *
	 *
	 * @since 1.10.0
	 */
	function company_package_field() {

		if( WP_Job_Manager_Field_Editor_reCAPTCHA::is_enabled( 'company' ) ) {
			wp_enqueue_script( 'jmfe-recaptcha' );
		}

		$product_id = isset($_REQUEST['wcpl_jmfe_product_id']) ? intval( $_REQUEST['wcpl_jmfe_product_id'] ) : FALSE;
		$package    = isset($_REQUEST['company_package']) ? sanitize_text_field( $_REQUEST['company_package'] ) : $product_id;

		if( $package ) {
			$package = WP_Job_Manager_Field_Editor_Package_WC::get_product_id( $package );
			echo "<input type=\"hidden\" name=\"wcpl_jmfe_product_id\" value=\"{$package}\" />";
		}

	}

	/**
	 * Initialize company Fields
	 *
	 * Called by WP Job Manager filter in init_fields() to return
	 * company fields with user customization.
	 *
	 * @since 1.10.0
	 *
	 * @param array $fields
	 *
	 * @return mixed
	 */
	function init_fields( $fields ) {

		if ( ! $this->was_filter_forced() ) {

			$fields = $this->merge_with_custom_fields( $fields );

			// Remove job fields after merge
			if ( isset( $fields[ 'job' ] ) ) unset( $fields[ 'job' ] );
			// Remove company fields after merge
			if ( isset( $fields[ 'company' ] ) ) unset( $fields[ 'company' ] );
			// Remove resume fields after merge
			if ( isset( $fields[ 'resume_fields' ] ) ) unset( $fields[ 'resume_fields' ] );

			$product_id     = isset($_REQUEST['wcpl_jmfe_product_id']) ? intval( $_REQUEST['wcpl_jmfe_product_id'] ) : '';
			$company_package = isset($_REQUEST['company_package']) ? sanitize_text_field( $_REQUEST['company_package'] ) : $product_id;
			$action         = isset($_GET['action']) ? sanitize_text_field( $_GET['action'] ) : FALSE;
			$company_id      = isset($_REQUEST['company_id']) ? intval( $_REQUEST['company_id'] ) : FALSE;

			// Admin only filter
			$fields = $this->admin_only_fields( $fields );

			// Product/Package Handling, get job_package from post meta
			if( $company_id && empty($company_package) ) $company_package = WP_Job_Manager_Field_Editor_Package_WC::get_post_package_id( $company_id );

			// If listing is tied to package, filter so only fields for that package are shown
			if( $company_package ) $fields = WP_Job_Manager_Field_Editor_Package_WC::filter_fields( $fields, $company_package );

			// Check $_POST for submit_company (default) or edit_company (from Preview clicking on Edit button)
			$is_submit_or_edit = ( isset( $_POST['submit_company'] ) && ! empty( $_POST['submit_company'] ) || ( isset( $_POST[''] ) && ! empty( $_POST['edit_company'] ) ) );
			// Fields loaded when user clicks on edit from dashboard
			$is_frontend_edit = $action === 'edit' && ! empty( $company_id );

			// Other arg available in $_POST - company_manager_form = submit-company
			// If fields init by post new company, return fields with disabled removed
			if ( $this->validate_errors || ! empty($company_package) || ! $company_id || $is_submit_or_edit || $is_frontend_edit ) {
				$fields = $this->new_company_fields( $fields );
			}

			// If called by force validation, set fields equal to field config for validation
			if ( $this->force_validate ) $fields = $this->validation_fields( $fields );

		}

		return $fields;

	}

	/**
	 * Format fields to work with test validation
	 *
	 * In order to return all fields even those disabled we must test validation to determine
	 * fields to return.  To prevent errors when running through core validation, we have to
	 * customize some of the fields.
	 *
	 *
	 * @since 1.10.0
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function validation_fields( $fields ) {

		$fields[ 'company_fields' ] = array_map( array( $this, 'set_required_false' ), $fields[ 'company_fields' ] );

		if ( version_compare( COMPANY_MANAGER_VERSION, '1.7.5', 'le' ) ) {
			// Version 1.7.5 and earlier do not have filter for upload test, so we have to remove file fields
			// to prevent error when testing validation.
			$fields[ 'company_fields' ] = $this->fields_list_filter( $fields[ 'company_fields' ], array( 'type' => 'file' ), 'NOT' );
		}

		return $fields;
	}

	/**
	 * Validate file uploads based on configurations
	 *
	 * This method checks file uploads for max uploads, etc, and returns a WP_Error
	 * if all conditions are not met.
	 *
	 *
	 * @since 1.10.0
	 *
	 * @param $no_errors
	 * @param $fields
	 * @param $values
	 *
	 * @return bool|\WP_Error
	 */
	function check_uploads( $no_errors, $fields, $values ) {

		$company_uploads = FALSE;

		/**
		 * Filter out all fields except for file upload fields
		 */
		$company_fields = isset( $fields[ 'company_fields' ] ) && ! empty( $fields[ 'company_fields' ] ) ? $this->fields_list_filter( $fields[ 'company_fields' ], array( 'type' => 'file' ), 'AND' ) : array();

		if( ! empty( $company_fields ) && isset( $values[ 'company_fields' ] ) && ! empty( $values[ 'company_fields' ] ) ) {
			$company_uploads = $this->check_max_upload( $company_fields, $values[ 'company_fields' ] );
		}

		if( is_wp_error( $company_uploads ) ) {
			return $company_uploads;
		}

		return $no_errors;
	}

	/**
	 * Output company Fields for Template
	 *
	 * Called by WP Job Manager filter in submit() to return company
	 * fields with user customization for output in template.
	 *
	 * @since 1.10.0
	 *
	 * @param array $fields
	 *
	 * @return mixed
	 */
	function new_company_fields( $fields ) {

		// Fields were initialized to output form, removed disabled fields from array
		$fields[ 'company_fields' ] = wp_list_filter( $fields[ 'company_fields' ], array( 'status' => 'disabled' ), 'NOT' );
		return $fields;

	}

	/**
	 * Filter out Admin Only fields
	 *
	 * If configuration value is set for field to be admin only
	 * this function will remove those fields from the array.
	 *
	 *
	 * @since 1.10.0
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function admin_only_fields( $fields ) {

		$fields[ 'company_fields' ] = wp_list_filter( $fields[ 'company_fields' ], array('admin_only' => '1'), 'NOT' );
		return $fields;

	}

	/**
	 * Custom company Fields Required Label
	 *
	 *
	 * @since 1.10.04
	 *
	 * @param $label
	 * @param $field
	 *
	 * @return string
	 */
	function custom_required_label( $label, $field = false ) {

		// Required Field
		if ( $label === '' ) {
			$custom_req_label = get_option( 'jmfe_company_required_label' );
			if ( get_option( 'jmfe_enable_company_required_label' ) && $custom_req_label ) {
				$custom_req_label= html_entity_decode( $custom_req_label);
				$label = ' ' . __( $custom_req_label, 'wp-job-manager-field-editor' );
			}
		}

		// Optional Field
		$defaultOptional = ' <small>' . __( '(optional)', 'wp-job-manager', 'wp-job-manager-field-editor' ) . '</small>';

		$skip_field_types = apply_filters( 'field_editor_company_required_label_field_types', array('header', 'html', 'actionhook') );
		if( isset($field, $field['type']) && in_array( $field['type'], $skip_field_types ) ) return '';

		if ( $label === $defaultOptional ) {
			$custom_opt_label = get_option( 'jmfe_company_optional_label' );
			if ( get_option( 'jmfe_enable_company_optional_label' ) && $custom_opt_label ) {
				$custom_opt_label= html_entity_decode( $custom_opt_label);
				$label = ' ' . __( $custom_opt_label, 'wp-job-manager-field-editor' );
			} elseif ( get_option( 'jmfe_enable_company_required_label' ) ) {
				$label = '';
			}
		}

		return $label;
	}

	/**
	 * Custom company Submit Button Label
	 *
	 *
	 * @since 1.10.04
	 *
	 * @param $label
	 *
	 * @return mixed|void
	 */
	function custom_submit_button( $label ){

		$custom_submit_button = get_option( 'jmfe_company_submit_button' );

		if ( get_option( 'jmfe_enable_company_submit_button' ) && $custom_submit_button ) {
			$label = __( $custom_submit_button, 'wp-job-manager-field-editor' );
		}

		return $label;
	}

	/**
	 * Auto Populate Form Action Check
	 *
	 * This method checks if the current form name matches the class this filter is being ran for,
	 * if it does, we then return a merged array of all the fields to check and remove from the action
	 * URL
	 *
	 * @param WP_Job_Manager_Form $form_class
	 *
	 * @return array|false
	 * @since 1.10.2
	 *
	 */
	function auto_populate_check_form_get_fields( $form_class ) {

		if ( ! $form_class || ! isset( $form_class->form_name ) || ! in_array( $form_class->form_name, array( 'submit-company', 'edit-company' ) ) ) {
			return false;
		}

		return $form_class->get_fields( 'company_fields' );
	}
}