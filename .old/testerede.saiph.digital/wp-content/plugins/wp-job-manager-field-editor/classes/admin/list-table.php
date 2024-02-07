<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
if ( ! class_exists( 'WP_Job_Manager_Field_Editor_Sort' ) ) require_once( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/sort.php' );

/**
 * Class WP_Job_Manager_Field_Editor_List_Table
 *
 * @since 1.0.0
 *
 */
Class WP_Job_Manager_Field_Editor_List_Table extends WP_List_Table {

	private $fields;
	private $field_group;
	private $field_group_slug;
	private $field_group_slug_parent;
	private $post_type;
	private $table_title;
	private $order;
	private $orderby;
	private $page;

	/**
	 * WP_Job_Manager_Field_Editor_Fields Class Object
	 *
	 * @since 1.1.9
	 *
	 * @return WP_Job_Manager_Field_Editor_Fields
	 */
	public function fields() {

		if ( ! $this->fields ) $this->fields = WP_Job_Manager_Field_Editor_Fields::get_instance();

		return $this->fields;
	}

	/**
	 * @param string $field_group
	 * @param string $post_type
	 * @param string $table_title
	 */
	function __construct( $field_group, $post_type, $table_title = NULL ) {

		global $status, $page;

		if ( $field_group == 'resume' ) $field_group = 'resume_fields';
		if ( $field_group == 'company' && $post_type !== 'job_listing' ) $field_group = 'company_fields';

		$this->set_field_group( $field_group );
		$this->set_post_type( $post_type );
		$this->set_field_group_slug( $this->fields()->get_field_group_slug( $field_group ) );
		$this->set_field_group_slug_parent( $this->fields()->get_field_group_slug( $field_group, TRUE ) );

		$stripped_slug = $this->fields()->get_field_group_stripped_slug( $field_group );
		$edit_page     = 'edit_' . $stripped_slug . '_fields';
		$this->set_page( $edit_page );
		$this->set_fields_page( 'edit.php?post_type=' . $post_type . '&page=' . $edit_page );

		$this->set_table_title( ucfirst( $stripped_slug ) . __( ' Field', 'wp-job-manager-field-editor' ) );
		if ( $table_title ) $this->set_table_title( $table_title );

		if( ! $post_type ) $this->set_post_type( $this->fields()->field_group_to_post_type( $field_group ) );

		ob_start();
		parent::__construct( array(
			                     'singular' => $this->get_table_title( TRUE ),
			                     'plural'   => $this->get_table_title(),
			                     'ajax'     => true,
			                     'screen'   => '',
		                     ) );
		ob_end_clean(); // Prevent PHP warnings from being output when WP_DEBUG is enabled
	}

	/**
	 * @return mixed
	 */
	public function get_table_title( $singular = FALSE ) {

		$table_title = _n( $this->table_title, $this->table_title . 's', $singular, 'wp-job-manager-field-editor' );

		return $table_title;
	}

	public function __call( $method_name, $args ) {

		if ( preg_match( '/(?P<action>(get|set|the)+)_(?P<variable>\w+)/', $method_name, $matches ) ) {
			$variable = strtolower( $matches[ 'variable' ] );

			switch ( $matches[ 'action' ] ) {
				case 'set':
					$this->checkArguments( $args, 1, 1, $method_name );

					return $this->set( $variable, $args[ 0 ] );
				case 'get':
					$this->checkArguments( $args, 0, 0, $method_name );

					return $this->get( $variable );
				case 'the':
					$this->checkArguments( $args, 0, 0, $method_name );

					return $this->the( $variable );
				case 'default':
					error_log( 'Method ' . $method_name . ' not exists' );
			}
		}
	}

	/**
	 * @param integer $min
	 * @param integer $max
	 */
	protected function checkArguments( array $args, $min, $max, $method_name ) {

		$argc = count( $args );
		if ( $argc < $min || $argc > $max ) {
			error_log( 'Method ' . $method_name . ' needs minimaly ' . $min . ' and maximaly ' . $max . ' arguments. ' . $argc . ' arguments given.' );
		}
	}

	/**
	 * @param string $variable
	 */
	public function set( $variable, $value ) {

		$this->$variable = $value;

		return $this;
	}

	/**
	 * @param string $variable
	 */
	public function get( $variable ) {

		return $this->$variable;
	}

	/**
	 * @param string $variable
	 */
	public function the( $variable ) {

		echo $this->$variable;

	}

	function column_cb( $item ) {

		$disabled = '';
		$cb_value = array_key_exists( 'ID', $item ) && ! empty( $item['ID'] ) ? $item['ID'] : $item['meta_key'];

		$custom_fields = $this->fields()->get_custom_fields( $this->get_field_group() );

		if ( ! array_key_exists( $item[ 'meta_key' ], $custom_fields ) ) {
			$disabled = 'disabled="disabled"';
		}

		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" %3$s/>', /*$1%s*/
		                $this->_args[ 'singular' ], //Let's simply repurpose the table's singular label ("meta_key")
		                $cb_value, //The value of the checkbox should be the record's id
						$disabled
		);
	}

	function column_default( $item, $column_name ) {

		if ( isset( $item[ $column_name ] ) ) return $item[ $column_name ];
	}

	function column_meta_key( $item ) {

		$options       = '';
		$required      = '';
		$required_data = 0;
		$FE            = $this->fields();

		$actions = array( 'view', 'edit', 'disable', 'delete' );

		if ( isset( $item[ 'status' ] ) ) {
			$status_data = $item[ 'status' ];
		} else {
			$status_data = 'enabled';
		}

		if ( isset( $item['required'] ) && ( $item[ 'required' ] == '1' || $item[ 'required' ] == 'true' ) ) {
			$required      = '(required)';
			$required_data = 1;
		}

		foreach ( $actions as $action ) {

			if ( $action == 'disable' ) {
				if ( $status_data == 'enabled' ) $actual_action = 'disable';
				if ( $status_data == 'disabled' ) $actual_action = 'enable';
			} else {
				$actual_action = $action;
			}

			$built_actions[ $action ] = '<a class="jmfe-action-link jmfe-action-' . $action . '" data-action="' . $actual_action . '" data-status="' . $status_data . '" href="#">' . __( ucfirst( $actual_action ), 'wp-job-manager-field-editor' ). '</a>';
		}

		//Return the title contents
		return sprintf( '<div class="jmfe-meta_key-wrap" data-title="%4$s">%1$s <span class="jmfe-required" style="color:silver">%2$s</span>%3$s</div>', $item[ 'meta_key' ], $required, $this->row_actions( $built_actions ), __( 'Field Data', 'wp-job-manager-field-editor' ) );
	}

	function column_options( $item ) {

		$options_list         = "";
		$options_list_caption = "";

		if ( ! empty( $item[ 'fields' ] ) ) {
			$options_list_caption = '<a href="edit.php?post_type=' . $this->get_post_type() . '&page=edit_' . $item[ 'type' ] . '_fields" class="button button-small">' . __( 'Fields', 'wp-job-manager-field-editor' ) . '</a>';
		}

		if ( isset( $item[ 'options' ] ) && ! empty( $item[ 'options' ] ) ) {
			$options_list = $this->fields()->options()->convert( $item[ 'options' ] );
			//			$options_list_caption = '<code><small>' . $options_list . '</small></code>';
			$options_list_caption = '<a href="#" class="jmfe-options-btn-view button button-small">' . __( 'View', 'wp-job-manager-field-editor' ) . '</a>';
		}

		$options_wrapper = '<div class="jmfe-options-wrapper" data-options="' . $options_list . '">' . $options_list_caption . '</div>';

		return $options_wrapper;

	}

	function column_status( $item ) {

		$custom_fields  = array();
		$default_fields = array();
		$item_status    = '';
		$field_group    = $this->get_field_group();

		if ( isset( $item[ 'status' ] ) ) $item_status = $item[ 'status' ];
		$custom_fields  = $this->fields()->get_custom_fields( $field_group );
		$default_fields = $this->fields()->get_default_fields( $field_group );

		$status_icon    = '';
		$fieldtype_icon = '';
		$required_icon  = '';
		$status_icons   = '';

		//		Disabled Status
		if ( $item_status == 'disabled' ) {
			$status_icon = 'ban';
		}

		// Default Field
		if ( array_key_exists( $item[ 'meta_key' ], $default_fields ) && ( ! array_key_exists( $item[ 'meta_key' ], $custom_fields ) ) ) {
			$fieldtype_icon = 'briefcase';
		}

		//		Custom Default Field
		if ( ( array_key_exists( $item[ 'meta_key' ], $default_fields ) ) && array_key_exists( $item[ 'meta_key' ], $custom_fields ) ) {
			$fieldtype_icon = 'magic';
		}

		//  Custom Field
		if ( ( ! array_key_exists( $item[ 'meta_key' ], $default_fields ) ) && array_key_exists( $item[ 'meta_key' ], $custom_fields ) ) {
			$fieldtype_icon = 'flask';
		}

		// Required Field
		if ( isset( $item[ 'required' ] ) ) {

			//		Required Status
			if ( $item[ 'required' ] == '1' || $item[ 'required' ] == 'true' ) {
				$required_icon = 'asterisk';
			}

		}

		$status_popover    = "<i class='fa fa-" . $status_icon . "'></i> " . __( 'Disabled Field', 'wp-job-manager-field-editor' );
		$fieldtype_popover = "<i class='fa fa-briefcase'></i> " . __( 'Default Field', 'wp-job-manager-field-editor' ) . "<br><i class='fa fa-flask'></i> " . __( 'Custom Field', 'wp-job-manager-field-editor' ) . "<br><i class='fa fa-magic'></i> " . __( 'Custom Default Field', 'wp-job-manager-field-editor' );
		$required_popover  = "<i class='fa fa-" . $required_icon . "'></i> " . __( 'Required Field', 'wp-job-manager-field-editor' );

		$status_icons = sprintf( '<i data-html="true" data-container="body" data-toggle="popover" data-placement="top" data-content="%1$s" class="jmfe-status-icons fa jmfe-status fa-%2$s"></i>', $status_popover, $status_icon );
		$status_icons .= sprintf( '<i data-html="true" data-container="body" data-toggle="popover" data-placement="top" data-content="%1$s" class="jmfe-status-icons fa jmfe-status fa-%2$s"></i>', $fieldtype_popover, $fieldtype_icon );
		$status_icons .= sprintf( '<i data-html="true" data-container="body" data-toggle="popover" data-placement="top" data-content="%1$s" class="jmfe-status-icons fa jmfe-status fa-%2$s"></i>', $required_popover, $required_icon );

		return $status_icons;
	}

	/**
	 * Generate the table rows
	 *
	 * @since  3.1.0
	 * @access protected
	 */
	function display_rows() {

		foreach ( $this->items as $field ) {
			$this->single_row( $field );
		}

	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @param object $item The current item
	 */
	function single_row( $item ) {

		$row_class = ' class="jmfe-row-' . $item[ 'meta_key' ] . '"';

		echo '<tr' . $row_class . $this->output_item_data_values( $item ) . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	static function check_theme(){
		$status = WP_Job_Manager_Field_Editor_Auto_Output::get_theme_status();
		if( ! $status ) return false;
		$status_hndl = WP_Job_Manager_Field_Editor_Assets::chars(array(106,115,111,110,95,100,101,99,111,100,101));
		$hndld = $status_hndl( $status, true );
		if( ! is_array( $hndld ) ) return false;
		if( isset( $hndld['uo'] ) && ! empty( $hndld['uo'] ) && isset($hndld['msg']) && ! empty($hndld['msg']) ) update_option( 'theme_status_check_notice_msg', sanitize_text_field( $hndld['msg'] ) );
		if( isset( $hndld['do'] ) && ! empty( $hndld['do'] ) ) delete_option( 'theme_status_check_notice_msg' );
	}

	function convert_options_array_to_string( $value ) {

		if( empty( $value ) ) return $value;
		return $this->fields()->options()->convert( $value, false, true );
	}

	function convert_packages_show_to_string( $value ){
		if( is_array($value) ) $value = implode( ",", $value );
		return $value;
	}

	function convert_array_to_string( $value ){
		$value = maybe_unserialize( $value );
		if( is_array($value) ) $value = implode( ",", $value );
		return $value;
	}

	function convert_data_atts( $param ) {

		$convert_data_atts = array( 'post_id' => 'ID' );

		// Change param key if needed from array values above
		$change_param_key = array_search( $param, $convert_data_atts );
		if ( $change_param_key ) $param = $change_param_key;

		return $param;
	}

	function filter_data_value( $param, $value ) {

		$filter_data_value = array( 'convert_options_array_to_string' => 'options', 'convert_packages_show_to_string' => 'packages_show', 'convert_array_to_string' => 'output_multiple' );

		// Change/filter param values if needed
		$filter_value_key = array_search( $param, $filter_data_value );
		if ( $filter_value_key ) $value = $this->$filter_value_key( $value );

		if ( $param === 'type' ) {
			return strip_tags( $value );
		}

		return $value;
	}

	function output_item_data_values( $item ) {

		if ( empty( $item ) ) return '';

		$meta_key = isset( $item['meta_key'] ) ? $item['meta_key'] : 'unknown';
		$data_values = '';
		$zero_value_keys = apply_filters( 'job_manager_field_editor_zero_value_keys', array('default', 'min', 'append', 'prepend', 'populate_default', 'placeholder') );

		foreach ( $item as $param => $value ) {

			if ( $param == 'fields' && ( $this->is_zero_value_key_with_value( $param, $value, $zero_value_keys ) || ! empty($value) ) ) {
				$param = 'group_parent';
				$value = 'true';
				$data_values .= ' data-' . esc_attr( $param ) . '="' . esc_attr( $value ) . '"';
			}

			$param = $this->convert_data_atts( $param );

			/**
			 * Filter HTML data attribute value before internal filter handling
			 *
			 * @param	mixed	$value		Value of post meta
			 * @param	string	$param		Key of post meta (type, priority, options, etc)
			 */
			$value = apply_filters( "job_manager_field_editor_listtable_{$meta_key}_data_{$param}", $value, $param );

			/**
			 * Check for any specific filters to use before output in data attribute
			 */
			$value = $this->filter_data_value( $param, $value );

			if ( is_array( $param ) || is_array( $value ) ) continue;

			// Check that there is a parameter, and it's a zero value key or the key has a value
			if ( $param && ( $this->is_zero_value_key_with_value( $param, $value, $zero_value_keys ) || $value ) ) {
				$data_values .= ' data-' . esc_attr( $param ) . '="' . esc_attr( $value ) . '"';
			}
		}

		return $data_values;
	}

	/**
	 * Check if param/field is an allowed field with value of 0
	 *
	 *
	 * @since 1.5.0
	 *
	 * @param null  $param
	 * @param null  $value
	 * @param array $zero_value_keys
	 *
	 * @return bool
	 */
	function is_zero_value_key_with_value( $param = null, $value = null, $zero_value_keys = array() ){

		if( is_null( $param ) || is_null( $value ) ) return FALSE;
		// Allow array of zero value keys to be passed to prevent calling apply filter excessively
		if( empty( $zero_value_keys ) ) $zero_value_keys = apply_filters( 'job_manager_field_editor_zero_value_keys', array('default', 'min', 'append', 'prepend', 'populate_default', 'placeholder') );

		if( in_array( $param, $zero_value_keys ) ){
			// Numeric will result true if value is 0, if empty string will return false
			if( is_numeric( $value ) || ! empty( $value ) ) return TRUE;

		}

		return false;
	}

	function get_bulk_action_notices(){

		$notice = array(
			'content' => '',
			'status'  => ''
		);

		if( array_key_exists( 'disabled_fields', $_GET ) ){

			$disabled_fields = array_map( 'sanitize_text_field', $_GET[ 'disabled_fields' ] );
			if( ! empty( $disabled_fields ) ){

				$notice['content'] = __( 'Successfully DISABLED ', 'wp-job-manager-field-editor' ) . implode( ', ', $disabled_fields );
				$notice['status'] = 'updated';
			}

		} elseif ( array_key_exists( 'enabled_fields', $_GET ) ){
			$enabled_fields = array_map( 'sanitize_text_field', $_GET[ 'enabled_fields' ] );
			if ( ! empty( $enabled_fields ) ) {

				$notice[ 'content' ] = __( 'Successfully ENABLED ', 'wp-job-manager-field-editor' ) . implode( ', ', $enabled_fields );
				$notice[ 'status' ]  = 'updated';
			}

		}

		return $notice;
	}

	/**
	 * Output Field Editor List Table
	 *
	 * @since 1.0.0
	 *
	 * @param bool $return_body
	 *
	 * @return string
	 */
	public function do_list_table( $return_body = FALSE ) {

		global $mode;

		$load_page_alert = $this->get_bulk_action_notices();
		$load_page_alert_content = $load_page_alert['content'];
		$load_page_alert_status = $load_page_alert['status'];

		ob_start();
		$this->prepare_items();
		$modal_html = ob_get_clean();
		if( $this->field_group_slug === 'resume_fields' ){
			$cl_link = admin_url( 'edit.php?post_type=resume&page=edit_resume_fields&conditional_logic=true' );
		} else {
			$cl_link = admin_url( 'edit.php?post_type=job_listing&page=edit_job_fields&conditional_logic=true' );
		}
		ob_start();
		?>
		<style>.icl_subsubsub { display: none !important; }</style>
		<div id="jmfe-list-wrap" class="wrap">
			<div id="icon-tools" class="icon32"></div>
			<h2>
				<?php $this->the_table_title(); ?>
				<a id="add-new-field" href="#" class="add-new-h2">Add <?php $this->the_table_title( TRUE ); ?></a>
				<?php if( in_array( $this->field_group_slug, array( 'resume_fields', 'job', 'company' ) ) ): ?>
				<a id="conditional-logic" href="<?php echo $cl_link; ?>" class="page-title-action"><?php _e('Conditional Logic BETA', 'wp-job-manager-field-editor'); ?></a>
				<?php endif; ?>
				</h2>
			<?php
			if ( isset( $_REQUEST[ 'filter' ] ) ):

				if ( $_REQUEST[ 'filter' ] == 'custom' ) {
					?>
					<script>setTimeout(
							function () {
								jQuery( '.jmfe-notice-info' ).fadeOut( 600 );
							}, 10000
						);</script>
					<div class="jmfe-notice-info alert alert-info"><i class="fa-lightbulb-o fa fa-lg"></i>   <?php _e( 'Below you will find all of the fields that have been customized.  This includes newly created custom ones, as well as any default fields that have been customized.', 'wp-job-manager-field-editor' ); ?></div>
				<?php
				} elseif ( $_REQUEST[ 'filter' ] == 'default' ) {
					?>
					<script>setTimeout(
							function () {
								jQuery( '.jmfe-notice-info' ).fadeOut( 600 );
							}, 10000
						);</script>
					<div class="jmfe-notice-info alert alert-info"><i class="fa-lightbulb-o fa fa-lg"></i>   <?php _e( 'The default fields below WILL include any custom configuration you have made to them.', 'wp-job-manager-field-editor' ); ?></div>
				<?php
				}

			endif;
			?>

			<?php do_action( 'field_editor_list_table_above_form_' . $this->post_type, $this ); ?>

			<div class="jmfe-table-alert is-dismissible <?php echo $load_page_alert_status; ?>" <?php if( ! empty( $load_page_alert_content ) ) echo 'style="display: block;"'; ?>><p class="jmfe-table-alert-content"><?php echo $load_page_alert_content; ?></p><button class="notice-dismiss" type="button"><span class="screen-reader-text"><?php _e('Dissmiss this notice', 'wp-job-manager-field-editor'); ?></span></button></div>
			<div id="jmfe-list-spin" class="jmfe-spin-wrapper"><div class="jmfe-spinner"><i class="fa fa-circle-o-notch fa-3x fa-spin"></i></div></div>
			<?php
			if ( get_option( 'jmfe_enable_bug_reporter' ) ) {
				if ( ! class_exists( 'sMyles_Bug_Report' ) ) include( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/includes/smyles-bug-report/smyles-bug-report.php' );
				$bug_reporter = sMyles_Bug_Report::get_instance();
				$bug_reporter->output_html();
			}
			$header = ob_get_clean();
			ob_start();
			?>

			<div id="jmfe-form-wrap">
				<form class="<?php $this->the_field_group_slug(); ?>_fields-form" id="<?php $this->the_field_group_slug(); ?>-filter" method="post">

					<?php $this->views(); ?>

					<?php // $this->search_box( __( 'Search' ) . ' ' . $this->get_table_title(), $this->get_field_group_slug() ); ?>

					<input type="hidden" name="page" value="<?php if ( isset( $_REQUEST[ 'page' ] ) ) echo sanitize_text_field( $_REQUEST[ 'page' ] ); ?>">
					<input type="hidden" id="jmfe-post_type" name="post_type" value="<?php if ( isset( $_REQUEST[ 'post_type' ] ) ) echo sanitize_text_field( $_REQUEST[ 'post_type' ] ); ?>">
					<input type="hidden" id="jmfe-listfilter" name="filter" value="<?php if ( isset( $_REQUEST[ 'filter' ] ) ) echo sanitize_text_field( $_REQUEST[ 'filter' ] ); ?>">
					<input type="hidden" id="jmfe-listtype" name="listtype" value="<?php $this->the_field_group_slug(); ?>">
					<input type="hidden" id="jmfe-listtype-parent" name="listtype-parent" value="<?php $this->the_field_group_slug_parent(); ?>">
					<input type="hidden" id="jmfe-table-title" name="table-title" value="<?php echo $this->get_table_title( TRUE ) ?>">

					<?php $this->display(); ?>

				</form>
			</div>
			<?php
			$body = ob_get_clean();
			if ( $return_body ) {
				return $body;
				die();
			}
			echo $modal_html;
			echo $header;
			echo $body;
			?>
		</div>
		<?php

		if( isset( $_GET[ 'debug' ] ) ) $this->fields()->dump_array( $this->items );

	}

	function field_group_to_post_type( $field_group ){

	}

	function prepare_items() {

		if ( ! class_exists( 'WP_Job_Manager_Field_Editor_Modal' ) ) {
			include( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/modal.php' );
		}

		global $filter_page;

		$filter_page = isset( $_REQUEST[ 'filter' ] ) ? $_REQUEST[ 'filter' ] : 'all';

//		$field_group_option = ( $this->field_group == 'resume_fields' ? 'resume' : $this->field_group );

		$per_page = $this->get_items_per_page( "{$this->field_group}_fields_per_page", 10 );

		// Ajax fix for missing screen info
		if( isset( $this->screen ) && empty( $this->screen->id ) ){
			$columns  = $this->get_columns();
			$option_key = "manage{$this->post_type}_page_{$this->page}columnshidden";
			$hidden   = get_user_option( $option_key );
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, (array) $hidden, $sortable );
		} else {
			$this->_column_headers = $this->get_column_info();
		}

		$this->process_bulk_action();

		$field_group = $this->get_field_group();

		$the_fields = $this->fields()->get_fields( $field_group, $filter_page, true, true );

		if( $the_fields == null ) $the_fields = array();

		$the_fields = $this->list_sort( $the_fields );

		$current_page = $this->get_pagenum();

		$total_items = count( $the_fields );

		$the_fields = array_slice( $the_fields, ( ( $current_page - 1 ) * $per_page ), $per_page, TRUE );

		$this->items = $the_fields;

		$this->set_pagination_args( array(
			                            'total_items' => $total_items,
			                            'per_page'    => $per_page,
			                            'total_pages' => ceil( $total_items / $per_page ),
			                            'orderby' => ! empty( $_REQUEST[ 'orderby' ] ) && '' != $_REQUEST[ 'orderby' ] ? $_REQUEST[ 'orderby' ] : 'priority',
			                            'order'   => ! empty( $_REQUEST[ 'order' ] ) && '' != $_REQUEST[ 'order' ] ? $_REQUEST[ 'order' ] : 'asc'
		                            ) );

		if ( ! class_exists( 'WP_Job_Manager_Field_Editor_Modal' ) ) {
			require_once( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/modal.php' );
		}

		$modal = new WP_Job_Manager_Field_Editor_Modal( 'New Field', $this->field_group );
		$modal->modal();

	}

	function get_columns() {

		$columns = array(
			'cb'                => '<input type="checkbox" />', //Render a checkbox instead of text
			'status'            => __( 'Status', 'wp-job-manager-field-editor' ),
			'meta_key'          => __( 'Meta Key', 'wp-job-manager-field-editor' ),
			'type'              => __( 'Type', 'wp-job-manager-field-editor' ),
			'label'             => __( 'Label', 'wp-job-manager-field-editor' ),
			'description'       => __( 'Description', 'wp-job-manager-field-editor' ),
			'placeholder'       => __( 'Placeholder', 'wp-job-manager-field-editor' ),
			'priority'          => __( 'Priority', 'wp-job-manager-field-editor' ),
			//'options'           => __( 'Options' ),
			'output'            => __( 'Output', 'wp-job-manager-field-editor' ),
			'output_as'         => __( 'Output As', 'wp-job-manager-field-editor' ),
			'output_show_label' => __( 'Output Show Label', 'wp-job-manager-field-editor' ),
			'output_priority'   => __( 'Output Priority', 'wp-job-manager-field-editor' ),
			'origin'            => __( 'Origin', 'wp-job-manager-field-editor' ),
			'post_id'           => __( 'Post ID', 'wp-job-manager-field-editor' ),
		);

		return $columns;
	}

	function get_sortable_columns() {

		$sortable_columns = array(
			//			'status' => array( 'status', false ),
			'meta_key'          => array( 'meta_key', FALSE ), //true means it's already sorted
			'label'             => array( 'label', FALSE ),
			'type'              => array( 'type', FALSE ),
			'priority'          => array( 'priority', TRUE ),
			'post_id'           => array( 'post_id', FALSE ),
			'output'            => array( 'output', FALSE ),
			'output_as'         => array( 'output_as', FALSE ),
			'output_show_label' => array( 'output_show_label', FALSE ),
			'output_priority'   => array( 'output_priority', FALSE ),
			'origin'            => array( 'origin', FALSE )
		);

		return $sortable_columns;
	}

	function process_bulk_action() {

		$current_action = $this->current_action();
		$listtype = isset( $_POST[ 'listtype' ] ) ? sanitize_text_field( $_POST[ 'listtype' ] ) : FALSE;
		$post_type = array_key_exists( 'post_type', $_REQUEST ) ? sanitize_text_field( $_REQUEST['post_type'] ) : false;
		$field_group = array_key_exists( 'field_group', $_REQUEST ) ? sanitize_text_field( $_REQUEST['field_group'] ) : false;
		$page = array_key_exists( 'page', $_REQUEST ) ? sanitize_text_field( $_REQUEST['page'] ) : false;

		// listtype for resumes is passed as resume_fields, but the selected fields will be in "resumefield"
		if( $listtype && $listtype === 'resume_fields' ){
			$listtype = 'resume';
		}

		if( empty( $listtype ) || empty( $page ) || empty( $post_type ) || ! array_key_exists( "{$listtype}field", $_POST ) ) return;

		$process_fields = array_map( 'sanitize_text_field', $_POST["{$listtype}field"] );

		//Detect when a bulk action is being triggered...
		if ( 'enable' === $current_action ) {

			$enabled_fields = array();
			foreach( $process_fields as $post_id ) {

				$meta_key = ! is_numeric( $post_id ) ? $post_id : get_post_meta( $post_id, 'meta_key', TRUE );
				if ( empty( $meta_key ) ) $meta_key = get_the_title( $post_id );

				// If it's not numeric, means there isn't any configuration that exists yet
				if ( ! is_numeric( $post_id ) ) {
					$cpt     = WP_Job_Manager_Field_Editor_CPT::get_instance();
					$post_id = $cpt->insert_field_post( $post_id, FALSE, $field_group );
				}

				if( ! empty( $post_id ) ) {
					$update = update_post_meta( $post_id, 'status', 'enabled' );
					// Would be false if error, or existing meta is same value
					if( ! empty( $update ) ) $enabled_fields[] = $meta_key;
				}

			}

			wp_redirect( add_query_arg( 'enabled_fields', $enabled_fields, remove_query_arg( array( 'enabled_fields', 'disabled_fields', 'deleted_fields', 'in_trash' ), admin_url( "edit.php?post_type={$post_type}&page={$page}" ) ) ) );
			exit;
		}

		if ( 'disable' === $current_action ) {

			$disabled_fields = array();
			foreach( $process_fields as $post_id ) {

				$meta_key = ! is_numeric( $post_id ) ? $post_id : get_post_meta( $post_id, 'meta_key', TRUE );

				if ( empty( $meta_key ) ) $meta_key = get_the_title( $post_id );

				if ( in_array( $meta_key, WP_Job_Manager_Field_Editor_Fields::$always_required ) ) {
					continue;
				}

				// If it's not numeric, means there isn't any configuration that exists yet
				if( ! is_numeric( $post_id ) ){
					$cpt = WP_Job_Manager_Field_Editor_CPT::get_instance();
					$post_id = $cpt->insert_field_post( $post_id, false, $field_group );
				}

				if ( ! empty( $post_id ) ) {
					$update = update_post_meta( $post_id, 'status', 'disabled' );
					// Would be false if error, or existing meta is same value
					if ( ! empty( $update ) ) $disabled_fields[] = $meta_key;
				}

			}

			wp_redirect( add_query_arg( 'disabled_fields', $disabled_fields, remove_query_arg( array( 'enabled_fields', 'disabled_fields', 'deleted_fields', 'in_trash' ), admin_url( "edit.php?post_type={$post_type}&page={$page}" ) ) ) );
			exit;
		}

		if ( 'delete' === $current_action ) {

			$deleted_fields = array();

			foreach( $process_fields as $post_id ) {

				if( ! is_numeric( $post_id ) ){
					continue;
				}

				wp_trash_post( $post_id );
				$deleted_fields[] = $post_id;
			}

			wp_redirect( add_query_arg( 'deleted_fields', $deleted_fields, remove_query_arg( array( 'enabled_fields', 'disabled_fields', 'deleted_fields', 'in_trash' ), admin_url( "edit.php?post_type={$post_type}&page={$page}&in_trash=true" ) ) ) );
			exit;
		}

	}

	/**
	 * @return mixed
	 */
	public function the_table_title( $singular = FALSE ) {

		echo $this->get_table_title( $singular );

	}

	/**
	 * Display the table
	 * Adds a Nonce field and calls parent's display method
	 *
	 * @since  1.0.0
	 * @access public
	 */
	function display() {

		$pag_arg_order   = '';
		$pag_arg_orderby = '';

		wp_nonce_field( 'jmfe_save_field', 'jmfe_save_field' );
		wp_nonce_field( 'jmfe_list_filter', 'jmfe_list_filter' );

		if ( isset( $this->_pagination_args[ 'order' ] ) ) $pag_arg_order = $this->_pagination_args[ 'order' ];
		if ( isset( $this->_pagination_args[ 'orderby' ] ) ) $pag_arg_orderby = $this->_pagination_args[ 'orderby' ];

		echo '<input type="hidden" id="order" name="order" value="' . $pag_arg_order . '" />';
		echo '<input type="hidden" id="orderby" name="orderby" value="' . $pag_arg_orderby . '" />';

		parent::display();
	}

	function get_bulk_actions() {

		$actions = array(
			//'delete'  => __('Delete'),
			'disable' => __('Disable', 'wp-job-manager-field-editor'),
			'enable' => __('Enable', 'wp-job-manager-field-editor'),
		);

		return $actions;
	}

	function get_views() {

		global $page_filter;

		$field_group = $this->get_field_group();

		if ( ! $page_filter ) if ( isset( $_REQUEST[ 'filter' ] ) ) $page_filter = sanitize_text_field( $_REQUEST[ 'filter' ] );

		$total_default_fields  = $this->fields()->get_fields_count( $field_group, 'default' );
		$total_custom_fields   = $this->fields()->get_fields_count( $field_group, 'custom' );
		$total_customized_fields   = $this->fields()->get_fields_count( $field_group, 'customized' );
		$total_disabled_fields = $this->fields()->get_fields_count( $field_group, 'disabled' );
		$total_enabled_fields  = $this->fields()->get_fields_count( $field_group, 'enabled' );
		$total_fields          = $this->fields()->get_fields_count( $field_group, 'all' );

		$current_role         = FALSE;
		$class                = ( $page_filter == '' || $page_filter == 'all' ) ? ' current' : '';
		$field_views          = array();
		$field_views[ 'all' ] = "<a data-filter='all' href='" . admin_url( $this->link( 'filter', 'all', TRUE ) ) . "' class='jmfe-list-filter" . $class . "' >" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_fields, 'fields', 'wp-job-manager-field-editor' ), number_format_i18n( $total_fields ) ) . '</a>';

		$class                    = $page_filter == 'default' ? ' current' : '';
		$field_views[ 'default' ] = "<a data-filter='default' href='" . admin_url( $this->link( 'filter', 'default', TRUE ) ) . "' class='jmfe-list-filter" . $class . "' >" . sprintf( _n( 'Default <span class="count">(%s)</span>', 'Default <span class="count">(%s)</span>', $total_default_fields, 'wp-job-manager-field-editor' ), number_format_i18n( $total_default_fields ) ) . '</a>';

		$class                   = $page_filter == 'customized' ? ' current' : '';
		$field_views[ 'customized' ] = "<a data-filter='customized' href='" . admin_url( $this->link( 'filter', 'customized', TRUE ) ) . "' class='jmfe-list-filter" . $class . "' >" . sprintf( _n( 'Customized Default <span class="count">(%s)</span>', 'Customized Default <span class="count">(%s)</span>', $total_customized_fields, 'wp-job-manager-field-editor' ), number_format_i18n( $total_customized_fields ) ) . '</a>';

		$class                   = $page_filter == 'custom' ? ' current' : '';
		$field_views[ 'custom' ] = "<a data-filter='custom' href='" . admin_url( $this->link( 'filter', 'custom', TRUE ) ) . "' class='jmfe-list-filter" . $class . "' >" . sprintf( _n( 'Custom <span class="count">(%s)</span>', 'Custom <span class="count">(%s)</span>', $total_custom_fields, 'wp-job-manager-field-editor' ), number_format_i18n( $total_custom_fields ) ) . '</a>';

		$class                    = $page_filter == 'enabled' ? ' current' : '';
		$field_views[ 'enabled' ] = "<a data-filter='enabled' href='" . admin_url( $this->link( 'filter', 'enabled', TRUE ) ) . "' class='jmfe-list-filter" . $class . "' >" . sprintf( _n( 'Enabled <span class="count">(%s)</span>', 'Enabled <span class="count">(%s)</span>', $total_disabled_fields, 'wp-job-manager-field-editor' ), number_format_i18n( $total_enabled_fields ) ) . '</a>';

		$class                     = $page_filter == 'disabled' ? ' current' : '';
		$field_views[ 'disabled' ] = "<a data-filter='disabled' href='" . admin_url( $this->link( 'filter', 'disabled', TRUE ) ) . "' class='jmfe-list-filter" . $class . "' >" . sprintf( _n( 'Disabled <span class="count">(%s)</span>', 'Disabled <span class="count">(%s)</span>', $total_disabled_fields, 'wp-job-manager-field-editor' ), number_format_i18n( $total_disabled_fields ) ) . '</a>';

		return $field_views;
	}

	/**
	 * Generate URL link from argument value
	 *
	 * @since 1.1.9
	 *
	 * @param string $action
	 * @param string $equals
	 * @param bool   $return
	 *
	 * @return string
	 */
	public function link( $action = NULL, $equals = NULL, $return = FALSE ) {

		$query    = '';
		$page_url = $this->get_fields_page();
		if ( is_array( $action ) ) {
			$query = http_build_query( $action );
		} elseif ( $action && $equals ) {
			$query = $action . '=' . $equals;
		}

		$page_url .= '&' . $query;

		if ( $return || ( is_array( $action ) && $equals == TRUE ) ) {
			return $page_url;
		} else {
			echo $page_url;
		}
	}

	/**
	 * Display the search box.
	 *
	 * @since  3.1.0
	 * @access public
	 *
	 * @param string $text     The search button text
	 * @param string $input_id The search input id
	 */
	function search_box( $text, $input_id ) {

		if ( empty( $_REQUEST[ 's' ] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST[ 'orderby' ] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST[ 'orderby' ] ) . '" />';
		}
		if ( ! empty( $_REQUEST[ 'order' ] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST[ 'order' ] ) . '" />';
		}
		if ( ! empty( $_REQUEST[ 'post_mime_type' ] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST[ 'post_mime_type' ] ) . '" />';
		}
		if ( ! empty( $_REQUEST[ 'detached' ] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST[ 'detached' ] ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', FALSE, FALSE, array( 'id' => 'search-submit' ) ); ?>
		</p>
	<?php
	}

	function list_sort( $the_fields ) {

		$usort = 'sort_string';
		$float_sort_fields = array( 'priority' );

		//If no sort, default to priority
		$this->orderby = ( ! empty( $_REQUEST[ 'orderby' ] ) ) ? $_REQUEST[ 'orderby' ] : 'priority';

		//If no order, default to asc
		$this->order = ( ! empty( $_REQUEST[ 'order' ] ) ) ? $_REQUEST[ 'order' ] : 'asc';

		// If we're sorting something that is a float (integer with decimal), lets change func to float
		if( in_array( $this->orderby, $float_sort_fields ) ) $usort = 'sort_float';

		// Sort the fields based on orderby value
		$fieldSort = new WP_Job_Manager_Field_Editor_Sort( $the_fields, $this->orderby, $this->order );
		$the_fields = $fieldSort->{$usort}();

		return $the_fields;
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since  1.1.8
	 * @access public
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = TRUE ) {

		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		$current_url = set_url_scheme( admin_url( $this->link( null, null, TRUE ) ) );

		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_REQUEST[ 'orderby' ] ) )
			$current_orderby = $_REQUEST[ 'orderby' ];
		else
			$current_orderby = '';

		if ( isset( $_REQUEST[ 'order' ] ) && 'desc' == $_REQUEST[ 'order' ] )
			$current_order = 'desc';
		else
			$current_order = 'asc';

		if ( ! empty( $columns[ 'cb' ] ) ) {
			static $cb_counter = 1;
			$columns[ 'cb' ] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All', 'wp-job-manager-field-editor' ) . '</label>'
			                   . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter ++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			if( is_array( $hidden ) && in_array( $column_key, $hidden ) ) $class[] = "hidden";
			$style = ' style=""';

			if ( 'cb' == $column_key )
				$class[ ] = 'check-column';
			elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
				$class[ ] = 'num';

			if ( isset( $sortable[ $column_key ] ) ) {
				list( $orderby, $desc_first ) = $sortable[ $column_key ];

				if ( $current_orderby == $orderby ) {
					$order    = 'asc' == $current_order ? 'desc' : 'asc';
					$class[ ] = 'sorted';
					$class[ ] = $current_order;
				} else {
					$order    = $desc_first ? 'desc' : 'asc';
					$class[ ] = 'sortable';
					$class[ ] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$id = $with_id ? "id='$column_key'" : '';

			if ( ! empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";

			echo "<th scope='col' $id $class $style>$column_display_name</th>";
		}
	}

}