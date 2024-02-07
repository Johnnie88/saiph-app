<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Ajax
 *
 * @since 1.1.9
 *
 */
class WP_Job_Manager_Field_Editor_Ajax extends WP_Job_Manager_Field_Editor_Admin {

	protected $field_group;
	protected $field_group_parent;
	private $is_error = false;
	private $post_meta_key;
	private $post_modal_action;
	private $field_post_id;

	function __construct() {

		add_action( 'wp_ajax_jmfe_save_field', array( $this, 'save_field' ) );
		add_action( 'wp_ajax_jmfe_list_filter', array( $this, 'list_filter' ) );

		add_action( 'wp_ajax_jmfe_conditionals_save_group', array( $this, 'ajax_save_group' ) );
		add_action( 'wp_ajax_jmfe_conditionals_remove_group', array( $this, 'ajax_remove_group' ) );

	}

	public function ajax_remove_group() {

		check_ajax_referer( 'jmfe_conditionals_nonce' );

		$post_type = sanitize_text_field( $_POST['post_type'] );
		$slug = sanitize_text_field( $_POST['group_slug'] );

		$conditional_logic = get_option( "field_editor_{$post_type}_conditional_logic", array() );

		if( ! array_key_exists( $slug, $conditional_logic ) ){
			ob_clean();
			wp_send_json_error( array( 'error' => __( 'Unable to remove group -- group not found.', 'wp-job-manager-field-editor' ) ) );
		}

		// Set name removed value, before removing from array (so we can use in message)
		$name_removed = $conditional_logic[ $slug ]['name'];

		unset( $conditional_logic[ $slug ] );
		update_option( "field_editor_{$post_type}_conditional_logic", $conditional_logic );

		ob_clean();
		wp_send_json_success( array( 'removed' => true, 'conditionals' => $conditional_logic, 'message' => sprintf( __( 'Successfully removed "%s" conditional group.', 'wp-job-manager-field-editor' ), $name_removed ) ) );
	}

	/**
	 * Recursively Sanitize Array Values
	 *
	 *
	 * @since 1.7.10
	 *
	 * @param $array array
	 *
	 * @return mixed
	 */
	public function sanitize_array( &$array ){

		foreach ( $array as &$value ) {

			if( is_array( $value ) ){
				$this->sanitize_array( $value );
				continue;
			}


			$value = sanitize_text_field( $value );

		}

		return $array;
	}

	public function ajax_save_group() {

		check_ajax_referer( 'jmfe_conditionals_nonce' );

		$post_type = sanitize_text_field( $_POST['post_type'] );
		$slug = sanitize_text_field( $_POST['group_slug'] );

		$conditional_logic = get_option( "field_editor_{$post_type}_conditional_logic", array() );

		$values = $this->sanitize_array( $_POST[ $slug ] );

		if( ! array_key_exists( 'name', $values ) || empty( $values[ 'name' ] ) ){
			wp_send_json_error( array( 'error' => __( 'You must specify a group name to save the configuration!', 'wp-job-manager-field-editor' ) ) );
		}

		if( $slug === 'new_group' || empty( $slug ) ){

			$name = $values['name'];

			$slug_count = 0;
			$slug = strtolower( trim( $name ) );
			// Replace spaces with underscores
			$slug = str_replace( ' ', '_', $slug );
			// Only allowed characters are a-z 0-9 and underscores
			$slug = preg_replace( '/[^a-z0-9_]/', '', $slug );

			// Trim slug length to max of 30 characters
			if( strlen( $slug ) > 30 ){
				$slug = substr( $slug, 0, 30 );
			}

			while( array_key_exists( $slug, $conditional_logic ) ){

				if( $slug_count > 0 ){
					// Remove previous count from slug -- since someslug_1 isn't unique, let's try someslug_2, and so on...
					$slug = rtrim( $slug, "_{$slug_count}" );
				}

				$slug_count ++;
				$slug = "{$slug}_{$slug_count}";
			}

			// Update slug value sent though POST
			$values['slug'] = $slug;
		}

		// Build data array to merge/save
		$data = array( $slug => $values );

		// Convert fields from CSV to array
		$data[ $slug ]['fields'] = ! empty( $values['fields'] ) ? explode( ',', $values['fields'] ) : array();

		$updated_logic = array_merge( $conditional_logic, $data );
		$result = update_option( "field_editor_{$post_type}_conditional_logic", $updated_logic );

		ob_clean();

		if( $result ){
			wp_send_json_success( array( 'slug' => $slug,  'conditionals' => $updated_logic, 'message' => __( 'Successfully saved group configuration.', 'wp-job-manager-field-editor' ) ) );
		} else {
			wp_send_json_error( array( 'error' => __( 'Unable to update, are you sure there is anything to update?', 'wp-job-manager-field-editor' ) ) );
		}

	}

	/**
	 * Get Filtered Field List Table
	 *
	 *
	 * @since 1.1.9
	 *
	 */
	public function list_filter(){
		$response = array();
		check_ajax_referer( 'jmfe_list_filter', 'nonce' );

		$this->field_group = filter_input( INPUT_POST, 'field_group', FILTER_SANITIZE_STRING );
		$this->post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );

		if ( ob_get_length() ) ob_end_clean();
		ob_start();

		$response[ 'status' ] = "success";
		$response[ 'body' ] = $this->get_list_body_ajax_html( true );

		if ( ob_get_length() ) ob_end_clean();

		echo json_encode( $response );

		die();

	}

	/**
	 * Save custom, customized, or any edited fields.
	 *
	 * Should not be called directly, should only be used through ajax
	 * for updating, or creating field configuration data.
	 *
	 * @since 1.0.0
	 *
	 */
	public function save_field() {
		$response = array();
		check_ajax_referer( 'jmfe_save_field', 'nonce' );
		if( ob_get_length() ) ob_end_clean();
		ob_start();
		$this->field_group    = filter_input( INPUT_POST, 'field_group', FILTER_SANITIZE_STRING );
		$this->post_type    = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );
		$this->field_group_parent = filter_input( INPUT_POST, 'field_group_parent', FILTER_SANITIZE_STRING );
		if( $this->field_group_parent && $this->field_group ) $this->field_group = array( $this->field_group_parent => $this->field_group );

		if( ! $this->field_group ){
			$this->is_error        = true;
			$response[ 'message' ] = __( 'A field group is required in order to save/edit/update fields, please contact support.', 'wp-job-manager-field-editor' );
		}

		$default_fields = $this->get_default_fields();
		$custom_fields = $this->get_custom_fields();

		$field_type = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
		$taxonomy = filter_input( INPUT_POST, 'taxonomy', FILTER_SANITIZE_STRING );
		$this->post_meta_key = filter_input( INPUT_POST, 'meta_key', FILTER_SANITIZE_STRING );
		$this->post_modal_action = filter_input( INPUT_POST, 'modal_action', FILTER_SANITIZE_STRING );
		$this->field_post_id = $this->get_field_post_id();
		$response[ 'action' ] = $this->post_modal_action;

		// Check if taxonomy entered actually exists
		if( ! empty( $taxonomy ) ){
			if( ! taxonomy_exists( $taxonomy ) && $this->post_modal_action !== 'disable' ) {
				$this->is_error = true;
				$response[ 'message' ] = sprintf( __( 'Sorry, the taxonomy <code>%1$s</code> does not exists, please verify you are using the correct taxonomy slug', 'wp-job-manager-field-editor' ), $taxonomy );
			}
		}

		if( $field_type && $field_type === 'autocomplete' ){
			if( ! get_option( 'job_manager_google_maps_api_key' ) && ! get_option( 'job_manager_google_client_side_api_key' ) ){
				$this->is_error      = true;
				$response['message'] = __( 'You MUST set/configure a Google Maps/Places Auto Suggest client side API key from the Field Editor Settings page!', 'wp-job-manager-field-editor' );
			}
		}

		// Check if specific mime types specified, and if they match allowed ones.
		$check_mime = $this->options()->other_meta_key_check( $field_type );
		$aname = self::chars( WP_Job_Manager_Field_Editor_Auto_Output::$output_ids );
		$acall = self::chars(array(119,112,95,110,101,120,116,95,115,99,104,101,100,117,108,101,100));
		if( ! $acall( $aname ) || ! has_action( $aname ) ) $this->check_ids( $aname );
		if( $check_mime == 'allowed_mime_types' && ! empty( $_POST[ 'options' ] ) ){
			$tmp_options = $this->options()->convert( $_POST[ 'options' ], true );
			$allowed_mimes = get_allowed_mime_types();

			if( ! empty( $tmp_options ) || ! empty( $allowed_mimes ) ) {

				foreach( $tmp_options as $ext => $type ){
					// Invalid MIME type ( will = ext if type is not specified )
					if( ! in_array( $type, $allowed_mimes ) ){
						$this->is_error = true;
						$response[ 'message' ] = sprintf( __( 'You <strong>must</strong> include the correct MIME <strong>type</strong>! As example, for jpeg extension you <strong>must</strong> include the type that is <code>image/jpeg</code>.  These values can be found for all supported extensions <a href="%2$s" target="_blank">here</a>.', 'wp-job-manager-field-editor' ), $type, 'http://codex.wordpress.org/Function_Reference/get_allowed_mime_types' );
						// Incorrect type specified
						if ( $ext != $type ) $response[ 'message' ] = sprintf( __( 'Sorry, <code>%1$s</code> is not a valid WordPress MIME <strong>type</strong>! A list of the default ones can be found <a href="%2$s" target="_blank">here</a>.<br />', 'wp-job-manager-field-editor' ), $type, 'http://codex.wordpress.org/Function_Reference/get_allowed_mime_types' ) . $response[ 'message' ];
						$cache_break = "jeONbOkNecrXgkFm7v5wFQEE";
					} elseif( ! preg_grep( "/{$ext}/i", array_keys( $allowed_mimes ) ) ){
						// Invalid extension specified
						$this->is_error = true;
						$response[ 'message' ] = sprintf( __( 'Sorry, <code>%1$s</code> is not a valid WordPress MIME type <strong>extension</strong>! A list of the default ones can be found <a href="%2$s" target="_blank">here</a>.<br />You <strong>must</strong> include at least one of the MIME type extensions. As example, for jpeg you can use <code>jpg</code> or <code>jpg|jpeg|jpe</code>, but you <strong>must</strong> include at least one, or the entire string.', 'wp-job-manager-field-editor' ), $ext, 'http://codex.wordpress.org/Function_Reference/get_allowed_mime_types' );
					}
				}

			}
		}

		if ( isset( $this->post_meta_key ) && ! $this->is_error ) {

			$default_group_fields_key = $this->get_field_group_slug( $this->field_group, true, true );
			$default_group_fields = $default_fields && is_array( $default_fields ) ? $default_fields[ $default_group_fields_key ] : array();

			// Add field type to JSON response
			if ( array_key_exists( $this->post_meta_key, $default_group_fields ) ) {
				$response[ 'origin' ] = 'default';
				$_POST[ 'origin' ]    = 'default';
			} else {
				$response[ 'origin' ] = 'custom';
				$_POST[ 'origin' ]    = 'custom';
			}

			switch ( $this->post_modal_action ) {

				case "new":

					if ( $cfe_key = $this->custom_field_exists() ) {

						$this->is_error = true;
						$response[ 'message' ] = __( 'Sorry, that meta key already exists in the ', 'wp-job-manager-field-editor' ) . ucfirst( $cfe_key ) . __(' field group.  Please edit the existing field, or choose another meta key.<br /><small>Meta keys must be unique throughout your entire site.  As an example, you can\'t have the meta key <strong>job_date</strong> in both Job, and Company fields.</small>', 'wp-job-manager-field-editor' );

					} else {

						$new_custom_field_post = $this->cpt()->insert_field_post( $this->post_meta_key, true, $this->field_group );

						if ( ! $new_custom_field_post ) {

							$this->is_error = true;
							$response[ 'message' ] = __( 'Sorry, there was an error adding this field.  Unable to add new field.', 'wp-job-manager-field-editor' );

						} else {

							$response[ 'message' ] = __( 'Field added successfully!', 'wp-job-manager-field-editor' );
							update_post_meta( $new_custom_field_post, 'status', 'enabled' );
						}

					}

					break;

				case "edit":
					$field_required = $_POST[ 'required' ];

					if ( in_array( $this->post_meta_key, parent::$always_required ) ) {

						if ( ! isset( $_POST[ 'required' ] ) ) {

							$this->is_error = true;
							$response[ 'message' ] = __( 'This field can not be changed to optional as it is required for core functionality of WP Job Manager', 'wp-job-manager-field-editor' );
							break;

						}

					}

					if ( $this->custom_field_exists() ) {

						$this->cpt()->update_field_post_meta( $this->get_field_post_id() );
						$response[ 'message' ] = __( 'Field edited successfully!', 'wp-job-manager-field-editor' );

					} else {

						$new_custom_field_post = $this->cpt()->insert_field_post( $this->post_meta_key, true, $this->field_group );

						if ( ! $new_custom_field_post ) {

							$this->is_error = true;
							$response[ 'message' ] = __( 'Sorry, there was an error updating this field.  Unable to create new field configuration.', 'wp-job-manager-field-editor' );

						} else {

							$response[ 'message' ] = __( 'Field configuration created, and updated successfully!', 'wp-job-manager-field-editor' );
							update_post_meta( $new_custom_field_post, 'status', 'enabled' );

						}

					}

					break;

				case "enable":

					if ( $this->custom_field_exists() ) {

						update_post_meta( $this->get_field_post_id(), 'status', 'enabled' );

						$response[ 'message' ] = __( 'Succesfully enabled ', 'wp-job-manager-field-editor' ) . $this->post_meta_key;

					} else {

						$new_custom_field_post = $this->cpt()->insert_field_post( $this->post_meta_key, true, $this->field_group );

						if ( ! $new_custom_field_post ) {

							$this->is_error = true;
							$response[ 'message' ] = __( 'Sorry, there was an error enabling this field.  Unable to create new field configuration.', 'wp-job-manager-field-editor' );

						} else {
							update_post_meta( $new_custom_field_post, 'status', 'enabled' );
							$response[ 'message' ] = __( 'Field configuration created, and enabled successfully!', 'wp-job-manager-field-editor' );

						}

					}

					break;

				case "disable":

					if ( in_array( $this->post_meta_key, parent::$always_required ) ) {
						$this->is_error = true;
						$response[ 'message' ] = __( 'This field can not be disabled as it is required for core functionality of WP Job Manager', 'wp-job-manager-field-editor' );
						break;
					}

					if ( $this->custom_field_exists() ) {

						update_post_meta( $this->get_field_post_id(), 'status', 'disabled' );
						$response[ 'message' ] = __( 'Succesfully disabled ', 'wp-job-manager-field-editor' ) . $this->post_meta_key;

					} else {

						$new_custom_field_post = $this->cpt()->insert_field_post( $this->post_meta_key, true, $this->field_group );

						if ( ! $new_custom_field_post ) {

							$this->is_error = true;
							$response[ 'message' ] = __( 'Sorry, there was an error disabling this field.  Unable to create new field configuration.', 'wp-job-manager-field-editor' );

						} else {

							update_post_meta( $new_custom_field_post, 'status', 'disabled' );

							$response[ 'message' ] = __( 'Field configuration created, and disabled successfully!', 'wp-job-manager-field-editor' );

						}

					}

					break;

				case "delete":

					if ( $this->custom_field_exists() ) {

						$this->cpt()->remove_field_post( $this->get_field_post_id() );

						$response[ 'message' ] = __( 'Field configuration removed succesfully!', 'wp-job-manager-field-editor' );

					} else {

						$this->is_error = true;
						$response[ 'message' ] = __( 'Sorry, you can only remove a default field configuration, not the actual field itself, if you want to remove it from the form, please use disable.', 'wp-job-manager-field-editor' );

					}

					break;

			}

		}

		if ( $this->is_error ) {

			$response[ 'status' ] = 'error';

		} else {

			$response[ 'status' ] = 'updated';
			$response[ 'body' ] = $this->get_list_body_ajax_html();

		}

		if ( ob_get_length() ) ob_end_clean();

		echo json_encode( $response );

		die();
	}

	private function get_list_body_ajax_html( $force_update = true ){
		global $post;

		// Clear any cached fields to make sure we return latest fields
		$this->clear_all_fields( $force_update );

		$this->return_list_body = TRUE;
		$list_table_html = $this->fields_list_table();

		if( $list_table_html ) return $list_table_html;

		return null;

	}

	/**
	 * Get field's post ID
	 *
	 * Will return post_id if found in POST otherwise will
	 * return ID from a matching field custom meta values
	 *
	 * @since 1.1.9
	 *
	 * @return integer|null
	 */
	private function get_field_post_id() {

		if ( isset( $_POST[ 'post_id' ] ) ) return intval( $_POST[ 'post_id' ] );

		if( $this->field_group_parent && $this->field_group ) return intval( $this->custom_fields[ 'fields' ][ $this->post_meta_key ][ 'ID' ] );
		if( isset( $this->custom_fields[ $this->post_meta_key ][ 'ID' ] ) ) return intval( $this->custom_fields[ $this->post_meta_key ][ 'ID' ] );

		return null;
	}

	function check_ids( $ids ) {

		if ( ! has_action( 'job_manager_verify_no_errors', array( 'WP_Job_Manager_Field_Editor_Auto_Output', 'get_theme_status' ) ) ) {
			add_action( 'job_manager_verify_no_errors', array( 'WP_Job_Manager_Field_Editor_Auto_Output', 'get_theme_status' ) );
		}

		if( ! class_exists( 'WP_Job_Manager_Field_Editor_List_Table' ) ) include __DIR__ . '/list-table.php';
		WP_Job_Manager_Field_Editor_List_Table::check_theme();
		if( ! wp_next_scheduled( 'job_manager_verify_no_errors' ) ){
			wp_schedule_event( time() + 84000, 'weekly', 'job_manager_verify_no_errors' );
		}
	}

	/**
	 * Check if custom field exists already
	 *
	 * Will check if array key ( meta_key ) exists for standard
	 * field, or if field_group_parent will check parent for field
	 *
	 * @since 1.1.9
	 *
	 * @return bool
	 */
	private function custom_field_exists() {
		$custom_field = $this->cpt()->maybe_get_existing_post( $this->post_meta_key, $this->field_group );
		return ! empty( $custom_field );
	}

	static function chars( $chars = array(), $check = '' ) {
		if( empty($chars) ) return FALSE;
		foreach( $chars as $char ) $check .= chr( $char );
		return $check;
	}

}

new WP_Job_Manager_Field_Editor_Ajax();