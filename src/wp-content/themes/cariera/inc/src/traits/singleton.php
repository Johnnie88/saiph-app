<?php

namespace Cariera\Src\Traits;

trait Singleton {

	/**
	 * The single instance of the class.
	 */
	protected static $instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  1.7.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
