<?php

namespace Cariera_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Core {

	/**
	 * App Rest Class for mobile handling.
	 *
	 * @var \Cariera_Core\Core\App
	 */
	protected $app;

	/**
	 * Ajax functions of the core plugin.
	 *
	 * @var \Cariera_Core\Core\Ajax
	 */
	protected $ajax;

	/**
	 * Core plugin assets.
	 *
	 * @var \Cariera_Core\Core\Assets
	 */
	protected $assets;

	/**
	 * Cariera Notifications
	 *
	 * @var \Cariera_Core\Core\Notifications
	 */
	protected $notifications;

	/**
	 * Cariera Admin Settings
	 *
	 * @var \Cariera_Core\Core\Admin
	 */
	protected $admin;

	/**
	 * Cariera Email Handling
	 *
	 * @var \Cariera_Core\Core\Emails
	 */
	protected $emails;

	/**
	 * Cariera Users
	 *
	 * @var \Cariera_Core\Core\Users
	 */
	protected $users;

	/**
	 * Cariera Metaboxes
	 *
	 * @var \Cariera_Core\Core\Metabox
	 */
	protected $metabox;

	/**
	 * Cariera Messages
	 *
	 * @var \Cariera_Core\Core\Messages
	 */
	protected $messages;

	/**
	 * Constructor function.
	 *
	 * @since   1.4.3
	 * @version 1.7.2
	 */
	public function __construct() {
		// Init Classes.
		$this->app           = \Cariera_Core\Core\App::instance();
		$this->ajax          = \Cariera_Core\Core\Ajax::instance();
		$this->assets        = \Cariera_Core\Core\Assets::instance();
		$this->notifications = \Cariera_Core\Core\Notifications::instance();
		$this->admin         = \Cariera_Core\Core\Admin::instance();
		$this->emails        = \Cariera_Core\Core\Emails::instance();
		$this->users         = \Cariera_Core\Core\Users::instance();
		$this->metabox       = \Cariera_Core\Core\Metabox::instance();
		$this->messages      = \Cariera_Core\Core\Messages::instance();

		// Actions.
		add_action( 'widgets_init', [ $this, 'widgets_init' ] );

		// Admin Notice.
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );

		// Custom Cron Schedules.
		add_filter( 'cron_schedules', [ $this, 'cron_schedules' ] );

		// Remove WPJM Notices.
		add_action( 'admin_init', [ $this, 'remove_wpjm_notices' ] );
	}

	/**
	 * Registering Widgets
	 *
	 * @since   1.2.2
	 * @version 1.7.2
	 */
	public function widgets_init() {
		include_once CARIERA_CORE_PATH . '/inc/widgets/social-media-widget.php';
		include_once CARIERA_CORE_PATH . '/inc/widgets/recent-posts-widget.php';
		include_once CARIERA_CORE_PATH . '/inc/widgets/job-search-widget.php';
		include_once CARIERA_CORE_PATH . '/inc/widgets/resume-search-widget.php';
		include_once CARIERA_CORE_PATH . '/inc/widgets/company-search-widget.php';
	}

	/**
	 * Admin Notices
	 *
	 * @since   1.4.8
	 * @version 1.7.2
	 */
	public function admin_notices() {
		$wpjm_gmaps_api_key = get_option( 'job_manager_google_maps_api_key' );

		if ( ! empty( $wpjm_gmaps_api_key ) || 'none' === cariera_get_option( 'cariera_map_provider' ) || apply_filters( 'cariera_wpjm_gmaps_hide_notice', false ) ) {
			return;
		}

		cariera_get_template_part( 'backend/google-api-notice' );
	}

	/**
	 * Add schedule to use for cron job. Should not be called externally.
	 *
	 * @since 1.5.0
	 */
	public function cron_schedules( $schedules ) {
		if ( ! isset( $schedules['5min'] ) ) {
			$schedules['5min'] = [
				'interval' => 5 * 60,
				'display'  => esc_html__( 'Once every 5 minutes', 'cariera' ),
			];
		}

		if ( ! isset( $schedules['30min'] ) ) {
			$schedules['30min'] = [
				'interval' => 30 * 60,
				'display'  => esc_html__( 'Once every 30 minutes', 'cariera' ),
			];
		}

		if ( ! isset( $schedules['monthly'] ) ) {
			$schedules['monthly'] = [
				'interval' => 2635200,
				'display'  => esc_html__( 'Once monthly', 'cariera' ),
			];
		}

		return $schedules;
	}

	/**
	 * Remove WPJM Admin notices
	 *
	 * @since   1.3.8
	 * @version 1.7.2
	 */
	public function remove_wpjm_notices() {
		if ( ! class_exists( 'WP_Job_Manager_Helper' ) || ! class_exists( 'WP_Job_Manager_Admin_Notices' ) ) {
			return;
		}

		remove_action( 'admin_notices', [ \WP_Job_Manager_Helper::instance(), 'licence_error_notices' ] );
		remove_action( 'admin_notices', [ 'WP_Job_Manager_Admin_Notices', 'display_notices' ] );
	}
}
