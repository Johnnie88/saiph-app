<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since   1.4.3
	 * @version 1.6.2
	 */
	public function __construct() {
		// Register Assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );

		// Enqueue Assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ], 20 );
	}

	/**
	 * Register Core Plugin assets.
	 *
	 * @since   1.6.3
	 * @version 1.7.1
	 */
	public function register_assets() {
		$suffix = is_rtl() ? '.rtl' : '';

		// Main Core Frontend.
		wp_register_script( 'cariera-core-main', CARIERA_URL . '/assets/dist/js/frontend.js', [ 'jquery' ], CARIERA_CORE_VERSION, true );

		$args = [
			'ajax_url'      => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
			'nonce'         => wp_create_nonce( '_cariera_core_nonce' ),
			'is_rtl'        => is_rtl() ? 1 : 0,
			'home_url'      => esc_url( home_url( '/' ) ),
			'upload_ajax'   => admin_url( 'admin-ajax.php?action=handle_uploaded_media' ),
			'delete_ajax'   => admin_url( 'admin-ajax.php?action=handle_deleted_media' ),
			'max_file_size' => apply_filters( 'cariera_file_max_size', size_format( wp_max_upload_size() ) ),
			'map_provider'  => cariera_get_option( 'cariera_map_provider' ),
			'strings'       => [
				'delete_account_text' => esc_html__( 'Are you sure you want to delete your account?', 'cariera' ),
			],
		];

		wp_localize_script( 'cariera-core-main', 'cariera_core_settings', $args );

		// WPJM Ajax Filters.
		if ( class_exists( 'WP_Job_Manager' ) && defined( 'JOB_MANAGER_VERSION' ) ) {
			wp_dequeue_script( 'wp-job-manager-ajax-filters' );
			wp_deregister_script( 'wp-job-manager-ajax-filters' );
			wp_register_script( 'wp-job-manager-ajax-filters', CARIERA_URL . '/assets/dist/js/jobs-ajax-filters.js', [ 'jquery', 'jquery-deserialize' ], CARIERA_CORE_VERSION, true );
			wp_localize_script(
				'wp-job-manager-ajax-filters',
				'job_manager_ajax_filters',
				[
					'ajax_url'                => \WP_Job_Manager_Ajax::get_endpoint(),
					'is_rtl'                  => is_rtl() ? 1 : 0,
					'i18n_load_prev_listings' => esc_html__( 'Load previous listings', 'cariera' ),
					'currency'                => \cariera_currency_symbol(),
				]
			);
		}

		// Resume AJAX Filters.
		if ( class_exists( 'WP_Job_Manager' ) && class_exists( 'WP_Resume_Manager' ) ) {
			wp_dequeue_script( 'wp-resume-manager-ajax-filters' );
			wp_deregister_script( 'wp-resume-manager-ajax-filters' );
			wp_register_script( 'wp-resume-manager-ajax-filters', CARIERA_URL . '/assets/dist/js/resumes-ajax-filters.js', [ 'jquery', 'jquery-deserialize' ], CARIERA_CORE_VERSION, true );
			wp_localize_script(
				'wp-resume-manager-ajax-filters',
				'resume_manager_ajax_filters',
				[
					'ajax_url'    => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
					'currency'    => \cariera_currency_symbol(),
					'showing_all' => esc_html__( 'Showing all resumes', 'cariera' ),
				]
			);
		}

		// Maps.
		wp_register_script( 'cariera-maps', CARIERA_URL . '/assets/dist/js/maps.js', [ 'jquery' ], CARIERA_CORE_VERSION, true );
		wp_localize_script(
			'cariera-maps',
			'cariera_maps',
			[
				'map_provider'        => cariera_get_option( 'cariera_map_provider' ),
				'autolocation'        => 1 === absint( cariera_get_option( 'cariera_job_location_autocomplete' ) ) ? true : false,
				'country'             => cariera_get_option( 'cariera_map_restriction' ),
				'map_autofit'         => cariera_get_option( 'cariera_map_autofit' ),
				'centerPoint'         => cariera_get_option( 'cariera_map_center' ),
				'mapbox_access_token' => cariera_get_option( 'cariera_mapbox_access_token' ),
				'map_type'            => cariera_get_option( 'cariera_maps_type' ),
			]
		);

		// Backend.
		wp_register_style( 'cariera-core-admin', CARIERA_URL . '/assets/dist/css/admin' . $suffix . '.css', [], CARIERA_CORE_VERSION );
		wp_register_script( 'cariera-core-admin', CARIERA_URL . '/assets/dist/js/admin.js', [], CARIERA_CORE_VERSION, true );
		wp_localize_script(
			'cariera-core-admin',
			'cariera_core_admin',
			[
				'ajax_url'     => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
				'map_provider' => cariera_get_option( 'cariera_map_provider' ),
				'strings'      => [
					'delete_messages_notice'      => esc_html__( 'Messages Deleted!', 'cariera' ),
					'delete_notifications_notice' => esc_html__( 'Notifications Deleted!', 'cariera' ),
					'delete_demo_notice'          => esc_html__( 'All demo data have been deleted!', 'cariera' ),
				],
			]
		);

		// reCaptcha.
		wp_register_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js', [], false, true );

		// Blog Elementor Element.
		wp_register_style( 'cariera-blog-element', CARIERA_URL . '/assets/dist/css/blog-element' . $suffix . '.css', [], CARIERA_CORE_VERSION );

		// Pricing Tables.
		wp_register_style( 'cariera-pricing-tables', CARIERA_URL . '/assets/dist/css/pricing-tables' . $suffix . '.css', [], CARIERA_CORE_VERSION );

		// Testimonials.
		wp_register_style( 'cariera-testimonials', CARIERA_URL . '/assets/dist/css/testimonials' . $suffix . '.css', [], CARIERA_CORE_VERSION );

		// Listing Categories.
		wp_register_style( 'cariera-companies-list', CARIERA_URL . '/assets/dist/css/companies-list' . $suffix . '.css', [], CARIERA_CORE_VERSION );

		// Listing Categories.
		wp_register_style( 'cariera-listing-categories', CARIERA_URL . '/assets/dist/css/listing-categories' . $suffix . '.css', [], CARIERA_CORE_VERSION );
	}

	/**
	 * Enqueue Core Plugin assets.
	 *
	 * @since   1.6.3
	 * @version 1.6.6
	 */
	public function enqueue_assets() {
		// Main JS File of the core plugin.
		wp_enqueue_script( 'cariera-core-main' );

		// Map Providers.
		$map_provider  = cariera_get_option( 'cariera_map_provider' );
		$gmap_api_key  = cariera_get_option( 'cariera_gmap_api_key' );
		$gmap_language = cariera_get_option( 'cariera_gmap_language' );

		if ( 'google' === $map_provider && $gmap_api_key ) {
			wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $gmap_api_key . '&amp;libraries=places&language=' . $gmap_language . '&callback=Function.prototype', [ 'jquery' ], false, true );
		}

		// Maps.
		wp_enqueue_script( 'cariera-maps' );
	}

	/**
	 * Backend - Enqueue Core Plugin assets.
	 *
	 * @since   1.6.3
	 * @version 1.6.6
	 */
	public function enqueue_admin_assets() {
		// Main JS File of the core plugin.
		wp_enqueue_style( 'cariera-core-admin' );
		wp_enqueue_script( 'cariera-core-admin' );

		// Map Providers.
		$map_provider  = cariera_get_option( 'cariera_map_provider' );
		$gmap_api_key  = cariera_get_option( 'cariera_gmap_api_key' );
		$gmap_language = cariera_get_option( 'cariera_gmap_language' );

		if ( 'google' === $map_provider && $gmap_api_key ) {
			wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $gmap_api_key . '&amp;libraries=places&language=' . $gmap_language . '&callback=Function.prototype', [ 'jquery' ], false, true );
		}
	}
}
