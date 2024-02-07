<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Resume_Manager {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since 1.7.2
	 */
	public function __construct() {
		// Init Classes.
		new \Cariera_Core\Core\Resume_Manager\Resumes_Extender();
		new \Cariera_Core\Core\Resume_Manager\Fields();
		new \Cariera_Core\Core\Resume_Manager\Search();
		new \Cariera_Core\Core\Resume_Manager\Settings();
		new \Cariera_Core\Core\Resume_Manager\Taxonomy();
	}
}
