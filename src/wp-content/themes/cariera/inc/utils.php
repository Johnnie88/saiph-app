<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Require util files.
 * 
 * @since   1.7.0
 * @version 1.7.0
 */
require_once locate_template( 'inc/utils/dev.php' );
require_once locate_template( 'inc/utils/helper.php' );
require_once locate_template( 'inc/utils/generic.php' );

/**
 * Get assets version
 *
 * @since 1.7.0
 * 
 * @todo check $version if child theme is enabled.
 */
function get_assets_version() {
	static $version;
	if ( ! is_null( $version ) ) {
		return $version;
	}

	$version = \Cariera\is_dev_mode() ? wp_rand( 1, 1e4 ) : wp_get_theme( get_template() )->get( 'Version' );

	return $version;
}

/**
 * Check if Cariera Core plugin is activated.
 *
 * @since 1.7.0
 */
function cariera_core_is_activated() {
	return class_exists( 'Cariera_Core' ) ? true : false;
}

/**
 * Check if WooCommerce is activated.
 *
 * @since 1.7.0
 */
function wc_is_activated() {
	return class_exists( 'WooCommerce' ) ? true : false;
}

/**
 * Check if WP Job Manager is activated.
 *
 * @since 1.7.0
 */
function wp_job_manager_is_activated() {
	return class_exists( 'WP_Job_Manager' ) ? true : false;
}

/**
 * Check if WP Resume Manager is activated.
 *
 * @since 1.7.0
 */
function wp_resume_manager_is_activated() {
	return class_exists( 'WP_Resume_Manager' ) ? true : false;
}

/**
 * Check if Cariera Company Manager is activated.
 *
 * @since 1.7.2
 */
function company_manager_is_activated() {
	return class_exists( '\Cariera_Core\Core\Company_Manager\Company_Manager' ) ? true : false;
}

/**
 * Check if Elementor is activated.
 *
 * @since 1.7.0
 */
function is_elementor_active() {
	return class_exists( '\Elementor\Plugin' );
}

/**
 * Check if Elementor is editing mode.
 *
 * @since 1.7.0
 */
function is_elementor_edit_mode() {
	return \Cariera\is_elementor_active() && \Elementor\Plugin::$instance->editor->is_edit_mode();
}

/**
 * Check if Elementor is preview mode.
 *
 * @since 1.7.0
 */
function is_elementor_preview_mode() {
	return \Cariera\is_elementor_active() && \Elementor\Plugin::$instance->preview->is_preview_mode();
}

/**
 * Check if Elementor is ajaxing.
 *
 * @since 1.7.0
 */
function is_elementor_ajax() {
	return ! empty( $_REQUEST['_nonce'] ) && wp_verify_nonce( $_REQUEST['_nonce'], 'elementor_ajax' );
}
