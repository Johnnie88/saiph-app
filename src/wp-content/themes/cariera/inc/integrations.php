<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Integrations {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		// Loading Classes for integrations.
		\Cariera\Integrations\Polylang::instance();
		\Cariera\Integrations\WPML::instance();

		// WPJM & Addons integration.
		if ( \Cariera\wp_job_manager_is_activated() ) {
			\Cariera\Integrations\WPJM\Alerts::instance();
			\Cariera\Integrations\WPJM\Bookmarks::instance();
			\Cariera\Integrations\WPJM\Field_Editor::instance();
			\Cariera\Integrations\WPJM\Job_Manager::instance();
		}

		if ( \Cariera\wp_job_manager_is_activated() && \Cariera\wp_resume_manager_is_activated() ) {
			\Cariera\Integrations\WPJM\Resume_Manager::instance();
		}
	}
}
