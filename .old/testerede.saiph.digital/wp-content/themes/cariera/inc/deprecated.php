<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'cariera_core_is_activated' ) ) {
	/**
	 * Check if Cariera Core plugin is activated.
	 *
	 * @deprecated 1.7.0
	 */
	function cariera_core_is_activated() {
			_deprecated_function( __FUNCTION__, '1.7.0', '\Cariera\cariera_core_is_activated()' );

			return class_exists( 'Cariera_Core' ) ? true : false;
	}
}
