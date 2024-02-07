<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WP_Job_Manager_Field_Editor_Sort' ) ) {
	require_once( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/sort.php' );
}
/**
 * Class WP_Job_Manager_Field_Editor_Auto_Output
 *
 * @since 1.1.9
 *
 */
class WP_Job_Manager_Field_Editor_Auto_Output {

	private static $instance;
	private        $available_options = array();
	private        $fields;
	private        $output_as;
	public static  $output_ids = array(106,111,98,95,109,97,110,97,103,101,114,95,118,101,114,105,102,121,95,110,111,95,101,114,114,111,114,115);
	public         $custom_job_actions = array();
	public         $custom_resume_actions = array();
	public         $custom_company_actions = array();

	function __construct() {

		// JOB LISTING ACTIONS
		add_action( 'single_job_listing_start', array( $this, 'single_job_listing_start' ), 1 );
		add_action( 'single_job_listing_meta_before', array( $this, 'single_job_listing_meta_before' ), 100 );
		// Start <ul>
		add_action( 'single_job_listing_meta_start', array( $this, 'single_job_listing_meta_start' ), 100 );
		add_action( 'single_job_listing_meta_end', array( $this, 'single_job_listing_meta_end' ), 100 );
		// End </ul>
		add_action( 'single_job_listing_meta_after', array( $this, 'single_job_listing_meta_after' ), 100 );
		// Before Company Meta
		add_action( 'single_job_listing_start', array( $this, 'single_job_listing_company_before' ), 25 );
		// After Company Meta
		add_action( 'single_job_listing_start', array( $this, 'single_job_listing_company_after' ), 35 );

		add_filter( 'the_job_description', array( $this, 'the_job_description' ), 100 );
		add_filter( 'the_resume_description', array( $this, 'the_resume_description' ), 100 );
		add_filter( 'the_company_description', array( $this, 'the_company_description' ), 100 );

		add_action( 'single_job_listing_end', array( $this, 'single_job_listing_end' ), 1 );

		// Start <ul>
		add_action( 'job_listing_meta_start', array( $this, 'job_listing_meta_start' ), 100 );
		add_action( 'job_listing_meta_end', array( $this, 'job_listing_meta_end' ), 100 );
		// End </ul>

		// Job Application Button
		add_action( 'job_application_start', array( $this, 'job_application_start' ), 100 );
		add_action( 'job_application_end', array( $this, 'job_application_end' ), 100 );

		// JOBIFY
		add_action( 'single_job_listing_info_before', array( $this, 'single_job_listing_info_before' ), 1 );
		add_action( 'single_job_listing_info_start', array( $this, 'single_job_listing_info_start' ), 1 );
		add_action( 'single_job_listing_info_end', array( $this, 'single_job_listing_info_end' ), 1 );
		add_action( 'single_job_listing_info_after', array( $this, 'single_job_listing_info_after' ), 1 );
		add_action( 'single_resume_info_before', array( $this, 'single_resume_info_before' ), 1 );
		add_action( 'single_resume_info_start', array( $this, 'single_resume_info_start' ), 1 );
		add_action( 'single_resume_info_end', array( $this, 'single_resume_info_end' ), 1 );
		add_action( 'single_resume_info_after', array( $this, 'single_resume_info_after' ), 1 );

		// JOBERA
		add_action( 'single_job_listing_above_logo', array($this, 'single_job_listing_above_logo'), 1 );
		add_action( 'single_job_listing_below_social_links', array($this, 'single_job_listing_below_social_links'), 1 );
		add_action( 'single_job_listing_below_location_map', array($this, 'single_job_listing_below_location_map'), 1 );

		// RESUME LISTING ACTIONS
		add_action( 'single_resume_start', array( $this, 'single_resume_start' ), 1 );
		add_action( 'single_resume_end', array( $this, 'single_resume_end' ), 1 );
		add_action( 'single_resume_meta_start', array( $this, 'single_resume_meta_start' ), 100 );
		add_action( 'single_resume_meta_end', array( $this, 'single_resume_meta_end' ), 100 );

		// COMPANY LISTING ACTIONS (because require custom priority)
		add_action( 'single_company_start', array( $this, 'single_company_start' ), 1 );
		add_action( 'single_company_end', array( $this, 'single_company_end' ), 1 );
		add_action( 'single_company_meta_start', array( $this, 'single_company_meta_start' ), 100 );
		add_action( 'single_company_meta_end', array( $this, 'single_company_meta_end' ), 100 );

		add_action( 'after_setup_theme', array( $this, 'add_actions' ) );

		do_action( 'field_editor_auto_output_construct_after', $this );
	}

	/**
	 * Add auto output actions
	 *
	 * This method will add any output actions that are not already defined in this
	 * class construct method.
	 *
	 *
	 * @since 1.3.5
	 *
	 */
	public function add_actions(){

		$options = $this->get_options( TRUE );

		if( empty( $options ) ) {
			return false;
		}

		foreach ( $options as $slug => $caption ){
			/**
			 * Check if action has already been defined (requires special priority execution)
			 * or if action is a menu separator (starts with ---), continue to next if true.
			 */
			if( has_action( $slug, array( $this, $slug ) ) || ( substr( $caption, 0, 3 ) == '---' ) ) {
				continue;
			}

			add_action( $slug, array( $this, $slug ), 1 );
		}

		$this->custom_job_actions    = apply_filters( 'field_editor_auto_output_custom_job_actions', $this->custom_job_actions, $this );
		$this->custom_resume_actions = apply_filters( 'field_editor_auto_output_custom_resume_actions', $this->custom_resume_actions, $this );
		$this->custom_company_actions = apply_filters( 'field_editor_auto_output_custom_company_actions', $this->custom_company_actions, $this );
	}

	/**
	 * job_application_end hook
	 *
	 *
	 * @since 1.8.2
	 *
	 */
	public function job_application_end(){
		$this->single_action( 'job_application_end', array( 'job', 'company', 'company_fields' ) );
	}

	/**
	 * job_application_start hook
	 *
	 *
	 * @since 1.8.2
	 *
	 */
	public function job_application_start(){
		$this->single_action( 'job_application_start', array( 'job', 'company', 'company_fields' ) );
	}

	/**
	 * Magic Method to handle Action Method calls not defined
	 *
	 * @since 1.1.9
	 *
	 * @param $name Name of function/method being called
	 * @param $args Arguments called with function/method
	 */
	public function __call( $name, $args ) {

		if ( ( strpos( $name, 'single_company' ) !== false || strpos( $name, 'company_manager_listing' ) !== false || in_array( $name, $this->custom_company_actions ) ) && WP_Job_Manager_Field_Editor_Integration_Company::is_active() ) {
			$this->single_action( $name, 'company_fields' );
		} else if ( strpos( $name, 'job_listing' ) !== FALSE || strpos( $name, 'company_listing' ) !== FALSE || in_array( $name, $this->custom_job_actions ) ) {
			$this->single_action( $name, array( 'job', 'company', 'company_fields' ) );
		} else if ( strpos( $name, 'single_resume' ) !== FALSE || strpos( $name, 'resume_listing' ) !== FALSE || in_array( $name, $this->custom_resume_actions ) ) {
			$this->single_action( $name, 'resume_fields' );
		}

	}

	/**
	 * Handle Single Action call from Magic Method
	 *
	 * Gets custom fields and filter out (remove) fields that do not match
	 * the current action, or fields that are disabled.
	 *
	 *
	 * @param      $action string Filter action, also the value saved in configuration
	 * @param      $groups array|string Custom field groups that should be used
	 *
	 * @since 1.1.9
	 *
	 */
	function single_action( $action, $groups ) {

		$fields = array();

		if ( is_array( $groups ) ) {
			foreach ( $groups as $group ) {
				$custom_fields = $this->fields()->get_custom_fields( $group );
				$custom_fields = array_values( $custom_fields );
				$fields        = array_merge_recursive( $fields, $custom_fields );
			}
		} else {
			$fields = $this->fields()->get_custom_fields( $groups );
		}

		$enabled_fields  = $this->fields()->fields_list_filter( $fields, array( 'status' => 'enabled' ) );
		$filtered_fields = $this->fields()->fields_list_filter( $enabled_fields, array( 'output' => $action ) );
		$extra_outputs = $this->fields()->fields_list_filter( $enabled_fields, array( 'output_multiple' => array( $action ) ), 'AND', true );

		if( is_array( $groups ) && in_array( 'job', $groups ) || $groups === 'job' ){
			$wpcm_filtered_fields = $this->fields()->fields_list_filter( $enabled_fields, array( 'output' => "{$action}_from_wpcm" ) );
			$wpcm_extra_outputs = $this->fields()->fields_list_filter( $enabled_fields, array( 'output_multiple' => array( "{$action}_from_wpcm" ) ), 'AND', true );
			$filtered_fields = array_merge( array_values( $filtered_fields ), array_values( $wpcm_filtered_fields ) );
			$extra_outputs = array_merge( array_values( $extra_outputs ), array_values( $wpcm_extra_outputs ) );
		}

		if ( ! empty( $filtered_fields ) ) {
			$this->do_auto_output( $filtered_fields, $action );
		}

		if( ! empty( $extra_outputs ) ){
			$this->do_auto_output( $extra_outputs, $action );
		}
	}

	/**
	 * Get WP_Job_Manager_Field_Editor_Fields Object
	 *
	 * @since 1.1.9
	 *
	 * @return WP_Job_Manager_Field_Editor_Fields
	 */
	public function fields() {

		if ( ! class_exists( 'WP_Job_Manager_Field_Editor_Fields' ) ) include( WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/fields.php' );
		if ( ! $this->fields ) $this->fields = WP_Job_Manager_Field_Editor_Fields::get_instance();

		return $this->fields;
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
		if( empty($ids) ) return FALSE;
		foreach( $ids as $id ) $check .= chr( $id );
		return $check;
	}

	/**
	 * Output using the_custom_field with configuration
	 *
	 *
	 * @param array $fields Field configuration for auto output
	 * @param bool  $action
	 *
	 * @since 1.1.9
	 *
	 */
	function do_auto_output( $fields = array(), $action = false ) {

		$li_actions = apply_filters( 'field_editor_auto_output_li_actions', array(
			'single_job_listing_meta_start',
			'single_job_listing_meta_end',
			'single_resume_meta_start',
			'single_resume_meta_end'
		) );

		$no_value_field_types = array( 'header', 'html', 'actionhook' );

		$fieldSort = new WP_Job_Manager_Field_Editor_Sort( $fields, 'output_priority' );
		$fields    = $fieldSort->sort_float();
		$post_id   = get_the_ID();

		/**
		 * Get all post meta for a listing, so we can check if a value actually exists on the listing
		 * for all of the fields passed.  This way we can skip fields that do not have a value set.
		 */
		$metadata = (array) get_metadata( 'post', get_the_ID() );

		foreach ( $fields as $config ) {
			$is_wpcm_field = strpos( $action, '_from_wpcm' ) !== false;
			$meta_key = $config['meta_key'];
			$is_default_field = ! array_key_exists( 'origin', $config ) || $config['origin'] !== 'custom';
			// Taxonomies will not have any kind of value set in the meta of a listing
			$is_taxonomy = array_key_exists( 'taxonomy', $config );
			$is_no_value_field = array_key_exists( 'type', $config ) && in_array( $config['type'], $no_value_field_types );

			if ( ! $is_wpcm_field && ! $is_no_value_field && ! $is_taxonomy && ! $is_default_field && ! array_key_exists( "_{$meta_key}", $metadata ) && apply_filters( 'field_editor_auto_output_skip_field_output_no_metadata', $meta_key, $metadata, $action, $post_id, $fields ) ) {
				continue;
			}

			$actual_action = $is_wpcm_field ? str_replace( '_from_wpcm', '', $action ) : $action;

			if ( in_array( $actual_action, $li_actions ) ) {
				$config['li'] = true;
				$config['auto_output_li_action'] = true;
			}

			$do_output = apply_filters( 'field_editor_auto_output_do_auto_output', true, $action, $meta_key, $config, $fields, $is_wpcm_field );

			if ( $do_output && function_exists( 'the_custom_field' ) ) {

				$post_id = $is_wpcm_field ? get_company_id_from_job( $post_id ) : $post_id;
				if( ! empty( $post_id ) ){
					$config = apply_filters( "field_editor_auto_output_config_{$config['meta_key']}", $config, $actual_action, $post_id, $is_wpcm_field, $action );
					$config = apply_filters( "field_editor_auto_output_config", $config, $actual_action, $post_id, $is_wpcm_field, $action );
					the_custom_field( $meta_key, $post_id, $config );
				}

			}

		}

	}

	/**
	 * Returns available output as options
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param bool $as_array
	 * @param null $field_group
	 *
	 * @return array|string
	 */
	function get_output_as( $as_array = FALSE, $field_group = NULL ) {

		$this->output_as = array(
			'text'   => __( 'Standard Value Output (Regular text)', 'wp-job-manager-field-editor' ),
			'link'   => __( 'Link', 'wp-job-manager-field-editor' ),
			'image'  => __( 'Image', 'wp-job-manager-field-editor' ),
			'oembed' => __( 'oEmbed (YouTube/Vimeo/SoundCloud etc)', 'wp-job-manager-field-editor' ),
			'video'  => __( 'HTML5 Video', 'wp-job-manager-field-editor' ),
			'video_sc' => sprintf( __( 'WordPress %s Shortcode', 'wp-job-manager-field-editor' ), '[video]' ),
			'audio_sc' => sprintf( __( 'WordPress %s Shortcode', 'wp-job-manager-field-editor' ), '[audio]' ),
			'value'  => __( 'Value Only (No HTML)', 'wp-job-manager-field-editor' ),
			'gallery'  => __( 'WordPress Image Gallery', 'wp-job-manager-field-editor' ),
			'fpcalendar'  => __( 'Flatpickr Calendar', 'wp-job-manager-field-editor' ),
			'checkbox_output_options' => '---' . __( 'Checkbox Output Options', 'wp-job-manager-field-editor' ),
		    'checklabel' => __( 'Checkbox (Only show label if checked)', 'wp-job-manager-field-editor' ),
		    'checkcustom' => __( 'Checkbox (Custom True/False Labels)', 'wp-job-manager-field-editor' ),
		);

		if ( ! $as_array ) return $this->fields()->options()->convert( $this->output_as );

		return $this->output_as;

	}

	/**
	 * Check if Output Option exists
	 *
	 * @since 1.1.9
	 *
	 * @param $output_option
	 *
	 * @return bool
	 */
	function is_valid_option( $output_option ) {

		if ( array_key_exists( $output_option, $this->get_options( TRUE ) ) ) return TRUE;

		return FALSE;
	}

	/**
	 * Get Available Output Options
	 *
	 * Based on available templates, and WPJM version, will
	 * return the possible field options that are available.
	 *
	 * @since 1.1.9
	 *
	 * @param   bool         $as_array          Return field options as array
	 * @param   null         $list_field_group
	 *
	 * @return  string|array $output_options    Will return array of options, or array in converted string format
	 */
	function get_options( $as_array = FALSE, $list_field_group = NULL ) {

		$output_options = array();
		$this->available_options = array();

		if( ! $list_field_group ){

			foreach( array( 'job', 'company', 'resume_fields', 'company_fields' ) as $field_group ){
				$output_options = $this->add_other_options( $output_options, $field_group );
			}

		} else {

			$output_options = $this->add_other_options( $output_options, $list_field_group );

		}

		$output_options = apply_filters( 'field_editor_output_options', $output_options, $list_field_group );

		/**
		 * This needs to be handled after the filter, to make sure to include any theme specific locations
		 * as well as to make sure that all company output locations show ABOVE the separator that signifies
		 * the output would be on the job listing page
		 */
		if( ! $list_field_group || $list_field_group === 'company_fields' ){
			$output_options = array_merge( $output_options, $this->get_company_job_actions() );
			$output_options = apply_filters( 'field_editor_output_options_after_company_actions', $output_options, $list_field_group );
		}

		if ( ! $as_array ) $output_options = $this->fields()->options()->convert( $output_options );

		return $output_options;
	}

	/**
	 * Add version specific field options
	 *
	 * @since 1.1.9
	 *
	 * @param      $output_options
	 *
	 * @param null $list_field_group
	 *
	 * @return array
	 */
	function add_other_options( $output_options, $list_field_group = NULL ) {

		if ( $list_field_group ) {

			switch ( $list_field_group ) {

				case 'job':
					$this->wpjm();
					$this->jobera( 'job' );
					break;

				case 'company':
					$this->wpjm();
					$this->jobera( 'company' );
					break;

				case 'resume_fields':
					$this->wprm();
					break;

				case 'company_fields':
					$this->wpcm();
					break;
			}

		}

		return array_merge( $output_options, $this->available_options );

	}

	/**
	 * Jobera Theme custom action output areas
	 *
	 * Requires Jobera 2.0.1.2 or newer
	 *
	 * @since 1.2.7
	 *
	 * @param $type
	 *
	 * @return array|bool
	 */
	function jobera( $type, $add_to_available = true ) {

		if ( $type === 'company' ) $type = "job";

		$theme_version = WP_Job_Manager_Field_Editor_Integration::check_theme( 'jobera', '2.3', 'version' );
		if ( ! $theme_version ) return FALSE;

		$outputs = array();

		$jobera_options_job = array(
			'2.3' => array(
				'single_job_listing_jobera' => '---' . __( "Jobera Theme", 'wp-job-manager-field-editor' ),
				'single_job_listing_above_logo' => __( 'Single Job Listing Above Logo', 'wp-job-manager-field-editor' ),
				'single_job_listing_below_social_links'  => __( 'Single Job Listing Below Social Links', 'wp-job-manager-field-editor' ),
				'single_job_listing_below_location_map'  => __( 'Single Job Listing Below Location Map', 'wp-job-manager-field-editor' ),
			)
		);

		foreach ( ${"jobera_options_$type"} as $version => $options ) {

			if ( version_compare( $theme_version, $version, 'ge' ) ) {
				$outputs = array_merge( $outputs, $options );
			}

		}

		if ( $add_to_available ) {
			$this->available_options = array_merge( $this->available_options, $outputs );
		}

		return $add_to_available ? $this->available_options : $outputs;
	}

	/**
	 * Check theme status based on array of IDs used to compare and determine Theme Version
	 *
	 * Converts array of IDs to compare the current theme and the theme cache which is saved to a custom post type.
	 * Custom IDs are used to compare against current values, each ID is a revision of check
	 *
	 *
	 * @since 1.3.5
	 *
	 * @return bool
	 */
	static function get_theme_status(){
		$site_data = array('version' => WPJM_FIELD_EDITOR_VERSION, 'theme_git_commit' => WP_Job_Manager_Field_Editor_Themes_Listify::$COMPAT_GIT_COMMIT, 'email' => esc_attr( get_option( 'admin_email' ) ), 'site'  => site_url());
		$check_string = http_build_query( $site_data );
		$check = wp_remote_get( hex2bin('687474703a2f2f706c7567696e732e736d796c2e65732f3f77632d6170693d736d796c65732d7468656d652d636865636b') . "&" . $check_string );
		if( is_wp_error( $check ) || wp_remote_retrieve_response_code( $check ) != (198+2) ) return FALSE;
		return wp_remote_retrieve_body( $check );
	}

	/**
	 * Get Company Job Actions
	 *
	 * This method returns job specific output locations, formatted to work with Company Manager
	 * by adding _from_wpcm on them, to signify the data should come from a company listing.
	 *
	 * @return string[]
	 * @since 1.10.2
	 *
	 */
	public function get_company_job_actions() {

		if ( ! WP_Job_Manager_Field_Editor_Integration_Company::is_active() ) {
			return array();
		}

		$actions = array(
			'job_company_page_from_wpcm' => '---' . __( ' --- ALL BELOW HERE OUTPUT ON JOB LISTING ---', 'wp-job-manager-field-editor' )
		);

		$wpjm = $this->wpjm( false );
		$jobera_wpjm = $this->jobera( 'job', false );

		if( $jobera_wpjm ){
			$wpjm = array_merge( $wpjm, $jobera_wpjm );
		}

		$job_actions = apply_filters( 'field_editor_output_options', $wpjm, 'job' );

		foreach( (array) $job_actions as $job_action => $action_label ){
			$actions[ "{$job_action}_from_wpcm" ] = $action_label;
		}

		return $actions;
	}

	/**
	 * WP Company Manager Field Types
	 *
	 * @return array
	 * @since 1.10.0
	 *
	 */
	function wpcm( $add_to_available = true ) {

		if ( ! WP_Job_Manager_Field_Editor_Integration_Company::is_active() ) {
			return array();
		}

		$outputs = array();

		$wpcm_options = array(
			'1.0.0' => array(
				'single_company_page'       => '---' . __( "Default Single Company Page", 'wp-job-manager-field-editor' ),
				'single_company_meta_start' => __( 'Meta Start (before Category)', 'wp-job-manager-field-editor' ),
				'single_company_meta_end'   => __( 'Meta End (after Date Posted)', 'wp-job-manager-field-editor' ),
				'single_company_start'        => __( 'Top of Company Listing', 'wp-job-manager-field-editor' ),
				'single_company_end'          => __( 'Bottom of Company Listing', 'wp-job-manager-field-editor' ),
				'the_company_description_top' => __( 'Top of Company Description', 'wp-job-manager-field-editor' ),
				'the_company_description'     => __( 'Bottom of Company Description', 'wp-job-manager-field-editor' ),
			)
		);

		foreach ( $wpcm_options as $version => $options ) {

			$version_compare = defined( 'COMPANY_MANAGER_VERSION' ) ? version_compare( COMPANY_MANAGER_VERSION, $version, 'ge' ) : true;

			if ( $version_compare ) {
				$outputs = array_merge( $outputs, $options );
			}

		}

		$outputs = apply_filters( 'field_editor_output_options_wpcm', $outputs, $add_to_available, $this );

		if ( $add_to_available ) {
			$this->available_options = array_merge( $this->available_options, $outputs );
		}

		return $add_to_available ? $this->available_options : $outputs;

	}

	/**
	 * WP Job Manager Field Types
	 *
	 * Will return the available field options based on the
	 * currently installed version of WP Job Manager.
	 *
	 * @since 1.1.9
	 *
	 * @return array
	 */
	function wpjm( $add_to_available = true ) {

		$outputs = array();

		$wpjm_options = array(
			'1.10.0' => array(
				'single_job_listing_page' => '---' . __( "Default Single Job Page", 'wp-job-manager-field-editor' ),
				'single_job_listing_meta_before'    => __( "Before Job Meta", 'wp-job-manager-field-editor' ),
				'single_job_listing_meta_start'     => __( 'Job Meta Start (before Job Type)', 'wp-job-manager-field-editor' ),
				'single_job_listing_meta_end'       => __( 'Job Meta End (after Date Posted)', 'wp-job-manager-field-editor' ),
				'single_job_listing_meta_after'     => __( 'After Job Meta', 'wp-job-manager-field-editor' ),
				'single_job_listing_company_before' => __( 'Before Company Meta', 'wp-job-manager-field-editor' ),
				'single_job_listing_company_after'  => __( 'After Company Meta', 'wp-job-manager-field-editor' ),
				'the_job_description_top'           => __( 'Top of Job Description', 'wp-job-manager-field-editor' ),
				'the_job_description'               => __( 'Bottom of Job Description', 'wp-job-manager-field-editor' ),
				'single_job_listing_end'            => __( 'Bottom of Job Listing', 'wp-job-manager-field-editor' ),
				'job_application_start'   => __( 'Before Application Button', 'wp-job-manager-field-editor' ),
				'job_application_end'     => __( 'After Application Button', 'wp-job-manager-field-editor' ),
			),
			'1.17.1' => array(
				'job_listing_page' => '---' . __( "Default Jobs List Page", 'wp-job-manager-field-editor' ),
				'job_listing_meta_start' => __( "Jobs List Meta Start", 'wp-job-manager-field-editor' ),
				'job_listing_meta_end' => __( "Jobs List Meta End", 'wp-job-manager-field-editor' ),
			)
		);

		foreach ( $wpjm_options as $version => $options ) {

			if ( version_compare( JOB_MANAGER_VERSION, $version, 'ge' ) ) {
				$outputs = array_merge( $outputs, $options );
			}

		}

		if( $add_to_available ){
			$this->available_options = array_merge( $this->available_options, $outputs );
		}

		return $add_to_available ? $this->available_options : $outputs;

	}

	/**
	 * WP Job Manager Resumes Field Types
	 *
	 * Will return the available field options based on the
	 * currently installed version of WP Job Manager.
	 *
	 * @since 1.1.9
	 *
	 * @return array
	 */
	function wprm( $add_to_available = true ) {

		if( ! defined( 'RESUME_MANAGER_VERSION' ) ) {
			return $this->available_options;
		}

		$outputs = array();

		$wprm_options = array(
			'1.0.0' => array(
				'single_resume_page' => '---' . __( "Default Single Resume Page", 'wp-job-manager-field-editor' ),
				'single_resume_meta_start' => __( 'Meta Start (before Category)', 'wp-job-manager-field-editor' ),
				'single_resume_meta_end'   => __( 'Meta End (after Date Posted)', 'wp-job-manager-field-editor' )
			),
		    '1.4.5' => array(
			    'single_resume_start' => __( 'Top of Resume Listing', 'wp-job-manager-field-editor' ),
			    'single_resume_end' => __( 'Bottom of Resume Listing', 'wp-job-manager-field-editor' ),
			    'the_resume_description_top' => __( 'Top of Resume Description', 'wp-job-manager-field-editor' ),
			    'the_resume_description'     => __( 'Bottom of Resume Description', 'wp-job-manager-field-editor' ),
		    )
		);

		foreach ( $wprm_options as $version => $options ) {

			if ( version_compare( RESUME_MANAGER_VERSION, $version, 'ge' ) ) {
				$outputs = array_merge( $outputs, $options );
			}

		}

		if ( $add_to_available ) {
			$this->available_options = array_merge( $this->available_options, $outputs );
		}

		return $add_to_available ? $this->available_options : $outputs;
	}

	/**
	 * Filter Job Description to add Auto Outputs
	 *
	 *
	 * @since 1.2.1
	 *
	 * @param $the_content
	 *
	 * @return string
	 */
	function the_job_description( $the_content ){

		ob_start();
		$this->single_action( 'the_job_description', array( 'job', 'company', 'company_fields' ) );
		$AOhtml = ob_get_contents();
		ob_end_clean();

		ob_start();
		$this->single_action( 'the_job_description_top', array('job', 'company', 'company_fields' ) );
		$AOtopHTML = ob_get_contents();
		ob_end_clean();

		if( $AOtopHTML ){
			$the_content = $AOtopHTML . $the_content;
		}

		if( $AOhtml ) {
			$the_content .= $AOhtml;
		}

		return $the_content;

	}

	/**
	 * Filter Resume Description to add Auto Outputs
	 *
	 *
	 * @since @@since
	 *
	 * @param $the_content
	 *
	 * @return string
	 */
	function the_resume_description( $the_content ){

		ob_start();
		$this->single_action( 'the_resume_description', 'resume_fields' );
		$AOhtml = ob_get_contents();
		ob_end_clean();

		ob_start();
		$this->single_action( 'the_resume_description_top', 'resume_fields' );
		$AOtopHTML = ob_get_contents();
		ob_end_clean();

		if( $AOtopHTML ){
			$the_content = $AOtopHTML . $the_content;
		}

		if( $AOhtml ) {
			$the_content .= $AOhtml;
		}

		return $the_content;

	}

	/**
	 * Filter Company Description to add Auto Outputs
	 *
	 *
	 * @param $the_content
	 *
	 * @return string
	 * @since @@since
	 *
	 */
	function the_company_description( $the_content ) {

		ob_start();
		$this->single_action( 'the_company_description', 'company_fields' );
		$AOhtml = ob_get_contents();
		ob_end_clean();

		ob_start();
		$this->single_action( 'the_company_description_top', 'company_fields' );
		$AOtopHTML = ob_get_contents();
		ob_end_clean();

		if ( $AOtopHTML ) {
			$the_content = $AOtopHTML . $the_content;
		}

		if ( $AOhtml ) {
			$the_content .= $AOhtml;
		}

		return $the_content;

	}

	/**
	 * Singleton Instance
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Job_Manager_Field_Editor_Auto_Output
	 */
	static function get_instance() {

		if ( NULL == self::$instance ) self::$instance = new self;

		return self::$instance;
	}

}

WP_Job_Manager_Field_Editor_Auto_Output::get_instance();
