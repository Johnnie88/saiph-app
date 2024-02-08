<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job_Manager {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since 1.7.2
	 */
	public function __construct() {
		// Init Classes.
		new \Cariera_Core\Core\Job_Manager\Jobs_Extender();
		new \Cariera_Core\Core\Job_Manager\Fields();
		new \Cariera_Core\Core\Job_Manager\Search();
		new \Cariera_Core\Core\Job_Manager\Settings();
		new \Cariera_Core\Core\Job_Manager\Taxonomy();
		\Cariera_Core\Core\Job_Manager\Type_Colors::instance();
		\Cariera_Core\Core\Job_Manager\Maps::instance();
		\Cariera_Core\Core\Job_Manager\Writepanels::instance();

		// Cariera Company Manager.
		$GLOBALS['cariera_company_manager'] = new \Cariera_Core\Core\Company_Manager\Company_Manager();
	}
}
