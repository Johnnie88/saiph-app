<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Integration_Company
 */
class WP_Job_Manager_Field_Editor_Integration_Company {

	/**
	 * @var \WP_Job_Manager_Field_Editor_Integration_Company
	 */
	private static $instance;
	/**
	 * @var null|bool
	 */
	public static $active = null;

	/**
	 * Singleton Instance
	 *
	 * @return WP_Job_Manager_Field_Editor_Integration_Company
	 * @since 1.10.0
	 *
	 */
	static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/**
	 * WP_Job_Manager_Field_Editor_Integration_Company constructor.
	 */
	public function __construct() {
		if( $fields_class = self::get_fields_class() ){
			new $fields_class();
		}
	}

	/**
	 * Get Fields Class
	 *
	 * @return false|mixed|string|void
	 * @since 1.10.2
	 *
	 */
	public static function get_fields_class() {
		$class_name = self::get_fields_class_name();
		if( class_exists( $class_name ) ){
			return $class_name;
		}

		return false;
	}

	/**
	 * Get Submit Form Class
	 *
	 * @return false|mixed|string|void
	 * @since 1.10.2
	 *
	 */
	public static function get_submit_form_class() {
		$class_name = self::get_submit_form_class_name();
		if( class_exists( $class_name ) ){
			return $class_name;
		}

		return false;
	}

	/**
	 * Get Company Post Type
	 *
	 * @return mixed|void
	 * @since 1.11.3
	 *
	 */
	public static function get_post_type() {
		return apply_filters( 'field_editor_company_post_type', 'company' );
	}

	/**
	 * Get Fields Class Name
	 *
	 * @return mixed|void
	 * @since 1.10.2
	 *
	 */
	public static function get_fields_class_name(){
		return apply_filters( 'field_editor_company_fields_class_name', 'WP_Job_Manager_Field_Editor_Company_Fields' );
	}

	/**
	 * Get Submit Form Class Name
	 *
	 * @return mixed|void
	 * @since 1.10.2
	 *
	 */
	public static function get_submit_form_class_name() {
		return apply_filters( 'field_editor_company_submit_form_class_name', 'WP_Job_Manager_Field_Editor_Company_Submit_Form' );
	}

	/**
	 * Is Company Integration Active?
	 *
	 * @return mixed|void
	 * @since 1.10.2
	 *
	 */
	public static function is_active() {

		if( is_null( self::$active ) ){

			$wpcm   = 'wp-company-manager/wp-company-manager.php';
			$active = false;

			if ( defined( 'COMPANY_MANAGER_PLUGIN_DIR' ) ) {
				$active = true;
			}

			if ( ! $active && ! function_exists( 'is_plugin_active' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			if ( ! $active && is_plugin_active( $wpcm ) ) {
				$active = true;
			}

			if ( ! $active && class_exists( 'WP_Company_Manager' ) ) {
				$active = true;
			}

			self::$active = apply_filters( 'field_editor_wpcm_active', $active );
		}

		return self::$active;
	}
}
