<?php

namespace Cariera\Integrations\WPJM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Alerts {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since   1.7.0
	 * @version 1.7.0
	 */
	public function __construct() {
		if ( ! class_exists( 'WP_Job_Manager_Alerts' ) ) {
			return;
		}

		add_filter( 'job_manager_alerts_login_url', [ $this, 'job_alert_login_url' ] );
	}

	/**
	 * Job Alert Login URL
	 *
	 * @since 1.2.3
	 */
	public function job_alert_login_url() {
		$login_registration = get_option( 'cariera_login_register_layout' );

		if ( 'popup' === $login_registration ) {
			$login_registration_page = get_option( 'woocommerce_myaccount_page_id' );
		} else {
			$login_registration_page = get_option( 'cariera_login_register_page' );
		}

		return get_permalink( $login_registration_page );
	}
}
