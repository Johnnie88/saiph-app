<?php
namespace Cariera_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoload Core classes and files
 *
 * @since  1.7.2
 *
 * @param $class
 */
function autoload( $class ) {

	// Namespace prefix.
	$prefix = 'Cariera_Core\\';

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		// No, move to the next registered autoloader.
		return;
	}

	// Het the relative class name.
	$relative_class = substr( $class, $len );

	// Replace '_' with '-'.
	$classname = strtolower( str_replace( '_', '-', $relative_class ) );

	// Replace the namespace prefix with the base directory, replace namespace
	// Separators with directory separators in the relative class name, append with .php.
	$file = strtolower( str_replace( '\\', '/', $classname ) ) . '.php';

	// error_log( $file );

	$file_location = untrailingslashit( CARIERA_CORE_PATH ) . "/inc/{$file}";
	if ( file_exists( $file_location ) ) {
		include_once $file_location;
	}
}

spl_autoload_register( 'Cariera_Core\autoload' );
