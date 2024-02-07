<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		add_action( 'cariera_dashboard_nav_inner_start', [ $this, 'dashboard_profile' ], 10 );
		add_action( 'cariera_dashboard_menu', [ $this, 'dashboard_main_menu' ], 10 );
		add_action( 'cariera_dashboard_menu', [ $this, 'dashboard_listing_menu' ], 11 );
		add_action( 'cariera_dashboard_menu', [ $this, 'dashboard_account_menu' ], 12 );
		add_action( 'cariera_dashboard_content_start', [ $this, 'dashboard_titlebar' ], 10 );
		add_action( 'cariera_dashboard_content_end', [ $this, 'dashboard_copyright' ], 10 );
		add_action( 'wp', [ $this, 'remove_wc_nav_on_dash' ] );
	}

	/**
	 * Dashboard Navigation - Profile Box
	 *
	 * @since   1.4.0
	 * @version 1.7.0
	 */
	public function dashboard_profile() {
		get_template_part( 'templates/dashboard/profile' );
	}

	/**
	 * Dashboard Main Menu
	 *
	 * @since   1.3.4
	 * @version 1.7.0
	 */
	public function dashboard_main_menu() {
		get_template_part( 'templates/dashboard/main-menu' );
	}

	/**
	 * Dashboard Listing Menu
	 *
	 * @since   1.3.4
	 * @version 1.7.0
	 */
	public function dashboard_listing_menu() {
		get_template_part( 'templates/dashboard/listing-menu' );
	}

	/**
	 * Dashboard Account Menu
	 *
	 * @since   1.3.4
	 * @version 1.7.0
	 */
	public function dashboard_account_menu() {
		get_template_part( 'templates/dashboard/account-menu' );
	}

	/**
	 * Dashboard Title Bar
	 *
	 * @since   1.3.4
	 * @version 1.7.0
	 */
	public function dashboard_titlebar() {
		get_template_part( 'templates/dashboard/titlebar' );
	}

	/**
	 * Dashboard Copyright Footer
	 *
	 * @since   1.3.4
	 * @version 1.7.0
	 */
	public function dashboard_copyright() {
		get_template_part( 'templates/dashboard/copyright' );
	}

	/**
	 * Remove WooCommerce Nav on User Dashboard Template
	 *
	 * @since   1.3.5
	 * @version 1.7.0
	 */
	public function remove_wc_nav_on_dash() {
		if ( is_page_template( 'templates/user-dashboard.php' ) ) {
			remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation' );
		}
	}
}
