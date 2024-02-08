<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoload function
 *
 * @param  mixed $classname
 * @return void
 *
 * @since 1.7.0
 */
function cariera_autoload( $classname ) {

	$parts = explode( '\\', $classname );
	if ( 'Cariera' !== $parts[0] ) {
		return;
	}

	$parts[0] = 'inc';

	$path_parts = array_map(
		function( $part ) {
			return strtolower( str_replace( '_', '-', $part ) );
		},
		$parts
	);

	$path = join( DIRECTORY_SEPARATOR, $path_parts ) . '.php';
	if ( locate_template( $path ) ) {
		require_once locate_template( $path );
	}
}

spl_autoload_register( 'cariera_autoload' );

// Initializer.
\Cariera\Init::instance();
