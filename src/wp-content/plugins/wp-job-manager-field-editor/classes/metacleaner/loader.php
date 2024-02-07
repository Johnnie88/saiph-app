<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta Cleaner Loader
 * @version 1.0.0
 */
class WP_Job_Manager_Field_Editor_Metacleaner_Loader {

	/**
	 * Meta Cleaner Loader
	 */
	public function __construct() {
		add_filter( 'job_manager_meta_cleaner_versions', array( $this, 'meta_cleaner_versions' ) );
		add_action( 'plugins_loaded', array( $this, 'meta_cleaner' ) );
	}

	/**
	 * Version Handling (to load latest version)
	 *
	 * @param $versions
	 *
	 * @since 1.0.0
	 *
	 */
	public function meta_cleaner_versions( $versions ) {

		$versions[] = array(
			'version' => '1.0.1',
			'file'    => WPJM_FIELD_EDITOR_PLUGIN_DIR . '/classes/metacleaner/cleaner.php',
			'assets' => WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/metacleaner'
		);

		return $versions;
	}

	/**
	 * Initialize Meta Cleaner
	 *
	 * @since 1.0.0
	 *
	 */
	public function meta_cleaner() {

		/**
		 * If class already exists, means another plugin has already
		 * loaded the cleaner
		 */
		if( class_exists( '\sMyles\WPJM\EMC\Cleaner') ){
			return;
		}

		$versions = apply_filters( 'job_manager_meta_cleaner_versions', array() );

		if( empty( $versions ) ){
			return;
		}

		usort( $versions, function( $a, $b ){
			return -1 * version_compare( $a['version'], $b['version'] );
		});

		if( ! isset( $versions[0], $versions[0]['file'] ) ){
			return;
		}

		require_once $versions[0]['file'];

		\sMyles\WPJM\EMC\Cleaner::get_instance( $versions[0]['assets'] );
	}
}