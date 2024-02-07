<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Job_Fields
 *
 * @since 1.1.9
 *
 */
class WP_Job_Manager_Field_Editor_Job_Fields extends WP_Job_Manager_Field_Editor_Integration {

	private $validate_errors = false;
	private $force_validate = false;
	private $original_submit_handler;

	function __construct() {

		add_filter( 'submit_job_form_fields', array( $this, 'init_fields' ), 100 );
		add_filter( 'submit_job_form_fields_get_job_data', array( $this, 'get_job_data' ), 100, 2 );
		add_filter( 'job_manager_job_listing_data_fields', array( $this, 'admin_fields' ), 100 );
		add_action( 'job_manager_save_job_listing', array( $this, 'save_admin_fields' ), 100, 2 );
		add_action( 'job_manager_update_job_data', array( $this, 'save_fields' ), 100, 2 );
		add_filter( 'submit_job_steps', array( $this, 'steps' ), 100 );
		add_filter( 'submit_job_wp_handle_upload_overrides', array( $this, 'upload_overrides' ), 100 );
		add_filter( 'submit_job_form_fields_get_user_data', array( $this, 'get_user_data' ), 100, 2 );
		add_filter( 'submit_job_form_required_label', array( $this, 'custom_required_label' ), 100, 2 );
		add_filter( 'submit_job_form_submit_button_text', array( $this, 'custom_submit_button' ), 100 );
		add_action( 'submit_job_form_job_fields_start', array( $this, 'job_package_field' ) );

		add_filter( 'job_manager_get_form_action', array( $this, 'check_auto_get_populate_params' ), 10, 2 );

		add_filter( 'submit_job_form_validate_fields', array( $this, 'check_uploads' ), 9999, 3 );
		add_action( 'deleted_term_relationships', array( $this, 'maybe_keep_deleted_term_relationships' ), 9999, 3 );

		add_action( 'job_manager_get_posted_fields', array( $this, 'check_job_tag_field_admin_only' ), 9 );
		add_filter( 'job_manager_settings', array($this, 'unset_job_tags'), 999999, 1 );
		add_filter( 'job_manager_settings', array($this, 'unset_job_type'), 999999, 1 );

		add_action( 'field_editor_update_field_post_meta_end', array($this, 'check_set_job_type_multi'), 10, 5 );

		add_filter( 'submit_job_form_save_job_data', array( $this, 'allow_empty_post_content' ), 99999, 5 );
		// Called only from class-wp-job-manager-form-submit-job
		// add_filter( 'submit_job_form_fields_get_user_data', array( $this, 'new_job_fields' ), 100, 2 );
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
	function maybe_keep_deleted_term_relationships( $post_id, $tt_ids, $taxonomy ){

		if( ! isset( $_POST['job_manager_form'] ) || $_POST['job_manager_form'] !== 'edit-job' ) return;

		$keep_terms = apply_filters('field_editor_maybe_keep_job_deleted_term_relationships', true, $post_id, $taxonomy, $tt_ids);
		if( ! $keep_terms ){
			return;
		}

		$job_fields = $this->get_fields( 'job' );
		$company_fields = $this->get_fields( 'company' );

		$taxonomy_job_fields     = wp_list_filter( $job_fields, array( 'taxonomy' => $taxonomy ) );
		$taxonomy_company_fields = wp_list_filter( $company_fields, array( 'taxonomy' => $taxonomy ) );

		$job_exclude_tax_ids = array();

		if ( ! empty( $taxonomy_job_fields ) ) {
			foreach ( $taxonomy_job_fields as $field ) {
				if ( isset( $field['tax_exclude_terms'] ) ) {
					$job_exclude_tax_ids = explode( ',', $field['tax_exclude_terms'] );
				}
			}
		}

		$company_exclude_tax_ids = array();
		if ( ! empty( $taxonomy_company_fields ) ) {
			foreach ( $taxonomy_company_fields as $field ) {
				if ( isset( $field['tax_exclude_terms'] ) ) {
					$company_exclude_tax_ids = explode( ',', $field['tax_exclude_terms'] );
				}
			}
		}

		$term_ids_to_readd = array();
		$exclude_tax_ids   = array_merge( $job_exclude_tax_ids, $company_exclude_tax_ids );
		foreach ( $tt_ids as $tt_id ) {
			if ( ! in_array( $tt_id, $exclude_tax_ids ) ) {
				continue;
			}
			$term_ids_to_readd[] = $tt_id;
		}

		if( ! empty( $term_ids_to_readd ) ){
			$term_ids_to_readd = array_map( 'absint', $term_ids_to_readd );
			wp_set_object_terms( $post_id, $term_ids_to_readd, $taxonomy, true );
		}
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
	function auto_populate_check_form_get_fields( $form_class ){

		if( ! $form_class || ! isset( $form_class->form_name ) || ! in_array( $form_class->form_name, array( 'submit-job', 'edit-job' ) ) ){
			return false;
		}

		return array_merge( $form_class->get_fields( 'job' ), $form_class->get_fields( 'company' ) );
	}

	/**
	 * Remove 'allow multiple' job_type setting
	 *
	 * Changing the job_types field type should be done through the field editor.  This method removes the checkbox
	 * to enable multiple job types from the job manager settings Job Listings tab.
	 *
	 * @since @@since
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	function unset_job_type( $settings ) {

		$jl_settings = $settings[ 'job_listings' ][ 1 ];

		foreach( (array) $jl_settings as $index => $setting ) {

			if ( $setting[ 'name' ] === 'job_manager_multi_job_type' ) {
				unset( $settings[ 'job_listings' ][ 1 ][ $index ] );

				return $settings;
			}

		}

		return $settings;
	}

	/**
	 * Check if Job Tags field exists or not
	 *
	 * If the user sets the job_tags field to admin only, this can cause a fatal error when trying to save the job listing
	 * due to the null value.  To work around these, we check if the job_tags field exists, if it does, we set the value
	 * to empty value (and not null).
	 *
	 * @param $values
	 *
	 * @return array
	 * @since 1.12.10
	 *
	 */
	public function check_job_tag_field_admin_only( $values ) {
		if( class_exists( 'WP_Job_Manager_Job_Tags' ) && isset( $values['job'] ) && ! isset( $values['job']['job_tags'] ) ) {
			$values['job']['job_tags'] = array();
		}

		return $values;
	}

	/**
	 * Remove Job Tag Input dropdown from Settings
	 *
	 * Changing the job_tag field type should be done through the field editor.  This method removes the dropdown
	 * to select the field type from the job manager settings Job Submission tab.
	 *
	 *
	 * @since 1.3.5
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	function unset_job_tags( $settings ) {

		// No need to go any further if Job Tags is not installed or activated
		if ( ! class_exists( 'WP_Job_Manager_Job_Tags' ) ) return $settings;

		$js_settings = $settings[ 'job_submission' ][ 1 ];

		foreach( (array) $js_settings as $index => $setting ) {

			if ( $setting[ 'name' ] === 'job_manager_tag_input' ) {
				unset( $settings[ 'job_submission' ][ 1 ][ $index ] );

				return $settings;
			}

		}

		return $settings;
	}

	/**
	 * Set "Enable multiple types for listing" option
	 *
	 * When a user customizes the job_type field, if they change the type to a multiple type,
	 * we need to automatically update the core WPJM option to match this since we hide this
	 * config from the settings area.
	 *
	 *
	 * @since 1.6.4
	 *
	 * @param $post_id
	 * @param $meta_key
	 * @param $field_type
	 * @param $action
	 * @param $old_meta
	 */
	function check_set_job_type_multi( $post_id, $meta_key, $field_type, $action, $old_meta ) {

		if ( $meta_key !== 'job_type' ) {
			return;
		}

		$multi = false;

		// Check if field type is a multi field type, or if show dynamic child taxonomy enabled (means allow multi)
		if ( in_array( $field_type, array('term-checklist', 'term-multiselect') ) || isset( $_POST['tax_show_child'] ) ) {
			$multi = true;
		}

		update_option( 'job_manager_multi_job_type', $multi ? 1 : 0 );
	}

	/**
	 * Get Job Field Data
	 *
	 * Called by submit() in both edit and submit form classes and includes all
	 * fields with the value set in the field array config.
	 *
	 * Submit form class calls this method when editing a listing already previewed (so the listing is draft)
	 * Edit form class class this method when editing a listing to populate the values
	 *
	 *
	 * @since 1.3.6
	 *
	 * @param $fields
	 * @param $job
	 *
	 * @return mixed
	 */
	function get_job_data( $fields, $job ){

		$fields = $this->new_job_fields( $fields );
		$fields = $this->remove_invalid_fields( $fields );
		$fields = WP_Job_Manager_Field_Editor_Fields_Date::convert_fields( $fields );

		$fields = $this->set_taxonomy_values( $fields, $job );
		return $fields;

	}

	/**
	 * WPJM Step Filtering
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $steps
	 *
	 * @return mixed
	 */
	function steps( $steps ){

		// Cache the original default handler so we can call it after our submit handler
		//$this->original_submit_handler = $steps[ 'submit' ][ 'handler' ];
		//
		//$steps[ 'submit' ][ 'handler' ] = array( $this, 'submit_handler' );

		// We need to remove the filter for multi type before fields are initialized, to prevent loop
		remove_filter( 'job_manager_multi_job_type', array( $this, 'job_type_multi' ) );
		return $steps;

	}

	/**
	 * WPJM Submit Handler Override
	 *
 	 * @depreciated 1.7.3   No longer used, was previously used to force validation checks.
	 *
	 * @since 1.1.9
	 *
	 */
	function submit_handler(){

		$this->force_validate = true;
		$this->validate_errors = $this->wpjm()->validation_errors();

		// Call original cached submit handler
		call_user_func( $this->original_submit_handler );

	}

	/**
	 * Update Custom Job and Company Field Post Meta
	 *
	 * Called after WPJM updates job_listing post meta with default fields
	 *
	 * @since 1.1.9
	 *
	 * @param $job_id
	 * @param $values
	 *
	 */
	function save_fields( $job_id, $values ) {
		$this->save_custom_fields( 'job', $job_id, $values );
		$this->save_custom_fields( 'company', $job_id, $values );
	}

	/**
	 * Listing saved from admin section
	 *
	 *
	 * @since 1.3.1
     * @since 1.8.0 -- moved from integration class
	 *
	 * @param $post_id
	 * @param $post
	 *
	 */
	function save_admin_fields( $post_id, $post ) {

		/**
		 * Backwards compatibility for WPJM <1.24.0 and removing featured image
		 * when saving from admin.  WPJM >= 1.24.0 uses company_logo and remove/add
		 * is handled by core WPJM.
		 */
		if ( defined( 'JOB_MANAGER_VERSION' ) && version_compare( JOB_MANAGER_VERSION, '1.24.0', 'lt' ) && apply_filters( 'job_manager_field_editor_set_featured_image', true ) ) {

			$fi_id = get_post_thumbnail_id( $post_id );
			if ( $fi_id ) {
				$fi_url = wp_get_attachment_url( $fi_id );
				update_post_meta( $post_id, '_featured_image', $fi_url );
			} else {
				delete_post_thumbnail( $post_id );
				delete_post_meta( $post_id, '_featured_image' );
			}
		}

		$this->save_admin_custom_fields( 'job', $post_id );
		$this->save_admin_custom_fields( 'company', $post_id );
	}

	/**
	 * Output Job and Company fields in Admin
	 *
	 * Called by WP Job Manager filter on admin side to return
	 * job and company fields with user customization.
	 *
	 * @since 1.1.9
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function admin_fields( $fields ) {

		return $this->prep_admin_fields( array( 'job', 'company' ), $fields );

	}

	/**
	 * Add a hidden field with product id to form
	 *
	 *
	 * @since 1.2.2
	 *
	 */
	function job_package_field(){

		if( WP_Job_Manager_Field_Editor_reCAPTCHA::is_enabled() ) {
			wp_enqueue_script( 'jmfe-recaptcha' );
		}

		$package  = isset( $_REQUEST[ 'wcpl_jmfe_product_id' ] ) ? intval( $_REQUEST[ 'wcpl_jmfe_product_id' ] ) : false;

		if( isset( $_REQUEST['job_package'] ) && ! empty( $_REQUEST['job_package'] ) ) {
			$package = sanitize_text_field( $_REQUEST['job_package'] );
		} elseif( isset( $_REQUEST['choose_package'] ) && ! empty( $_REQUEST['choose_package'] ) ){
			$package = sanitize_text_field( $_REQUEST['choose_package'] );
		}

		if( $package ){
			$package = WP_Job_Manager_Field_Editor_Package_WC::get_product_id( $package );
			echo "<input type=\"hidden\" name=\"wcpl_jmfe_product_id\" value=\"{$package}\" />";
		}

	}

	/**
	 * Initialize Job and Company Fields
	 *
	 * Called by WP Job Manager filter in init_fields() to return job and
	 * company fields with user customization.
	 *
	 * Will return ALL fields including disabled fields
	 *
	 * @since 1.1.9
	 *
	 * @param array $fields
	 *
	 * @return mixed
	 */
	function init_fields( $fields ) {

		if ( ! $this->was_filter_forced() ) {

			$fields = $this->merge_with_custom_fields( $fields );

			// Remove resume fields after merge
			if( isset( $fields['resume_fields'] ) ) {
				unset( $fields['resume_fields'] );
			}

			// Remove company fields after merge
			if( isset( $fields['company_fields'] ) ) {
				unset( $fields['company_fields'] );
			}

			// Set $product_id equal to our custom product id hidden input
			$product_id = isset( $_REQUEST['wcpl_jmfe_product_id'] ) ? intval($_REQUEST['wcpl_jmfe_product_id']) : '';
			// Set $job_package equal to value POST/GET if there is one set for `job_package`, otherwise revert to our custom $product_id value above
			$job_package = isset( $_REQUEST['job_package'] ) ? sanitize_text_field( $_REQUEST['job_package'] ) : $product_id;

			// If for some reason neither of the above are able to set job package value, check if it's set in the `choose_package` value (specific to Astoundify Listing Payments)
			if( empty( $job_package ) && isset( $_REQUEST['choose_package'] ) && ! empty( $_REQUEST['choose_package'] ) ){
				$job_package = sanitize_text_field( $_REQUEST['choose_package'] );
			}

			$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : false;
			$job_id = isset( $_REQUEST['job_id' ] ) ? intval( $_REQUEST['job_id'] ) : false;

			// Admin only filter
			$fields = $this->admin_only_fields( $fields );

			// Product/Package Handling, get job_package from post meta
			if( $job_id && empty( $job_package ) ) {
				$job_package = WP_Job_Manager_Field_Editor_Package_WC::get_post_package_id( $job_id );
			}

			// If listing is tied to package, filter so only fields for that package are shown
			if ( $job_package ) {
				$fields = WP_Job_Manager_Field_Editor_Package_WC::filter_fields( $fields, $job_package );
			}

			// Check $_POST for submit_job (default) or edit_job (from Preview clicking on Edit button)
			$is_submit_or_edit = ( isset( $_POST['submit_job'] ) && ! empty( $_POST['submit_job'] ) || ( isset( $_POST['edit_job'] ) && ! empty( $_POST['edit_job'] ) ) );
			// Fields loaded when user clicks on edit from dashboard
			$is_frontend_edit = $action === 'edit' && ! empty( $job_id );

			// $action === 'edit'
			// edit_job
			// job_manager_form = submit-job
			// If fields not init by preview, or save, return standard fields ( customizations returned in new_job_fields )
			if ( $this->validate_errors || ! empty( $job_package ) || ! $job_id || $is_submit_or_edit || $is_frontend_edit ) {
				$fields = $this->new_job_fields( $fields );
			}

			// If called by force validation, set fields equal to field config for validation
			if ( $this->force_validate ) {
				$fields = $this->validation_fields( $fields );
			}

			$fields = apply_filters( 'job_manager_field_editor_job_init_fields', $fields );

		}

		return $fields;

	}

	/**
	 * Format fields to work with test vaidation
	 *
	 * In order to return all fields even those disabled we must test validation to determine
	 * fields to return.  To prevent errors when running through core validation, we have to
	 * customize some of the fields.
	 *
	 *
	 * @since 1.2.2
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function validation_fields( $fields ){

		$fields[ 'job' ]     = array_map( array( $this, 'set_required_false' ), $fields[ 'job' ] );
		$fields[ 'company' ] = array_map( array( $this, 'set_required_false' ), $fields[ 'company' ] );

		if ( version_compare( JOB_MANAGER_VERSION, '1.14.0', 'le' ) ) {
			// Version 1.14.0 and earlier do not have filter for upload test, so we have to remove file fields
			// to prevent error when testing validation.
			$fields[ 'job' ]     = $this->fields_list_filter( $fields[ 'job' ], array( 'type' => 'file' ), 'NOT' );
			$fields[ 'company' ] = $this->fields_list_filter( $fields[ 'company' ], array( 'type' => 'file' ), 'NOT' );

		}

		return $fields;
	}

	/**
	 * Set wp_handle_upload Arguments
	 *
	 * When testing validation on form we need to set upload validation test
	 * form to TRUE in order to prevent actually uploading the file.
	 *
	 * @since 1.1.12
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	function upload_overrides( $args ) {

		// If filter wasn't forced don't set test form true
		//
		// Check for parent force validate for WPRM >= 1.9.1 which removes the resumes
		// filter for uploads and now uses core WPJM upload.
		if ( ! $this->force_validate && ! parent::$force_validate_resumes ) return $args;

		$this->force_validate = FALSE;
		parent::$force_validate_resumes = FALSE;
		$args[ 'test_form' ]  = TRUE;

		return $args;
	}

	/**
	 * Validate file uploads based on configurations
	 *
	 * This method checks file uploads for max uploads, etc, and returns a WP_Error
	 * if all conditions are not met.
	 *
	 *
	 * @since 1.5.0
	 *
	 * @param $no_errors
	 * @param $fields
	 * @param $values
	 *
	 * @return bool|\WP_Error
	 */
	function check_uploads( $no_errors, $fields, $values ){

		$job_uploads = false;
		$company_uploads = false;

		/**
		 * Filter out all fields except for file upload fields
		 */
		$job_fields = isset( $fields['job'] ) && ! empty( $fields['job'] ) ? $this->fields_list_filter( $fields['job'], array('type' => 'file'), 'AND' ) : array();
		$company_fields = isset( $fields['company'] ) && ! empty( $fields['company'] ) ? $this->fields_list_filter( $fields['company'], array('type' => 'file'), 'AND' ) : array();

		if( ! empty( $job_fields ) && isset( $values[ 'job' ] ) && ! empty( $values[ 'job' ] ) ) {
			$job_uploads = $this->check_max_upload( $job_fields, $values['job'] );
		}

		if( is_wp_error( $job_uploads ) ) {
			return $job_uploads;
		}

		if( ! empty( $company_fields ) && isset( $values[ 'company' ] ) && ! empty( $values[ 'company' ] ) ) {
			$company_uploads = $this->check_max_upload( $company_fields, $values[ 'company' ] );
		}

		if( is_wp_error( $company_uploads ) ){
			return $company_uploads;
		}

		return $no_errors;
	}

	/**
	 * Output Job and Company Fields for Template
	 *
	 * Called by WP Job Manager filter in submit() to return job and
	 * company fields with user customization for output in template.
	 *
	 * Any disabled fields are NOT included in the return $fields
	 *
	 * @since 1.1.9
	 *
	 * @param array $fields
	 *
	 * @return mixed
	 */
	function new_job_fields( $fields ) {

		// Fields were initialized to output form, removed disabled fields from array
		$fields[ 'job' ]     = wp_list_filter( $fields[ 'job' ], array( 'status' => 'disabled' ), 'NOT' );
		if( isset( $fields['company'] ) ){
			$fields['company'] = wp_list_filter( $fields['company'], array( 'status' => 'disabled' ), 'NOT' );
		}

		return $fields;

	}

	/**
	 * Filter out Admin Only fields
	 *
	 * If configuration value is set for field to be admin only
	 * this function will remove those fields from the array.
	 *
	 *
	 * @since 1.2.5
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function admin_only_fields( $fields ){

		if( ! apply_filters( 'field_editor_init_job_fields_remove_admin_only_fields', true, $fields, $this ) ){
			return $fields;
		}

		$fields[ 'job' ]     = wp_list_filter( $fields[ 'job' ], array('admin_only' => '1'), 'NOT' );
		$fields[ 'company' ] = isset( $fields['company'] ) ? wp_list_filter( $fields[ 'company' ], array('admin_only' => '1'), 'NOT' ) : array();

		return $fields;

	}

	/**
	 * Return Custom Required Label
	 *
	 *
	 * @since 1.1.14
	 *
	 * @param $label
	 * @param $field
	 *
	 * @return string
	 */
	function custom_required_label( $label, $field = false ){

		// Required Field
		if ( $label === '' ) {
			$custom_req_label = get_option( 'jmfe_required_label' );
			if( get_option( 'jmfe_enable_required_label' ) && $custom_req_label ){
				$custom_req_label= html_entity_decode( $custom_req_label);
				$label = ' ' . __( $custom_req_label, 'wp-job-manager-field-editor' );
			}

		}

		// Optional Field
		$defaultOptional = ' <small>' . __( '(optional)', 'wp-job-manager' ) . '</small>';

		$skip_field_types = apply_filters( 'field_editor_job_required_label_field_types', array('header', 'html', 'actionhook') );
		if( isset( $field, $field['type'] ) && in_array( $field['type'], $skip_field_types ) ) return '';

		if( $label === $defaultOptional ){
			$custom_opt_label = get_option( 'jmfe_optional_label' );
			if( get_option( 'jmfe_enable_optional_label' ) && $custom_opt_label ){
				$custom_opt_label= html_entity_decode( $custom_opt_label);
				$label = ' ' . __( $custom_opt_label, 'wp-job-manager-field-editor' );
			} elseif( get_option( 'jmfe_enable_required_label' ) ) {
				$label = '';
			}
		}

		return $label;
	}

	/**
	 * Return "(optional)" label
	 *
	 * This method is specifically to deal with grunt task to add text domain, from
	 * replacing 'wp-job-manager' with this plugin's text domain (since this file is omitted)
	 * as we need it to specifically match exactly the wp-job-manager text domain
	 *
	 * @since 1.8.9
	 *
	 * @return mixed|string|void
	 */
	public static function get_optional_string(){
		return __( '(optional)', 'wp-job-manager' );
	}

	/**
	 *  Return Custom Submit Button
	 *
	 *
	 * @since 1.1.14
	 *
	 * @param $label
	 *
	 * @return mixed|void
	 */
	function custom_submit_button( $label ) {

		$custom_submit_button = get_option( 'jmfe_job_submit_button' );
		if ( get_option( 'jmfe_enable_job_submit_button' ) && $custom_submit_button ) {
			$label = __( $custom_submit_button, 'wp-job-manager-field-editor' );
		}

		return $label;
	}

}