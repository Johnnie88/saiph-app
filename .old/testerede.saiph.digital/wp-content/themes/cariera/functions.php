<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CARIERA_VERSION', '1.7.2' );
define( 'CARIERA_PLUGINS_URL', 'kgezo6eh2tWPgran' );

// Define Cariera Dev Mode.
if ( ! defined( 'CARIERA_DEV_MODE' ) ) {
	define( 'CARIERA_DEV_MODE', false );
}

// Define Cariera Demo Mode.
if ( ! defined( 'CARIERA_DEMO' ) ) {
	define( 'CARIERA_DEMO', false );
}

/**
 * Check if debug mode is enabled
 *
 * @since 1.7.0
 */
function is_debug_mode() {
	return defined( 'WP_DEBUG' ) && WP_DEBUG;
}

/**
 * Check if dev mode is enabled
 *
 * @since 1.7.0
 */
function is_dev_mode() {
	return defined( 'CARIERA_DEV_MODE' ) && CARIERA_DEV_MODE;
}



/**
 * Check if demo mode is enabled
 *
 * @since 1.7.0
 */
function is_demo_mode() {
	return defined( 'CARIERA_DEMO' ) && CARIERA_DEMO;
}

require_once locate_template( 'inc/autoload.php' );



