<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Demo {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		if ( ! \Cariera\is_demo_mode() ) {
			return;
		}

		add_action( 'wp_footer', [ $this, 'envato_btn_purchase' ] );
		add_action( 'admin_init', [ $this, 'block_backend_access' ] );

		// Check demo handlings.
		add_action( 'cariera_change_user_details_before', [ $this, 'check_demo_account' ] );
		add_action( 'cariera_change_user_password_before', [ $this, 'check_demo_account' ] );
		add_action( 'cariera_delete_account_before', [ $this, 'check_demo_account' ] );

		// Demo login credentials.
		add_action( 'cariera_login_form_before', [ $this, 'demo_login_accounts' ] );

		// Demo Settings.
		add_filter( 'cariera_settings', [ $this, 'demo_settings' ] );

		// Delete demo data AJAX function.
		add_action( 'wp_ajax_cariera_delete_demo_data', [ $this, 'delete_demo_data' ] );
	}

	/**
	 * Envato Purchase Button
	 *
	 * @since 1.7.0
	 */
	public function envato_btn_purchase() {
		get_template_part( 'templates/demo/purchase-btn' );
	}

	/**
	 * Block access to backend if user is not admin.
	 *
	 * @since 1.7.0
	 */
	public function block_backend_access() {
		if ( is_admin() && ! current_user_can( 'administrator' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	/**
	 * Check demo handlings
	 *
	 * @since 1.7.1
	 */
	public function check_demo_account() {
		$user_id  = get_current_user_id();
		$user_obj = get_user_by( 'ID', $user_id );

		if ( strtolower( $user_obj->data->user_login ) == 'employer' || strtolower( $user_obj->data->user_login ) == 'candidate' ) {
			$return = [
				'status' => false,
				'msg'    => esc_html__( 'You can not take this action on a demo account.', 'cariera' ),
			];
			echo wp_json_encode( $return );
			exit;
		}
	}

	/**
	 * Demo account credentials
	 *
	 * @since 1.7.1
	 */
	public function demo_login_accounts() {
		get_template_part( 'templates/demo/login-accounts' );
	}

	/**
	 * Add demo settings to Cariera Settings page
	 *
	 * @param array $settings
	 * @since 1.7.1
	 */
	public function demo_settings( $settings = [] ) {
		$settings['demo_handling'] = [
			esc_html__( 'Demo Handling', 'cariera' ),
			[
				[
					'id'            => 'cariera_delete_demo_data',
					'label'         => esc_html__( 'Delete Demo Data', 'cariera' ),
					'title'         => esc_html__( 'Delete All Demo Data', 'cariera' ),
					'description'   => esc_html__( 'This will delete all unneeded demo data. Pending listings, applications, media etc.' ),
					'type'          => 'button',
					'class_wrapper' => 'cariera-demo',
					'attributes'    => [
						'data-action' => 'delete-demo-data',
					],
				],
			],
		];

		return $settings;
	}

	/**
	 * Delete all demo data via AJAX
	 *
	 * @since   1.7.1
	 * @version 1.7.2
	 */
	public function delete_demo_data() {
		$posts            = [];
		$post_types       = [ 'job_listing', 'resume', 'company' ];
		$other_post_types = [ 'job_application', 'job_alert', 'shop_order' ];

		// Get listings.
		$posts['listings'] = get_posts(
			[
				'post_type'   => $post_types,
				'post_status' => [ 'pending', 'pending_payment', 'preview', 'expired', 'trash' ],
				'numberposts' => -1,
				'orderby'     => 'post_date ID',
				'order'       => 'ASC',
			]
		);

		// Get applications, alerts and orders.
		$posts['other'] = get_posts(
			[
				'post_type'   => $other_post_types,
				'post_status' => 'any',
				'numberposts' => -1,
				'orderby'     => 'post_date ID',
				'order'       => 'ASC',
			]
		);

		// Delete all listings.
		foreach ( $posts['listings'] as $post ) {
			\Cariera\write_log( $post->ID );
			wp_delete_post( $post->ID, true );
		}

		// Delete all other data.
		foreach ( $posts['other'] as $post ) {
			\Cariera\write_log( $post->ID );
			wp_delete_post( $post->ID, true );
		}

		// Delete Messages.
		$this->delete_all_messages();

		// Delete Notifications.
		$this->delete_notifications();

		wp_send_json( [ 'success' => true ] );
		exit;
	}

	/**
	 * Delete all messages and conversations from the DB Table via AJAX
	 *
	 * @since   1.7.2
	 */
	private function delete_all_messages() {
		global $wpdb;

		$message_table      = $wpdb->prefix . 'cariera_messages';
		$conversation_table = $wpdb->prefix . 'cariera_conversations';

		$wpdb->query( "TRUNCATE TABLE {$message_table}" );
		$wpdb->query( "TRUNCATE TABLE {$conversation_table}" );
	}

	/**
	 * Delete old notifications
	 *
	 * @since 1.7.2
	 */
	private function delete_notifications() {
		global $wpdb;

		$notifications_table = $wpdb->prefix . 'cariera_notifications';

		$wpdb->query( "TRUNCATE TABLE {$notifications_table}" );
	}
}
