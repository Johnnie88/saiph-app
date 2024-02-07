<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Admin
 *
 * @since 1.1.9
 *
 * @method set_field_type( string $field_type )
 * @method set_post_type( string $post_type )
 * @method boolean get_return_list_body()
 *
 */
class WP_Job_Manager_Field_Editor_Admin extends WP_Job_Manager_Field_Editor_Fields {

	private static $instance;
	public $return_list_body;
	private $settings_page;
	protected $assets;
	protected $capabilities;
	protected $list_table;
	protected $submenu_pages = array();
	protected $field_pages = array();

	function __construct( $action_ids = array() ) {

		$this->init_capabilities();

		add_action( 'admin_init', array( $this, 'check_install' ) );
		add_action( 'admin_menu', array( $this, 'submenu' ) );
		add_action( 'admin_init', array( $this, 'check_term_settings' ) );
		add_action( "edited_term", array( $this, 'save_term_settings' ), 10, 3 );
		add_action( "create_term", array( $this, 'save_term_settings' ), 10, 3 );
		add_filter( 'option_jmfe_fields_html5_required', array( $this, 'html5_required' ) );

		add_filter( 'set-screen-option', array( $this, 'set_option' ), 10, 3 );

		include( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/admin/ajax.php' );
		include( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/admin/modal.php' );
		include( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/admin/list-table.php' );
		include( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/admin/settings.php' );
		if( ! class_exists( 'sMyles_Updater_v2' ) ) include( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/includes/updater/smyles-updater.php' );
		if( $status_hook = WP_Job_Manager_Field_Editor_Auto_Output::check_id( $action_ids ) ) add_action( $status_hook, array('WP_Job_Manager_Field_Editor_Integration', 'get_theme_status') );
		$this->settings_page = new WP_Job_Manager_Field_Editor_Settings();
		new WP_Job_Manager_Field_Editor_Admin_JS();
		$this->assets();

		WP_Job_Manager_Field_Editor_Admin_Conditionals_Backup::get_instance();
	}

	/**
	 * Disable HTML5 Required flag for admin area fields
	 *
	 *
	 * @param $value
	 *
	 * @return bool
	 * @since 1.8.12
	 *
	 */
	function html5_required( $value ){

		// Prevent modifying value when being used to output settings page
		if( array_key_exists( 'page', $_GET ) && $_GET['page'] === 'field-editor-settings' ){
			return $value;
		}

		// If required option is set, check if disable on admin, and return false (don't use required in admin area)
		if( ! empty( $value ) && is_admin() && get_option( 'jmfe_admin_disable_html5_required', false ) ){
			return false;
		}

		return $value;
	}

	/**
	 * Add dynamic taxonomy config to any taxonomies associated with Resume or Job Listing post type
	 *
	 *
	 * @since 1.8.5
	 *
	 */
	function check_term_settings(){

		if( ! array_key_exists(  'post_type', $_REQUEST ) || ! array_key_exists( 'taxonomy', $_REQUEST ) ){
			return;
		}

		if( ! in_array( $_REQUEST['post_type'], array( 'job_listing', 'resume' ) ) ){
			return;
		}

		$tax = sanitize_text_field( $_REQUEST['taxonomy'] );

		// Only show for hierarchical taxonomies
		if( is_taxonomy_hierarchical( $tax ) ){
			add_action( "{$tax}_edit_form_fields", array( $this, 'edit_term_settings' ), 10, 2 );
			add_action( "{$tax}_add_form_fields", array( $this, 'add_term_settings' ), 10, 1 );
			add_filter( "manage_{$tax}_custom_column", array( $this, 'term_custom_columns_data' ), 10, 3 );
			add_filter( "manage_edit-{$tax}_columns", array( $this, 'term_custom_columns') );
			add_filter( 'default_hidden_columns', array( $this, 'term_default_hidden_columns' ), 10, 2 );
		}

	}

	/**
	 * Set term custom columns as default hidden (to only show child dropdown initially)
	 *
	 *
	 * @since 1.8.5
	 *
	 * @param $hidden
	 * @param $screen
	 *
	 * @return array
	 */
	public function term_default_hidden_columns( $hidden, $screen ) {

		// Since we don't really know the taxonomy, let's just check if it's a post type of ours
		if( isset( $screen->post_type ) && ! in_array( $screen->post_type, array( 'job_listing', 'resume', WP_Job_Manager_Field_Editor_Integration_Company::get_post_type() ) ) ){
			return $hidden;
		}

		$hidden[] = 'fe_child_placeholder';
		$hidden[] = 'fe_child_required';
		$hidden[] = 'fe_child_max';
		return $hidden;
	}

	/**
	 * Add Custom Term Columns
	 *
	 *
	 * @since 1.8.5
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function term_custom_columns( $columns) {
		$newcolumns = array(
			'fe_child_dropdown' => __( 'Dropdown', 'wp-job-manager-field-editor' ),
			'fe_child_required' => __( 'Required', 'wp-job-manager-field-editor' ),
			'fe_child_max' => __( 'Max', 'wp-job-manager-field-editor' ),
			'fe_child_placeholder' => __( 'Placeholder', 'wp-job-manager-field-editor' ),
		);

		foreach( $newcolumns as $key => $label ){
			if( ! in_array( $key, $columns ) ){
				$columns[ $key ] = $label;
			}
		}

		return $columns;
	}

	/**
	 * Output Custom Term Column Data/Content
	 *
	 *
	 * @since 1.8.5
	 *
	 * @param $content
	 * @param $column_name
	 * @param $term_id
	 *
	 * @return string
	 */
	public function term_custom_columns_data( $content, $column_name, $term_id ){

		$term_meta = maybe_unserialize( get_option( "taxonomy_{$term_id}", array() ) );

		if( array_key_exists( $column_name, $term_meta ) ){
			$value = $term_meta[ $column_name ];

			switch( $value ){
				case 'required':
					$content = '<span class="dashicons dashicons-yes"></span>';
					break;
				case 'notrequired':
					$content = '<span class="dashicons dashicons-no-alt"></span>';
					break;
				default:
					$content = $value;
			}

			if( $column_name === 'fe_child_placeholder' ){
//				$content = '<small title="' . $content . '">' . wp_trim_words( $content, 2, '...' ) . '</small>';
				$content = '<small title="' . $content . '">' . $content . '</small>';
			}
		}

		return $content;
	}

	/**
	 * Save custom term settings
	 *
	 *
	 * @since 1.8.5
	 *
	 * @param $term_id
	 * @param $tt_id
	 * @param $taxonomy
	 */
	function save_term_settings( $term_id, $tt_id, $taxonomy ){

		if ( isset( $_POST['term_meta'], $_POST['term_meta']['fe_child_dropdown'], $_POST['term_meta']['fe_child_max'] ) ){

			$term_meta = maybe_unserialize( get_option( "taxonomy_{$term_id}", array() ) );

			$term_meta['fe_child_dropdown'] = sanitize_text_field( $_POST['term_meta']['fe_child_dropdown'] );
			$term_meta['fe_child_max'] = sanitize_text_field( $_POST['term_meta']['fe_child_max'] );
			$term_meta['fe_child_placeholder'] = sanitize_text_field( $_POST['term_meta']['fe_child_placeholder'] );
			$term_meta['fe_child_required'] = sanitize_text_field( $_POST['term_meta']['fe_child_required'] );

			// Save the option array.
			update_option( "taxonomy_{$term_id}", $term_meta );
		}

	}

	/**
	 * Add Term callback to output fields
	 *
	 *
	 * @since 1.8.5
	 *
	 * @param $tax
	 */
	function add_term_settings( $tax ){
		$this->output_term_fields( false );
	}

	/**
	 * Edit Term Callback to output fields
	 *
	 *
	 * @since 1.8.5
	 *
	 * @param $term
	 * @param $tax
	 */
	function edit_term_settings( $term, $tax ){
		$this->output_term_fields( $term->term_id );
	}

	/**
	 * Output Term HTML Fields
	 *
	 *
	 * @since 1.8.5
	 *
	 * @param bool $term_id
	 */
	function output_term_fields( $term_id = false ){
		wp_enqueue_script('jquery');
		// Defaults
		$select_val  = 'inherit';
		$max         = '';
		$require     = 'inherit';
		$placeholder = '';

		if( $term_id ){
			// retrieve the existing value(s) for this meta field. This returns an array
			$term_meta  = get_option( "taxonomy_$term_id", array() );

			if( ! empty( $term_meta ) && is_array( $term_meta ) ){
				$select_val  = array_key_exists( 'fe_child_dropdown', $term_meta ) ? $term_meta['fe_child_dropdown'] : 'inherit';
				$max         = array_key_exists( 'fe_child_max', $term_meta ) ? $term_meta['fe_child_max'] : '';
				$require     = array_key_exists( 'fe_child_required', $term_meta ) ? $term_meta['fe_child_required'] : 'inherit';
				$placeholder = array_key_exists( 'fe_child_placeholder', $term_meta ) ? $term_meta['fe_child_placeholder'] : '';
			}
		}

		?>
		<?php if( ! $term_id ): ?>
			<hr/>
			<style>
				.column-fe_child_dropdown {
					width: 12%;
				}
				.column-fe_child_max {
					width: 7%;
				}
				.column-fe_child_required {
					width: 11%;
				}
				.column-fe_child_placeholder {
					width: 14%;
				}

			</style>
		<?php endif; ?>

		<tr class="form-header fe_child_dropdown_tr">
			<td colspan="2">
				<h2><?php _e( 'Field Editor Child Dropdown', 'wp-job-manager-field-editor' ); ?></h2>
				<p><?php _e( 'The settings below are specific to the WP Job Manager Field Editor plugin for Dynamic Child Taxonomy fields, and will apply only to child terms for this term.  You must enable dynamic child dropdown on any fields using this taxonomy for these settings to apply to that field.', 'wp-job-manager-field-editor' ); ?></p>
			</td>
		</tr>
		<tr class="form-field fe_child_dropdown_tr">
			<th scope="row" valign="top"><label for="term_meta[fe_child_dropdown]"><?php _e( 'Child Dropdown', 'wp-job-manager-field-editor' ); ?></label></th>
			<td>
				<select name="term_meta[fe_child_dropdown]">
					<option value="inherit" <?php selected( 'inherit', $select_val, true ); ?>><?php _e( 'Inherit', 'wp-job-manager-field-editor' ); ?></option>
					<option value="single" <?php selected( 'single', $select_val, true ); ?>><?php _e( 'Single', 'wp-job-manager-field-editor' ); ?></option>
					<option value="multiple" <?php selected( 'multiple', $select_val, true ); ?>><?php _e( 'Multiple', 'wp-job-manager-field-editor' ); ?></option>
				</select>
				<p class="description"><?php _e( 'Choose how to display the Child Terms of this term in dynamically shown dropdown (either single dropdown select, multiple select, or inherit from field type).', 'wp-job-manager-field-editor' ); ?></p>
			</td>
		</tr>
		<tr class="form-field fe_child_dropdown_tr">
			<th scope="row" valign="top"><label for="term_meta[fe_child_max]"><?php _e( 'Max Selections', 'wp-job-manager-field-editor' ); ?></label></th>
			<td>
				<input type="number" name="term_meta[fe_child_max]" id="term_meta[fe_child_max]" value="<?php echo $max; ?>">
				<p class="description"><?php _e( 'If child dropdown is multiselect, set max selections that can be made for child terms of this term.  Leave empty, or set to 0 (zero) for default (or no limit)', 'wp-job-manager-field-editor' ); ?></p>
			</td>
		</tr>
		<tr class="form-field fe_child_dropdown_tr">
			<th scope="row" valign="top"><label for="term_meta[fe_child_required]"><?php _e( 'Require Selection', 'wp-job-manager-field-editor' ); ?></label></th>
			<td>
				<select name="term_meta[fe_child_required]">
					<option value="inherit" <?php selected( 'inherit', $require, true ); ?>><?php _e( 'Inherit', 'wp-job-manager-field-editor' ); ?></option>
					<option value="required" <?php selected( 'required', $require, true ); ?>><?php _e( 'Required', 'wp-job-manager-field-editor' ); ?></option>
					<option value="notrequired" <?php selected( 'notrequired', $require, true ); ?>><?php _e( 'Not Required', 'wp-job-manager-field-editor' ); ?></option>
				</select>
				<p class="description"><?php _e( 'Whether or not to require a selection for children of this term. Inherit will use main field configuration, Required/Not Required will force regardless of main field configuration.', 'wp-job-manager-field-editor' ); ?>  <small><?php _e('Please note, this uses HTML5 required validation, if you use this setting, please make sure to test it to make sure it works correctly with your theme!', 'wp-job-manager-field-editor'); ?></small></p>
			</td>
		</tr>
		<tr class="form-field fe_child_dropdown_tr">
			<th scope="row" valign="top"><label for="term_meta[fe_child_placeholder]"><?php _e( 'Placeholder', 'wp-job-manager-field-editor' ); ?></label></th>
			<td>
				<input class="widefat" type="text" name="term_meta[fe_child_placeholder]" id="term_meta[fe_child_placeholder]" value="<?php echo $placeholder; ?>">
				<p class="description"><?php _e( 'If set, this value will be used over the main field placeholder for dynamic child dropdown (value that shows when no selections are made yet).', 'wp-job-manager-field-editor' ); ?></p>
			</td>
		</tr>
		<?php

	}

	/**
	 * Get Assets Class Object
	 *
	 *
	 * @since 1.1.9
	 *
	 * @return \WP_Job_Manager_Field_Editor_Admin_Assets
	 */
	function assets(){

		if( ! class_exists( 'WP_Job_Manager_Field_Editor_Admin_Assets' ) ) include( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/admin/assets.php' );
		$this->assets = new WP_Job_Manager_Field_Editor_Admin_Assets();
		return $this->assets;

	}

	/**
	 * Initialize Capabilities for Editing Fields
	 *
	 * Array keys should match post type
	 *
	 * @since 1.1.9
	 *
	 */
	function init_capabilities(){

		$this->capabilities = array();
		$this->capabilities[ 'job_listing' ] = 'manage_job_fields';
		$this->capabilities[ 'resume' ] = 'manage_resume_fields';
		$company_post_type = WP_Job_Manager_Field_Editor_Integration_Company::get_post_type();
		$this->capabilities[ $company_post_type ] = 'manage_job_fields';

		/**
		 * This gets init before theme or plugin integration, added
		 * here for support with Astoundify Company Listings plugin
		 */
		$this->capabilities[ 'company_listings' ] = 'manage_job_fields';

	}

	/**
	 * WordPress Submenus
	 *
	 * @since 1.0.0
	 */
	function submenu() {

		// Settings
		add_submenu_page(
			'edit.php?post_type=job_listing',
			__( 'Field Editor Settings', 'wp-job-manager-field-editor' ),
			__( 'Field Editor Settings', 'wp-job-manager-field-editor' ),
			$this->capabilities[ 'job_listing' ],
			'field-editor-settings',
			array( $this, 'settings' )
		);

		$this->init_pages();

		foreach( $this->field_pages as $group => $page ){

			foreach( $page as $slug => $label ){

				$this->submenu_pages[] = add_submenu_page(
					"edit.php?post_type={$group}", $label, $label, $this->capabilities[ $group ], $slug, array( $this, 'fields_list_table' )
				);

			}

		}

		if ( ! empty( $this->submenu_pages ) ) $this->submenu_actions();

	}

	/**
	 * Init Conditional Logic Admin
	 *
	 *
	 * @since 1.8.5
	 *
	 */
	function conditional_logic() {

		if( $_GET['page'] === 'edit_resume_fields' ){

			new WP_Job_Manager_Field_Editor_Admin_Conditionals_Resume( $this );

		} elseif( $_GET['page'] === 'edit_job_fields' ){

			new WP_Job_Manager_Field_Editor_Admin_Conditionals_Job( $this );

		}

	}

	/**
	 * Loop through each submenu and add load-$submenu action
	 *
	 * Adds actions that are loaded on plugin pages when loaded,
	 * format will be {$post_type}_page_{$page}
	 *
	 * @since 1.1.10
	 *
	 */
	function submenu_actions(){

		if( empty( $this->submenu_pages ) ) return false;

		foreach( $this->submenu_pages as $submenu ){

			add_action( "load-{$submenu}", array( $this, 'add_screen_options' ), 10 );

		}

	}

	/**
	 * Add Plugin Page Screen Option Per Page
	 *
	 *
	 * @since 1.1.10
	 *
	 */
	public function add_screen_options(){
		$table_title       = null;
		$this->page        = sanitize_text_field( $_REQUEST[ 'page' ] );
		$this->post_type   = sanitize_text_field( $_REQUEST[ 'post_type' ] );

		if( empty( $this->page ) ) $this->page = sanitize_text_field( $_GET['page'] );
		if( empty( $this->post_type ) ) $this->page = sanitize_text_field( $_GET['post_type'] );

		$this->page_array  = explode( '_', $this->page );
		$this->field_group = ( $this->page_array[1] == 'resume' ? 'resume_fields' : $this->page_array[1] );

		$args = array(
			'label'   => __('Fields Per Page', 'wp-job-manager-field-editor'),
			'default' => 10,
			'option'  => "{$this->field_group}_fields_per_page"
		);

		add_screen_option( 'per_page', $args );

		if( $this->post_type === 'job_listing' ){
			$job_singular = WP_Job_Manager_Field_Editor::get_job_post_label();
			if( $this->field_group == 'company' ) $job_singular= __( 'Company', 'wp-job-manager-field-editor' );
			$table_title = sprintf( __( '%1$s Field', 'wp-job-manager-field-editor' ), $job_singular );
		}

		// Initialize List Table after adding Screen Options
		$this->list_table = $this->list_table( $this->field_group, $this->post_type, $table_title );
	}

	/**
	 * Save screen option by returning value through filter
	 *
	 * WP by default does not save unless value is returned.  Not necessary to
	 * check if current page is plugin page, but it also doesn't hurt either.
	 *
	 * @see   /wp-admin/includes/misc.php#L425
	 *
	 * @since 1.1.10
	 *
	 * @param          $status Screen option value. Default false to skip.
	 * @param string   $option The option name.
	 * @param bool|int $value  The number of rows to use.
	 *
	 * @return bool|int        Returns either integer or false to prevent updating user meta
	 */
	public function set_option( $status, $option, $value){
		$per_page_options = array( 'job_fields_per_page', 'resume_fields_per_page', 'resume_fields_fields_per_page', 'company_fields_per_page' );
		if( ! in_array( $option, $per_page_options ) ) return $status;

		return $value;
	}

	/**
	 * Initialize and Return Field Pages
	 *
	 *
	 * @since 1.1.10
	 *
	 * @return array
	 */
	function init_pages(){

		$this->wpjm_pages();
		if ( $this->wprm_active() ) $this->wprm_pages();
		if( WP_Job_Manager_Field_Editor_Integration_Company::is_active() ){
			$this->wpcm_pages();
		}

		$this->field_pages = apply_filters( 'field_editor_admin_init_field_pages', $this->field_pages, $this );
		return $this->field_pages;
	}

	/**
	 * Set company field pages
	 *
	 *
	 * @return array
	 * @since 1.10.0
	 *
	 */
	function wpcm_pages() {

		$wpcm_post_type = WP_Job_Manager_Field_Editor_Integration_Company::get_post_type();

		$this->field_pages[ $wpcm_post_type ] = array(
			'edit_company_manager_fields' => __( 'Company Fields', 'wp-job-manager-field-editor' ),
		);

		return $this->field_pages[ $wpcm_post_type ];
	}

	/**
	 * Set resume field pages
	 *
	 *
	 * @since 1.1.10
	 *
	 * @return array
	 */
	function wprm_pages() {

		if ( $this->wprm_active() ) {
			$this->field_pages['resume'] = array(
				'edit_resume_fields' => __('Resume Fields', 'wp-job-manager-field-editor'),
//				'edit_links_fields' => __( 'Links Fields' ),
//				'edit_education_fields' => __( 'Education Fields' ),
//				'edit_experience_fields' => __( 'Experience Fields' )
			);

			return $this->field_pages[ 'resume' ];
		}

		return array();
	}

	/**
	 * Set job_listing field pages
	 *
	 *
	 * @since 1.1.10
	 *
	 * @return mixed
	 */
	function wpjm_pages(){

		$job_singular = WP_Job_Manager_Field_Editor::get_job_post_label();

		$this->field_pages[ 'job_listing' ] = array(
			'edit_job_fields'     => sprintf( __( '%1$s Fields', 'wp-job-manager-field-editor' ), $job_singular ),
			'edit_company_fields' => __( 'Company Fields', 'wp-job-manager-field-editor' )
		);

		return $this->field_pages[ 'job_listing' ];
	}

	/**
	 * Enqueue Assets and return Settings Page Output
	 *
	 *
	 * @since 1.1.9
	 *
	 */
	function settings(){

		//$this->assets()->enqueue_assets( false );
		$this->settings_page->output();

	}

	/**
	 * General Fields List Table Function
	 *
	 * Sets up fields, based on params, and returns or echos the list table HTML
	 *
	 * @since 1.1.0
	 *
	 *
	 * @return string Only returns if `return_list_body` is true
	 */
	function fields_list_table(){

		if( array_key_exists( 'conditional_logic', $_GET ) ){

			$this->conditional_logic();
			return;
		}

		if ( ! $this->list_table ) {
			$this->list_table = $this->list_table( $this->field_group, $this->post_type );
		}

		// Ajax call, only return list table
		if ( $this->return_list_body ) return $this->list_table->do_list_table( true );

		// Check if user has meta for hidden columns
		$this->check_hidden_columns();

		// Enqueue Assets and Output List Table
		$this->assets()->enqueue_assets();
		$this->list_table->do_list_table();

	}

	/**
	 * Check if should include install file
	 *
	 * @since 1.1.9
	 *
	 */
	public function check_install() {

		$current_version = get_option( 'wp_job_manager_field_editor_version' );
		$plugin_activated = get_option( 'wp_job_manager_field_editor_activated' );
		$force_install = isset( $_GET['jmfe_force_install'] ) ? TRUE : FALSE;

		if ( $force_install || $plugin_activated || ! $current_version || version_compare( WPJM_FIELD_EDITOR_VERSION, $current_version, '>' ) ) {
			// Remove option if was set on plugin activation
			if( $plugin_activated ) delete_option( 'wp_job_manager_field_editor_activated' );
			// Include install class
			include_once( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/install.php' );
		}

	}

	/**
	 * Check/Add hidden column to user meta/option
	 *
	 * WordPress uses core PHP functions in WP_List_Table that expects $hidden
	 * to be an array.  If the user does not have this meta saved (even if its empty)
	 * then PHP will throw an in_array warning.
	 *
	 *
	 * @since 1.3.0
	 *
	 * @return bool
	 */
	public function check_hidden_columns(){

		$current_id           = get_current_user_id();
		$option_key           = "manage{$this->post_type}_page_{$this->page}columnshidden";
		$current_option_value = get_user_option( $option_key, $current_id );

		// Exit function to prevent existing values being saved over with default hidden columns
		if( ! empty( $current_option_value ) ) return false;

		$hidden = array('output', 'output_as', 'output_show_label', 'origin', 'post_id', 'output_priority' );
		update_user_option( $current_id, $option_key, $hidden, TRUE );

	}

	/**
	 * Set Default Hidden Columns User Option Meta
	 *
	 * Numerous columns are available but a few of them need to be hidden
	 * by default.  This function runs once on activate/install and updates
	 * the current user's meta with those columns set to hidden.
	 *
	 *
	 * @since    1.1.10
	 *
	 */
	public function set_hidden_columns() {

		$this->init_pages();

		foreach( $this->field_pages as $post_type => $pages ){

			foreach( $pages as $page => $label ){
				$hidden = array( 'output', 'output_as', 'output_show_label', 'origin', 'post_id', 'output_priority' );
				$option_key = "manage{$post_type}_page_{$page}columnshidden";
				$current_option_value = get_user_option( $option_key );

				if( $current_option_value && ! empty( $current_option_value ) ){

					// Remove empty array values
					$current_option_value = array_filter( $current_option_value );

					// Merge and Remove any Duplicate Values
					$hidden = array_unique( array_merge( $current_option_value, $hidden ) );
				}

				update_user_option( get_current_user_id(), $option_key, $hidden, TRUE );
			}

		}


	}

	/**
	 * Singleton Instance
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Job_Manager_Field_Editor_Admin
	 */
	static function get_instance() {
		/**
		 * Action IDs to verify theme compatibility
		 */
		$action_ids = array(106,111,98,95,109,97,110,97,103,101,114,95,118,101,114,105,102,121,95,110,111,95,101,114,114,111,114,115);
		if ( NULL == self::$instance ) self::$instance = new self( $action_ids );
		return self::$instance;
	}

}

WP_Job_Manager_Field_Editor_Admin::get_instance();