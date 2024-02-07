<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Debugging helper
 *
 * @param [type] $expression
 * @since 1.7.0
 */
function dump( $expression ) {
	if ( ! \Cariera\is_debug_mode() || ! \Cariera\is_dev_mode() ) {
		return;
	}

	echo '<pre>';
	foreach ( func_get_args() as $expression ) {
		var_dump( $expression );
		echo '<hr>';
	}
	echo '</pre>';
}

/**
 * Debugging helper
 *
 * @since 1.7.0
 */
function dd() {
	foreach ( func_get_args() as $expression ) {
		dump( $expression );
	}
	die;
}

/**
 * Output on debug.log
 *
 * @since 1.7.0
 */
function write_log( $log ) {
	if ( is_array( $log ) || is_object( $log ) ) {
		error_log( print_r( $log, true ) );
	} else {
		error_log( $log );
	}
}
