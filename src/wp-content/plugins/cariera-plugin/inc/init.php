<?php

namespace Cariera_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Init {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since 1.7.2
	 */
	public function __construct() {

		// Init Classes.
		new \Cariera_Core\Core();
		new \Cariera_Core\Elementor();
		new \Cariera_Core\Shortcodes();

		// Job Manager.
		if ( class_exists( 'WP_Job_Manager' ) ) {
			\Cariera_Core\Core\Job_Manager::instance();
		}

		// Resume Manager.
		if ( class_exists( 'WP_Job_Manager' ) && class_exists( 'WP_Resume_Manager' ) ) {
			\Cariera_Core\Core\Resume_Manager::instance();
		}

		// Extensions.
		\Cariera_Core\Extensions\Recaptcha\Recaptcha::init();
		\Cariera_Core\Extensions\Testimonials\Testimonials::instance();

		// Include Files.
		include_once CARIERA_CORE_PATH . '/inc/helpers.php';

		// TODO: Importer will be reworked ver: 1.7.3.
		// Importer.
		include_once CARIERA_CORE_PATH . '/inc/importer/core.php';
		include_once CARIERA_CORE_PATH . '/inc/importer/importer/cariera-importer.php';
		include_once CARIERA_CORE_PATH . '/inc/importer/init.php';

		// Extensions.
		include_once CARIERA_CORE_PATH . '/inc/extensions/social-share/social.php';
		// TODO: This will be rewritten on the new statistics update ver: 1.7.5.
		include_once CARIERA_CORE_PATH . '/inc/extensions/dashboard/reports.php';
		include_once CARIERA_CORE_PATH . '/inc/extensions/dashboard/views.php';
	}
}
