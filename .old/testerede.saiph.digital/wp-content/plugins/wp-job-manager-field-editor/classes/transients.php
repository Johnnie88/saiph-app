<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Field_Editor_Transients {

	private static $instance;
	protected $prefix = 'fields';
	protected $cache_prefix = 'jmfe_';
	private static $enabled = null;

	public function __construct() {
		add_filter( 'job_manager_field_editor_settings', array( $this, 'settings' ) );
		add_action( 'updated_postmeta', array( $this, 'post_update_check' ), 99, 4 );
		add_action( 'job_manager_field_editor_flush_cache', array( $this, 'field_config_changed' ) );
		add_action( 'job_manager_field_editor_flush_cache_from_ajax', array( $this, 'field_config_changed' ) );
	}

	/**
	 * Check post updated
	 *
	 * This method checks whenever a post is updated, if it's our custom post type,
	 * and if so, flush field cache so it can be regenerated.
	 *
	 *
	 * @since 1.6.0
	 *
	 * @param $meta_id
	 * @param $object_id
	 * @param $meta_key
	 * @param $meta_value
	 */
	function post_update_check( $meta_id, $object_id, $meta_key, $meta_value ){

		if ( ! self::is_enabled() || get_post_type( $object_id ) !== 'jmfe_custom_fields' ) return;

		if( ! has_action( 'shutdown', array($this, 'field_config_changed') ) ){
			add_action( 'shutdown', array($this, 'field_config_changed') );
		}

	}

	/**
	 * Purge cache due to field configuration change
	 *
	 *
	 * @since 1.6.0
	 *
	 */
	function field_config_changed(){

		do_action( 'job_manager_field_editor_field_config_changed', $this );

		if( ! self::is_enabled() ) return;

		/**
		 * Check if flush cache was called from AJAX action
		 */
		if( doing_action( 'job_manager_field_editor_flush_cache_from_ajax' ) ){

			/**
			 * If so, let's remove any calls in shutdown action since we're already
			 * flushing the cache, no need to duplicate flushing.
			 *
			 * The call to flush in the shutdown action is added whenever our custom
			 * post type's meta is updated.  This works fine normally whenever there
			 * is a page reload, but if the call to flush cache was made via an AJAX
			 * action, there's no need to flush on shutdown anymore.
			 */
			if ( has_action( 'shutdown', array($this, 'field_config_changed') ) ) {
				remove_action( 'shutdown', array($this, 'field_config_changed') );
			}
		}

		// Flush the cache
		$this->purge();
		$this->purge( FALSE );
	}

	/**
	 * Add Transient/Cache settings tab to Field Editor Settings page
	 *
	 *
	 * @since @@since
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	function settings( $settings ) {

		$settings['cache'] = array(
			__( 'Cache', 'wp-job-manager-field-editor' ),
			array(
				array(
					'name'       => 'jmfe_enable_cache',
					'std'        => '1',
					'label'      => __( 'Enable Cache', 'wp-job-manager-field-editor' ),
					'cb_label'   => __( 'Yes, enable caching of all field configuration', 'wp-job-manager-field-editor' ),
					'desc'       => __( 'This plugin uses WordPress transients to cache field configs to prevent excessive, and unecessary database queries. <strong><em>Whenever a new field is added, or updated, this cache is automatically purged and updated.</em></strong>.  Disable this if you have issues with your custom filters not working correctly, or while debugging.', 'wp-job-manager-field-editor' ),
					'type'       => 'checkbox',
					'attributes' => array()
				),
				array(
					'name'       => 'jmfe_cache_expiration',
					'std'        => 4 * WEEK_IN_SECONDS,
					'label'      => __( 'Expiration', 'wp-job-manager-field-editor' ),
					'desc'       => __( 'This will be the amount of time the cache is stored before it is automatically removed and has to be regenerated.', 'wp-job-manager-field-editor' ),
					'type'       => 'select',
					'attributes' => array(),
					'options'    => array(
						1 * MINUTE_IN_SECONDS  => __( '1 Minute', 'wp-job-manager-field-editor' ),
						5 * MINUTE_IN_SECONDS  => __( '5 Minutes', 'wp-job-manager-field-editor' ),
						15 * MINUTE_IN_SECONDS => __( '15 Minutes', 'wp-job-manager-field-editor' ),
						30 * MINUTE_IN_SECONDS => __( '30 Minutes', 'wp-job-manager-field-editor' ),
						HOUR_IN_SECONDS        => __( '1 Hour', 'wp-job-manager-field-editor' ),
						12 * HOUR_IN_SECONDS   => __( '12 Hours', 'wp-job-manager-field-editor' ),
						24 * HOUR_IN_SECONDS   => __( '24 Hours', 'wp-job-manager-field-editor' ),
						WEEK_IN_SECONDS        => __( '1 Week', 'wp-job-manager-field-editor' ),
						2 * WEEK_IN_SECONDS    => __( '2 Weeks', 'wp-job-manager-field-editor' ),
						4 * WEEK_IN_SECONDS    => __( '1 Month', 'wp-job-manager-field-editor' ),
						12 * WEEK_IN_SECONDS   => __( '3 Months', 'wp-job-manager-field-editor' ),
						24 * WEEK_IN_SECONDS   => __( '6 Months', 'wp-job-manager-field-editor' ),
						YEAR_IN_SECONDS        => __( '1 Year', 'wp-job-manager-field-editor' ),
					)
				),
				array(
					'name'        => 'jmfe_cache_purge',
					'caption'     => __( 'Purge All', 'wp-job-manager-field-editor' ),
					'field_class' => 'button-primary',
					'action'      => 'cache_purge_all',
					'label'       => __( 'Purge', 'wp-job-manager-field-editor' ),
					'desc'        => __( 'This will purge all field configuration cache, which will be regenerated immediately (and automatically).', 'wp-job-manager-field-editor' ),
					'type'        => 'cache_button',
					'cache_count' => 'count'
				),
				array(
					'name'        => 'jmfe_cache_flush_all',
					'caption'     => __( 'WP Cache', 'wp-job-manager-field-editor' ),
					'field_class' => 'button-primary',
					'action'      => 'cache_flush_all',
					'label'       => __( 'Flush Cache', 'wp-job-manager-field-editor' ),
					'desc'        => __( 'This will flush the entire WordPress core cache.', 'wp-job-manager-field-editor' ),
					'type'        => 'cache_button'
				),
			),
		);

		return $settings;
	}

	/**
	 * Return if Cache is Enabled
	 *
	 *
	 * @since 1.6.0
	 *
	 * @return mixed|void
	 */
	function cache_enabled() {
		return self::is_enabled();
	}

	/**
	 * Check if caching is enabled
	 *
	 * This method uses a static variable to set whether cache is enabled to prevent
	 * excessive calls to get_option() to check configuration.
	 *
	 *
	 * @since 1.6.0
	 *
	 * @return mixed|null|void
	 */
	static function is_enabled(){

		if ( self::$enabled === NULL ) {
			self::$enabled = get_option( 'jmfe_enable_cache', TRUE );
		}

		return self::$enabled;
	}

	/**
	 * Get Cached Value
	 *
	 * Will return cached value, or false if cache does not exist.
	 *
	 *
	 * @since 1.6.0
	 *
	 * @param string $append
	 *
	 * @return bool|mixed
	 */
	function get( $append = '' ) {

		if ( ! $this->cache_enabled() || isset( $_GET[ 'no_cache' ] ) ){
			return FALSE;
		}

		// Check language used when transient was set
		$cache_lang = get_transient( "{$this->cache_prefix}{$this->prefix}_{$append}_lang" );

		// If it does not match the current language, return false to force an update
		$cache_value = $cache_lang == get_locale() ? get_transient( "{$this->cache_prefix}{$this->prefix}_{$append}" ) : FALSE;

		return $cache_value;
	}

	/**
	 * Set Cache Value
	 *
	 * Set cache value from data
	 *
	 * @since 1.6.0
	 *
	 * @param  string $append
	 * @param         $data
	 * @param  null   $expire
	 *
	 * @return bool     False if value was not set and true if value was set.
	 */
	function set( $append = '', $data = '', $expire = NULL ) {

		if ( ! $this->cache_enabled() || isset( $_GET[ 'no_cache' ] ) ) return FALSE;

		// Default expiration
		if ( ! $expire ) $expire = ( $default_expire = get_option( 'jmfe_cache_expiration' ) ) === FALSE ? 4 * WEEK_IN_SECONDS : $default_expire;

		$expire = apply_filters( 'job_manager_field_editor_cache_expiration', $expire, $append, $data );
		return set_transient( "{$this->cache_prefix}{$this->prefix}_{$append}", $data, $expire );

	}

	/**
	 * Remove Cache Value
	 *
	 *
	 * @since 1.6.0
	 *
	 * @param string $append
	 *
	 * @return bool
	 */
	function remove( $append = '' ) {

		// Remove language transient if it exists
		delete_transient( "{$this->cache_prefix}{$this->prefix}_{$append}_lang" );

		return delete_transient( "{$this->cache_prefix}{$this->prefix}_{$append}" );

	}

	/**
	 * Count Cached Values
	 *
	 *
	 * @since 1.6.0
	 *
	 * @param bool $with_timeout Count caches with timeouts (Default: true)
	 *
	 * @return int
	 */
	function count( $with_timeout = TRUE ) {

		global $wpdb;

		$prefix  = esc_sql( "{$this->cache_prefix}{$this->prefix}" );
		$timeout = $with_timeout ? '_timeout' : '';

		$options = $wpdb->options;

		$t = esc_sql( "_transient{$timeout}_{$prefix}%" );

		$sql = $wpdb->prepare(
			"
      SELECT option_name
      FROM $options
      WHERE option_name LIKE '%s'
    ",
			$t
		);

		$transients = $wpdb->get_col( $sql );

		return count( $transients );
	}

	/**
	 * Purge All Cached Values
	 *
	 * By default this will purge all cached values with expirations (caches should have an expiration).
	 * Call with $set_timeout as FALSE to purge all caches without an expiration.  Purge is called from
	 * settings both with and without timeout when purge/clear all cache values.
	 *
	 *
	 * @since 1.6.0
	 *
	 * @param bool $with_timeout Whether or not to purge values without a timeout
	 */
	function purge( $with_timeout = TRUE ) {

		global $wpdb;

		$prefix  = esc_sql( "{$this->cache_prefix}{$this->prefix}" );
		$timeout = $with_timeout ? '_timeout' : '';

		$options = $wpdb->options;

		$t = esc_sql( "_transient{$timeout}_{$prefix}%" );

		$sql = $wpdb->prepare(
			"
      SELECT option_name
      FROM $options
      WHERE option_name LIKE '%s'
    ",
			$t
		);

		$transients = $wpdb->get_col( $sql );

		// For each transient...
		foreach( $transients as $transient ) {

			// Strip away the WordPress prefix in order to arrive at the transient key.
			$key = str_replace( "_transient{$timeout}_", '', $transient );

			// Now that we have the key, use WordPress core to the delete the transient.
			delete_transient( $key );

		}

		// But guess what?  Sometimes transients are not in the DB, so we have to do this too:
		wp_cache_flush();
	}

	/**
	 * Check if cache needs resume update
	 *
	 * This method checks if Resumes is enabled, and if so, checks if the cache value has
	 * resume fields set.  Will return TRUE or FALSE based on whether the field cache
	 * needs to be updated or not.
	 *
	 *
	 * @since 1.6.0
	 *
	 * @param $fields
	 *
	 * @return bool
	 */
	function needs_resume_update( $fields ) {
		if( empty( $fields ) ) return true;
		// Return opposite result -- if resume_fields key exists, or not enabled
		return ! ( array_key_exists( 'resume_fields', $fields ) || ! WP_Job_Manager_Field_Editor::resumes_active() );
	}

	/**
	 * Get field data
	 *
	 * This method will attempt to pull field data from WordPress transient cache,
	 * or call the specific method to regenerate field data, and save to transient cache.
	 *
	 *
	 * @since 1.6.0
	 *
	 * @param string $type      Type of data to return (default, custom, customized, all)
	 * @param bool   $force     Whether to force an update even if cache already exists
	 *
	 * @return array
	 */
	function get_data( $type = 'all', $force = false ){

		$cache_check = $this->get( $type );

		if ( empty( $cache_check ) || $force || $this->needs_resume_update( $cache_check ) ) {
			$fields                   = WP_Job_Manager_Field_Editor_Fields::get_instance();
			$field_data               = call_user_func( array( $fields, "get_{$type}_fields" ), null, true );

			$this->set( $type, $field_data );
			$this->set( "{$type}_lang", get_locale() );

			return $field_data;
		}

		return $cache_check;

	}

	/**
	 * Singleton Instance
	 *
	 * @since @@since
	 *
	 * @return WP_Job_Manager_Field_Editor_Transients|boolean   Returns FALSE if caching is disabled, or class object when enabled.
	 */
	static function get_instance() {

		if( ! self::is_enabled() ){
			return false;
		}

		if ( NULL == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Singleton Instance
	 *
	 * This should only be called once by the main class to make sure constuct is run
	 * to add settings, etc.  Use get_instance() to return class object for usage in
	 * plugins or themes.
	 *
	 * @since @@since
	 *
	 * @return WP_Job_Manager_Field_Editor_Transients
	 */
	static function first_init() {

		if ( NULL == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}