<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Company_Manager {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Cariera Company Manager CPT
	 *
	 * @var \Cariera_Core\Core\Company_Manager\CPT()
	 */
	public $post_types;

	/**
	 * Cariera Company Manager Forms Handling
	 *
	 * @var \Cariera_Core\Core\Company_Manager\Forms()
	 */
	public $forms;

	/**
	 * Cariera Company Manager Settings
	 *
	 * @var \Cariera_Core\Core\Company_Manager\Settings()
	 */
	public $settings;

	/**
	 * Cariera Company Manager WPJM Integration
	 *
	 * @var \Cariera_Core\Core\Company_Manager\WPJM()
	 */
	public $wpjm;

	/**
	 * Constructor
	 */
	public function __construct() {
		include_once 'company-manager-functions.php';
		include_once 'company-manager-templates.php';

		// Init classes.
		\Cariera_Core\Core\Company_Manager\Shortcodes::instance();
		new \Cariera_Core\Core\Company_Manager\Writepanels();
		new \Cariera_Core\Core\Company_Manager\Geocode();
		new \Cariera_Core\Core\Company_Manager\Email_Notifications();
		\Cariera_Core\Core\Company_Manager\Lifecycle::instance();
		new \Cariera_Core\Core\Company_Manager\Bookmarks();

		$this->post_types = new \Cariera_Core\Core\Company_Manager\CPT();
		$this->forms      = new \Cariera_Core\Core\Company_Manager\Forms();
		$this->settings   = new \Cariera_Core\Core\Company_Manager\Settings();
		$this->wpjm       = new \Cariera_Core\Core\Company_Manager\WPJM();

		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );
		add_action( 'rest_api_init', [ $this, 'rest_init' ] );
	}

	/**
	 * Queries companies with certain criteria and returns them.
	 *
	 * @since   1.3.0
	 * @version 1.6.3
	 */
	public function frontend_scripts() {
		$ajax_filter_deps = [ 'jquery', 'jquery-deserialize' ];

		// Ajax Filters.
		wp_register_script( 'company-ajax-filters', CARIERA_URL . '/assets/dist/js/company-ajax-filters.js', $ajax_filter_deps, CARIERA_CORE_VERSION, true );
		wp_localize_script(
			'company-ajax-filters',
			'cariera_company_ajax_filters',
			[
				'ajax_url' => admin_url( 'admin-ajax.php', 'relative' ),
				'is_rtl'   => is_rtl() ? 1 : 0,
				'lang'     => apply_filters( 'wpjm_lang', null ),
			]
		);

		// Company Submission.
		wp_register_script( 'cariera-company-manager-submission', CARIERA_URL . '/assets/dist/js/company-submission.js', [ 'jquery', 'jquery-ui-sortable' ], CARIERA_CORE_VERSION, true );

		// Company Dashboard.
		wp_register_script( 'cariera-company-manager-dashboard', CARIERA_URL . '/assets/dist/js/company-dashboard.js', [ 'jquery' ], CARIERA_CORE_VERSION, true );
		wp_localize_script(
			'cariera-company-manager-dashboard',
			'cariera_company_dashboard',
			[
				'i18n_confirm_delete' => esc_html__( 'Are you sure you want to delete this company listing?', 'cariera' ),
			]
		);
	}

	/**
	 * Loads the REST API functionality.
	 *
	 * @since 1.5.6
	 */
	public function rest_init() {
		\Cariera_Core\Core\Company_Manager\REST_API::init();
	}
}
