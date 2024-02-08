<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Init {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		require_once locate_template( 'inc/utils.php' );

		\Cariera\Setup::instance();
		\Cariera\Assets::instance();
		\Cariera\Dashboard::instance();
		\Cariera\Demo::instance();
		\Cariera\Theme_Support::instance();
		\Cariera\Integrations::instance();
		\Cariera\Onboarding\Onboarding::instance();
		\Cariera\Woocommerce::instance();

		// Require.
		require_once locate_template( '/inc/customizer.php' );
		require_once locate_template( '/inc/mega-menu.php' );
		require_once locate_template( '/inc/font-icons.php' );
		require_once locate_template( '/inc/deprecated.php' );

		// Require only if wpjm is activated.
		if ( \Cariera\wp_job_manager_is_activated() ) {
			require_once locate_template( '/inc/wp-job-manager/functions.php' );
		}

		// Require only if wpjm and wp resume manager are activated.
		if ( \Cariera\wp_job_manager_is_activated() && \Cariera\wp_resume_manager_is_activated() ) {
			require_once locate_template( '/inc/wp-resume-manager/functions.php' );
		}
	}
}
