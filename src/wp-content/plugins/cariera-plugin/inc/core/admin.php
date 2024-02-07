<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Available settings for plugin.
	 */
	protected $settings = [];

	/**
	 * Settings group.
	 */
	protected $settings_group;

	/**
	 * Construct
	 *
	 * @since  1.4.8
	 */
	public function __construct() {

		$this->settings_group = 'cariera';

		// Register plugin settings.
		add_action( 'admin_init', [ $this, 'register_settings' ] );

		// Add settings page to menu.
		add_action( 'admin_menu', [ $this, 'add_menu_item' ], 11 );
	}

	/**
	 * Get Cariera Settings
	 *
	 * @since 1.4.8
	 */
	public function get_settings() {
		if ( 0 === count( $this->settings ) ) {
			$this->init_settings();
		}
		return $this->settings;
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @since 1.4.8
	 */
	public function add_menu_item() {
		add_submenu_page( 'cariera_theme', esc_html__( 'Settings', 'cariera' ), esc_html__( 'Settings', 'cariera' ), 'manage_options', 'cariera_settings', [ $this, 'output' ] );

		add_submenu_page( 'cariera_theme', esc_html__( 'Documentation', 'cariera' ), esc_html__( 'Documentation', 'cariera' ), 'manage_options', 'cariera_documentation', function(){} );
	}

	/**
	 * Initializes the configuration for the plugin's setting fields.
	 *
	 * @since   1.4.8
	 * @version 1.6.0
	 */
	protected function init_settings() {

		$prefix = 'cariera_';

		$this->settings = apply_filters(
			'cariera_settings',
			[
				/********** GENERAL OPTIONS **********/
				'general'       => [
					esc_html__( 'General', 'cariera' ),
					[
						[
							'id'            => $prefix . 'header_emp_cta_link',
							'label'         => esc_html__( 'Main Header CTA Link', 'cariera' ),
							'description'   => esc_html__( 'This link will be added to the Header CTA for non loggedin, employers and admins.', 'cariera' ),
							'type'          => 'select',
							'options'       => cariera_get_pages_options(),
							'default'       => '',
							'class_wrapper' => '',
							'attributes'    => [],

						],
						[
							'id'            => $prefix . 'header_candidate_cta_link',
							'label'         => esc_html__( 'Candidate Header CTA Link', 'cariera' ),
							'description'   => esc_html__( 'This link will be added to the Header CTA for loggedin Candidate users.', 'cariera' ),
							'type'          => 'select',
							'options'       => cariera_get_pages_options(),
							'default'       => '',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'job_promotions',
							'label'         => esc_html__( 'Job Promotions', 'cariera' ),
							'description'   => esc_html__( 'Job Promotions will be disabled if this option is turned off.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'company_promotions',
							'label'         => esc_html__( 'Company Promotions', 'cariera' ),
							'description'   => esc_html__( 'Company Promotions will be disabled if this option is turned off.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'resume_promotions',
							'label'         => esc_html__( 'Resume Promotions', 'cariera' ),
							'description'   => esc_html__( 'Resume Promotions will be disabled if this option is turned off.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'font_iconsmind',
							'label'         => esc_html__( 'Additional Font Icons', 'cariera' ),
							'description'   => esc_html__( 'You can disable Iconsmind font icon library by turning the switch off if you do not use any icons from this library to improve performance.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'mobile_app',
							'label'         => esc_html__( 'Mobile App Integration', 'cariera' ),
							'description'   => sprintf( __( 'If you are using the <a href="%s" target="_blank">Cariera Flutter Mobile App</a> enable this setting to allow the theme to send various data to the app via REST API.', 'cariera' ), 'https://1.envato.market/cariera-flutter' ),
							'type'          => 'switch',
							'default'       => 0,
							'class_wrapper' => '',
							'attributes'    => [],
						],

					],
				],

				/********** PRIVATE MESSAGES OPTIONS **********/
				'private_messages' => [
					esc_html__( 'Private Messages', 'cariera' ),
					[
						[
							'id'            => $prefix . 'private_messages',
							'label'         => esc_html__( 'Private Messaging System', 'cariera' ),
							'description'   => esc_html__( 'You can disable the private messaging system by disabling the option.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'clear_messages_db',
							'label'         => esc_html__( 'Clear Database', 'cariera' ),
							'title'         => esc_html__( 'Delete All Messages & Conversations', 'cariera' ),
							'description'   => '',
							'type'          => 'button',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
								'data-action'   => 'delete-private-messages',
							],
						],
						[
							'id'            => $prefix . 'private_messages_job_listings',
							'label'         => esc_html__( 'Job Listings - Private Messages', 'cariera' ),
							'description'   => esc_html__( 'You can disable the private messages on job listings.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],
						[
							'id'            => $prefix . 'private_messages_companies',
							'label'         => esc_html__( 'Companies - Private Messages', 'cariera' ),
							'description'   => esc_html__( 'You can disable the private messages on companies.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],
						[
							'id'            => $prefix . 'private_messages_resumes',
							'label'         => esc_html__( 'Resumes - Private Messages', 'cariera' ),
							'description'   => esc_html__( 'You can disable the private messages on resumes.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],
						[
							'id'            => $prefix . 'private_messages_autoload_interval',
							'label'         => esc_html__( 'Autoload Interval', 'cariera' ),
							'description'   => esc_html__( 'Number of milliseconds to autoload messages and conversations. If you have a slower server you might want to increase the value. If left blank the default value will be 10000 (e.g 10000 is 10 sec).', 'cariera' ),
							'type'          => 'text',
							'default'       => '10000',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],
						[
							'id'            => $prefix . 'private_messages_compose',
							'label'         => esc_html__( 'Compose Messages', 'cariera' ),
							'description'   => esc_html__( 'You can disable compose message on the messages popup.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],

					],
				],

				/********** NOTIFICATIONS OPTIONS **********/
				'notifications' => [
					esc_html__( 'Notifications', 'cariera' ),
					[
						[
							'id'            => $prefix . 'notifications',
							'label'         => esc_html__( 'In-Site Notifications', 'cariera' ),
							'description'   => esc_html__( 'Notifications will be globally disabled if this option is turned off.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'clear_notification_db',
							'label'         => esc_html__( 'Clear Database', 'cariera' ),
							'title'         => esc_html__( 'Delete All Notifications', 'cariera' ),
							'description'   => '',
							'type'          => 'button',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-action'   => 'delete-notifications',
							],
						],
						[
							'id'            => $prefix . 'notification_listing_status_title',
							'label'         => '',
							'title'         => esc_html__( 'Listing Status', 'cariera' ),
							'description'   => esc_html__( 'When a listing\'s status changes, e.g pending to published.', 'cariera' ),
							'type'          => 'title',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_listing_status',
							'label'         => esc_html__( 'Notification', 'cariera' ),
							'description'   => esc_html__( '"Listing Status" notification will be disabled if this option is turned off.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_listing_status_webhook',
							'label'         => esc_html__( 'Webhook', 'cariera' ),
							'description'   => esc_html__( 'Enable to be able to use webhooks', 'cariera' ),
							'type'          => 'switch',
							'default'       => 0,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						// Published Listings - Webhook.
						[
							'id'            => $prefix . 'notification_webhook_url_listing_created',
							'label'         => esc_html__( 'Webhook URL - Published', 'cariera' ),
							'description'   => esc_html__( 'This Webhook URL is used for "Published Listings"', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_listing_created_trigger',
							'label'         => ' ',
							'title'         => esc_html__( 'Trigger Webhook', 'cariera' ),
							'description'   => '',
							'type'          => 'button',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-action'   => 'webhook-trigger',
								'data-webhook'  => 'cariera_notification_webhook_url_listing_created',
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						// Approved Listings - Webhook.
						[
							'id'            => $prefix . 'notification_webhook_url_listing_approved',
							'label'         => esc_html__( 'Webhook URL - Approved', 'cariera' ),
							'description'   => esc_html__( 'This Webhook URL is used for "Approved Listings"', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_listing_approved_trigger',
							'label'         => ' ',
							'title'         => esc_html__( 'Trigger Webhook', 'cariera' ),
							'description'   => '',
							'type'          => 'button',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-action'   => 'webhook-trigger',
								'data-webhook'  => 'cariera_notification_webhook_url_listing_approved',
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						// Expired Listings - Webhook.
						[
							'id'            => $prefix . 'notification_webhook_url_listing_expired',
							'label'         => esc_html__( 'Webhook URL - Expired', 'cariera' ),
							'description'   => esc_html__( 'This Webhook URL is used for "Expired Listings"', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_listing_expired_trigger',
							'label'         => ' ',
							'title'         => esc_html__( 'Trigger Webhook', 'cariera' ),
							'description'   => '',
							'type'          => 'button',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-action'   => 'webhook-trigger',
								'data-webhook'  => 'cariera_notification_webhook_url_listing_expired',
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_application_title',
							'label'         => '',
							'title'         => esc_html__( 'New Job Application', 'cariera' ),
							'description'   => '',
							'type'          => 'title',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_application',
							'label'         => esc_html__( 'Notification', 'cariera' ),
							'description'   => esc_html__( '"New Application" notification will be disabled if this option is turned off.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_application_webhook',
							'label'         => esc_html__( 'Webhook', 'cariera' ),
							'description'   => esc_html__( 'Enable to be able to use webhooks', 'cariera' ),
							'type'          => 'switch',
							'default'       => 0,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_job_application',
							'label'         => esc_html__( 'Webhook URL', 'cariera' ),
							'description'   => '',
							'type'          => 'text',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-toggleon' => 'cariera_notification_application_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_job_application_trigger',
							'label'         => ' ',
							'title'         => esc_html__( 'Trigger Webhook', 'cariera' ),
							'description'   => '',
							'type'          => 'button',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-action'   => 'webhook-trigger',
								'data-webhook'  => 'cariera_notification_webhook_url_job_application',
								'data-toggleon' => 'cariera_notification_application_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_listing_promotion_title',
							'label'         => '',
							'title'         => esc_html__( 'Listing Promotion', 'cariera' ),
							'description'   => '',
							'type'          => 'title',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_listing_promotion',
							'label'         => esc_html__( 'Notification - Promo Started', 'cariera' ),
							'description'   => esc_html__( '"Listing Promoted" notification will be disabled if this option is turned off.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_listing_promotion_ended',
							'label'         => esc_html__( 'Notification - Promo Expired', 'cariera' ),
							'description'   => esc_html__( '"Promotion Expired" notification will be disabled if this option is turned off.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_listing_promotion_webhook',
							'label'         => esc_html__( 'Webhook', 'cariera' ),
							'description'   => esc_html__( 'Enable to be able to use webhooks', 'cariera' ),
							'type'          => 'switch',
							'default'       => 0,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_promotion_expired',
							'label'         => esc_html__( 'Webhook URL - Expired', 'cariera' ),
							'description'   => esc_html__( 'This Webhook URL is used for "Expired Promotions"', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-toggleon' => 'cariera_notification_listing_promotion_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_promotion_expired_trigger',
							'label'         => ' ',
							'title'         => esc_html__( 'Trigger Webhook', 'cariera' ),
							'description'   => '',
							'type'          => 'button',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-action'   => 'webhook-trigger',
								'data-webhook'  => 'cariera_notification_webhook_url_promotion_expired',
								'data-toggleon' => 'cariera_notification_listing_promotion_webhook',
							],
						],

					],
				],

				/********** reCAPTCHA OPTIONS **********/
				'recaptcha'     => [
					esc_html__( 'reCAPTCHA', 'cariera' ),
					[
						[
							'id'            => $prefix . 'recaptcha_sitekey',
							'label'         => esc_html__( 'reCAPTCHA Site Key', 'cariera' ),
							'description'   => sprintf( __( 'Get the sitekey from <a href="%s" target="_blank">Google\'s reCAPTCHA admin dashboard</a> - use reCAPTCHA v2', 'cariera' ), 'https://www.google.com/recaptcha/admin#list' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'recaptcha_secretkey',
							'label'         => esc_html__( 'reCAPTCHA Secret Key', 'cariera' ),
							'description'   => sprintf( __( 'Get the secret key from <a href="%s" target="_blank">Google\'s reCAPTCHA admin dashboard</a> - use reCAPTCHA v2', 'cariera' ), 'https://www.google.com/recaptcha/admin#list' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'recaptcha_login',
							'label'         => esc_html__( 'Login Form', 'cariera' ),
							'description'   => esc_html__( 'Display reCAPTCHA field in the login form. You must have entered a valid site key and secret key above.', 'cariera' ),
							'type'          => 'switch',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'recaptcha_register',
							'label'         => esc_html__( 'Registration Form', 'cariera' ),
							'description'   => esc_html__( 'Display reCAPTCHA field in the registration form. You must have entered a valid site key and secret key above.', 'cariera' ),
							'type'          => 'switch',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'recaptcha_forgotpass',
							'label'         => esc_html__( 'Forgot Password Form', 'cariera' ),
							'description'   => esc_html__( 'Display reCAPTCHA field in the forgot password form. You must have entered a valid site key and secret key above.', 'cariera' ),
							'type'          => 'switch',
							'class_wrapper' => '',
							'attributes'    => [],
						],

					],
				],

				/********** REGISTRATION OPTIONS **********/
				'registration'  => [
					esc_html__( 'Login & Register', 'cariera' ),
					[
						[
							'id'            => $prefix . 'login_register_layout',
							'options'       => [
								'popup' => esc_html__( 'Popup', 'cariera' ),
								'page'  => esc_html__( 'Custom Page', 'cariera' ),
							],
							'default'       => 'popup',
							'label'         => esc_html__( 'Login & Registration Layout', 'cariera' ),
							'description'   => esc_html__( 'You can set your login & register to "popup" or to redirect to a "custom page".', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-select-field',
							],
						],
						[
							'id'            => $prefix . 'login_register_page',
							'options'       => cariera_get_pages_options(),
							'default'       => '',
							'label'         => esc_html__( 'Login & Registration Custom Page', 'cariera' ),
							'description'   => esc_html__( 'Choose page that uses "Page Template Login".', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => 'login-page',
							'attributes'    => [
								'data-toggleon'     => 'cariera_login_register_layout',
								'data-toggle-value' => 'page',
							],
						],
						[
							'id'            => $prefix . 'login_redirection',
							'options'       => [
								'dashboard'      => esc_html__( 'Dashboard', 'cariera' ),
								'home'           => esc_html__( 'Home Page', 'cariera' ),
								'custom_page'    => esc_html__( 'Custom Page', 'cariera' ),
								'no_redirection' => esc_html__( 'No Redirection', 'cariera' ),
							],
							'default'       => 'dashboard',
							'label'         => esc_html__( 'Login Redirection', 'cariera' ),
							'description'   => esc_html__( 'Select your prefered login redirection method.', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-select-field',
							],
						],
						[
							'id'            => $prefix . 'login_redirection_page',
							'options'       => cariera_get_pages_options(),
							'default'       => '',
							'label'         => esc_html__( 'Custom Redirection Page', 'cariera' ),
							'description'   => esc_html__( 'Choose the custom page to redirect users on login.', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon'     => 'cariera_login_redirection',
								'data-toggle-value' => 'custom_page',
							],
						],
						[
							'id'            => $prefix . 'login_redirection_candidate',
							'options'       => [
								'dashboard'      => esc_html__( 'Dashboard', 'cariera' ),
								'home'           => esc_html__( 'Home Page', 'cariera' ),
								'custom_page'    => esc_html__( 'Custom Page', 'cariera' ),
								'no_redirection' => esc_html__( 'No Redirection', 'cariera' ),
							],
							'default'       => 'dashboard',
							'label'         => esc_html__( 'Candidate Login Redirection', 'cariera' ),
							'description'   => esc_html__( 'Select your prefered login redirection method for candidate users.', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-select-field',
							],
						],
						[
							'id'            => $prefix . 'login_candi_redirection_page',
							'options'       => cariera_get_pages_options(),
							'default'       => '',
							'label'         => esc_html__( 'Custom Redirection Page', 'cariera' ),
							'description'   => esc_html__( 'Choose the custom page to redirect candidates on login.', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon'     => 'cariera_login_redirection_candidate',
								'data-toggle-value' => 'custom_page',
							],
						],

						/***** WELCOME USER **********/
						[
							'id'            => $prefix . 'header_registration',
							'label'         => '',
							'title'         => esc_html__( 'Registration', 'cariera' ),
							'description'   => '',
							'type'          => 'title',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'registration',
							'label'         => esc_html__( 'Registration', 'cariera' ),
							'description'   => esc_html__( 'Turn the switch "off" if you want to disable registration.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_role_candidate',
							'label'         => esc_html__( 'Candidate Role Selection', 'cariera' ),
							'description'   => esc_html__( 'Turn the switch "off" if you want to disable the "Candidate" role from the registration form.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_role_employer',
							'label'         => esc_html__( 'Employer Role Selection', 'cariera' ),
							'description'   => esc_html__( 'Turn the switch "off" if you want to disable the "Employer" role from the registration form.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'moderate_new_user',
							'options'       => [
								'auto'  => esc_html__( 'Auto Approval', 'cariera' ),
								'email' => esc_html__( 'Email Approval', 'cariera' ),
								'admin' => esc_html__( 'Admin Approval', 'cariera' ),
							],
							'default'       => 'auto',
							'label'         => esc_html__( 'Moderate New User', 'cariera' ),
							'description'   => esc_html__( 'Users are automatically approved once registered and there is no need to activate their account. You can setup so that they can activate their account by email or that admin has to approve them manually.', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [
								'class' => 'toggle-settings-select-field',
							],
						],
						[
							'id'            => $prefix . 'moderate_new_user_page',
							'options'       => cariera_get_pages_options(),
							'label'         => esc_html__( 'Approve User Page', 'cariera' ),
							'description'   => esc_html__( 'Approve pending user page. The page needs to have [cariera_approve_user] shortcode.', 'cariera' ),
							'type'          => 'select',
							'default'       => '',
							'class_wrapper' => 'cariera-registration approve-user-page',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'auto_login',
							'label'         => esc_html__( 'Auto Login after Registration', 'cariera' ),
							'description'   => esc_html__( 'If enabled the user will automatically login after registration.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [
								'data-toggleon'     => 'cariera_moderate_new_user',
								'data-toggle-value' => 'auto',
							],
						],
						[
							'id'            => $prefix . 'register_hide_username',
							'label'         => esc_html__( 'Hide Username Field', 'cariera' ),
							'description'   => esc_html__( 'If enabled the username will be generated from the email address.', 'cariera' ),
							'type'          => 'switch',
							'default'       => '',
							'class_wrapper' => 'cariera-registration',
						],
						[
							'id'            => $prefix . 'register_privacy_policy',
							'label'         => esc_html__( 'Privacy Policy', 'cariera' ),
							'description'   => esc_html__( 'Turn the switch to "off" if you want to disable privacy policy checkbox.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'register_privacy_policy_text',
							'label'         => esc_html__( 'Privacy Policy Text', 'cariera' ),
							'description'   => esc_html__( 'Make sure to add "{gdpr_link}" in the input below if you want to add a link. The {gdpr_link} will get replace with the page set on the next option.', 'cariera' ),
							'type'          => 'text',
							'default'       => esc_html__( 'By signing up, you agree to our {gdpr_link}.', 'cariera' ),
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [
								'data-toggleon' => 'cariera_register_privacy_policy',
							],
						],
						[
							'id'            => $prefix . 'register_privacy_policy_page',
							'options'       => cariera_get_pages_options(),
							'label'         => esc_html__( 'Privacy Policy Page', 'cariera' ),
							'description'   => esc_html__( 'Choose page that will contain detailed information about the Privacy Policy of your website.', 'cariera' ),
							'type'          => 'select',
							'default'       => '',
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [
								'data-toggleon' => 'cariera_register_privacy_policy',
							],
						],
						[
							'id'            => $prefix . 'account_role_change',
							'label'         => esc_html__( 'Switch User Role', 'cariera' ),
							'description'   => esc_html__( 'Allows Employers and Candidates to change their user role on "My Profile" page.', 'cariera' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration',
						],
						[
							'id'            => $prefix . 'register_password_length',
							'label'         => esc_html__( 'Minimun password length', 'cariera' ),
							'description'   => esc_html__( 'Select the minimun length of your password.', 'cariera' ),
							'type'          => 'number',
							'default'       => '4',
							'class_wrapper' => 'cariera-registration',
						],

						/***** WELCOME USER **********/
						[
							'id'            => $prefix . 'header_user_welcome',
							'label'         => '',
							'title'         => esc_html__( 'New User Welcome Email - Auto Approve', 'cariera' ),
							'description'   => esc_html__( 'Available tags are: ', 'cariera' ) . '<strong>{user_name}, {user_mail}, {site_name}, {password}</strong>',
							'type'          => 'title',
							'class_wrapper' => 'cariera-registration no-approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_welcome_email',
							'label'         => esc_html__( 'User Welcome Email', 'cariera' ),
							'description'   => esc_html__( 'Enable/Disable email notification when a user registers on the website.', 'cariera' ),
							'default'       => 1,
							'type'          => 'switch',
							'class_wrapper' => 'cariera-registration no-approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_welcome_email_admin',
							'label'         => esc_html__( 'Admin Notification - New User', 'cariera' ),
							'description'   => esc_html__( 'Enable/Disable email notification to notify the admin that a new user has registered.', 'cariera' ),
							'default'       => 1,
							'type'          => 'switch',
							'class_wrapper' => 'cariera-registration no-approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_welcome_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera' ),
							'default'       => esc_html__( 'Welcome to {site_name}', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-registration no-approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_welcome_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera' ),
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
Welcome and thank you for signing up. You can login to your account with the details below:<br>
<ul>
<li>Username: {user_name}</li>
<li>Email: {user_mail}</li>
<li>Password: {password}</li>
</ul>'
								)
							),
							'type'          => 'editor',
							'class_wrapper' => 'cariera-registration no-approval-required',
							'attributes'    => [],
						],

						/***** APPROVAL EMAIL **********/
						[
							'id'            => $prefix . 'header_new_user_approve',
							'label'         => '',
							'title'         => esc_html__( 'Approve new registered User', 'cariera' ),
							'description'   => esc_html__( 'This email will be sent to the user or the admin depening on your approval settings. Available tags are: ', 'cariera' ) . '<strong>{user_name}, {user_mail}, {site_name}, {password}, {approval_url}</strong>',
							'type'          => 'title',
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_approve_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera' ),
							'default'       => esc_html__( 'Approve new Registered user: {user_name}', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_approve_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera' ),
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									"Hi {user_name},<br>
Welcome and thank you for signing up. You can verify your account by clicking the link below:<br>
<a href='{approval_url}' target='_blank'>Verify Account</a>"
								)
							),
							'type'          => 'editor',
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],

						/***** USER APPROVED EMAIL **********/
						[
							'id'            => $prefix . 'header_new_user_approved',
							'label'         => '',
							'title'         => esc_html__( 'User Approved', 'cariera' ),
							'description'   => esc_html__( 'This email will be sent to the user once their user status changes to "Approved"', 'cariera' ),
							'type'          => 'title',
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_approved_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera' ),
							'default'       => esc_html__( 'Your Account has been Approved', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_approved_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera' ),
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									"Hi {user_name},<br>
Your account has been approved. You can login via the link below:<br>
<a href='{site_url}' target='_blank'>Login</a>"
								)
							),
							'type'          => 'editor',
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],

						/***** USER DENIED EMAIL **********/
						[
							'id'            => $prefix . 'header_new_user_denied',
							'label'         => '',
							'title'         => esc_html__( 'User Denied', 'cariera' ),
							'description'   => esc_html__( 'This email will be sent to the user once their user status changes to "Denied"', 'cariera' ),
							'type'          => 'title',
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_denied_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera' ),
							'default'       => esc_html__( 'Your Account has been Denied', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_denied_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera' ),
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
We are sorry to say but your account has been denied.'
								)
							),
							'type'          => 'editor',
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],

					],
				],

				/********** PAGES OPTIONS **********/
				'pages'         => [
					esc_html__( 'Pages', 'cariera' ),
					[
						[
							'id'            => $prefix . 'dashboard_page',
							'options'       => cariera_get_pages_options(),
							'label'         => esc_html__( 'Dashboard Page', 'cariera' ),
							'description'   => esc_html__( 'Main User Dashboard page. The page needs to have [cariera_dashboard] shortcode (optional).', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'bookmarks_page',
							'options'       => cariera_get_pages_options(),
							'label'         => esc_html__( 'Bookmarks Page', 'cariera' ),
							'description'   => esc_html__( 'The page needs to have [my_bookmarks] shortcode.', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'past_applications_page',
							'options'       => cariera_get_pages_options(),
							'label'         => esc_html__( 'Applied Jobs Page', 'cariera' ),
							'description'   => esc_html__( 'The page needs to have [past_applications] shortcode.', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'listing_reports_page',
							'options'       => cariera_get_pages_options(),
							'label'         => esc_html__( 'Listing Reports Page', 'cariera' ),
							'description'   => esc_html__( 'The page needs to have [cariera_listing_reports] shortcode.', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_packages_page',
							'options'       => cariera_get_pages_options(),
							'label'         => esc_html__( 'User Packages Page', 'cariera' ),
							'description'   => esc_html__( 'The page needs to have [cariera_user_packages] shortcode.', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'dashboard_profile_page',
							'options'       => cariera_get_pages_options(),
							'label'         => esc_html__( 'My Profile Page', 'cariera' ),
							'description'   => esc_html__( 'Profile customization page. The page needs to have [cariera_my_account] shortcode.', 'cariera' ),
							'type'          => 'select',
							'class_wrapper' => '',
							'attributes'    => [],
						],

					],
				],

				/********** EMAILS OPTIONS **********/
				'emails'        => [
					esc_html__( 'Emails', 'cariera' ),
					[
						[
							'id'            => $prefix . 'emails_name',
							'label'         => esc_html__( '"From name" in email', 'cariera' ),
							'description'   => esc_html__( 'The name from who the email is received, by default it is your site name.', 'cariera' ),
							'default'       => get_bloginfo( 'name' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'emails_from_email',
							'label'         => esc_html__( '"From" email ', 'cariera' ),
							'description'   => esc_html__( 'This will act as the "from" and "reply-to" address. This emails should match your domain address', 'cariera' ),
							'default'       => get_bloginfo( 'admin_email' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [],
						],

						/***** ACCOUNT DELETED **********/
						[
							'id'            => $prefix . 'header_delete_account',
							'label'         => '',
							'title'         => esc_html__( 'Delete Account Email', 'cariera' ),
							'description'   => esc_html__( 'Available tags are: ', 'cariera' ) . '<strong>{user_name}, {user_mail}, {first_name}, {last_name}</strong>',
							'type'          => 'title',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'delete_account_email',
							'label'         => esc_html__( 'Delete Account Notification', 'cariera' ),
							'description'   => esc_html__( 'Enable/Disable email notification when a user deletes their account.', 'cariera' ),
							'default'       => 1,
							'type'          => 'switch',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'delete_account_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera' ),
							'description'   => '',
							'default'       => esc_html__( 'Your account has been deleted!', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'delete_account_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera' ),
							'description'   => '',
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
We are sorry to see you go! If you change your mind feel free to register on our website again anytime.'
								)
							),
							'type'          => 'editor',
							'class_wrapper' => '',
							'attributes'    => [],
						],

						/***** LISTING PROMOTED **********/
						[
							'id'            => $prefix . 'header_listing_promoted',
							'label'         => '',
							'title'         => esc_html__( 'Listing Promotion', 'cariera' ),
							'description'   => esc_html__( 'Available tags that can be used in the mail content: ', 'cariera' ) . '<strong>{user_name}, {user_mail}, {listing_name}, {listing_url}</strong>',
							'type'          => 'title',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'listing_promoted_email',
							'label'         => esc_html__( 'Listing Promotion', 'cariera' ),
							'description'   => esc_html__( 'Enable/Disable email notifications to notify the author when their listing get\'s promoted.', 'cariera' ),
							'default'       => 1,
							'type'          => 'switch',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'listing_promoted_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera' ),
							'description'   => '',
							'default'       => esc_html__( 'Listing Promoted Successfully!', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'listing_promoted_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera' ),
							'description'   => '',
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
Your listing <strong>"{listing_name}"</strong> has been promoted successfully.<br>'
								)
							),
							'type'          => 'editor',
							'class_wrapper' => '',
							'attributes'    => [],
						],

						/***** LISTING PROMOTION EXPIRED **********/
						[
							'id'            => $prefix . 'header_promotion_expired_email',
							'label'         => '',
							'title'         => esc_html__( 'Listing Promotion Expired', 'cariera' ),
							'description'   => esc_html__( 'Available tags that can be used in the mail content: ', 'cariera' ) . '<strong>{user_name}, {user_mail}, {listing_name}, {listing_url}</strong>',
							'type'          => 'title',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'promotion_expired_email',
							'label'         => esc_html__( 'Promotion Expired', 'cariera' ),
							'description'   => esc_html__( 'Enable/Disable email notifications to notify the author when their listing get\'s promoted.', 'cariera' ),
							'default'       => 1,
							'type'          => 'switch',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'promotion_expired_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera' ),
							'description'   => '',
							'default'       => esc_html__( 'Promotion has Expired!', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'promotion_expired_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera' ),
							'description'   => '',
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
Your promotion for <strong>"{listing_name}"</strong> has expired.<br>'
								)
							),
							'type'          => 'editor',
							'class_wrapper' => '',
							'attributes'    => [],
						],

						/***** PRIVATE MESSAGES **********/
						[
							'id'            => $prefix . 'private_messages_email_header',
							'label'         => '',
							'title'         => esc_html__( 'Private Messages', 'cariera' ),
							'description'   => esc_html__( 'User will receive an email notification when receiving a new message. Available tags are: ', 'cariera' ) . '<strong>{user_name}, {user_mail}, {first_name}, {last_name}, {sender_name}, {sender_mail}</strong>',
							'type'          => 'title',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'private_messages_email_notification',
							'label'         => esc_html__( 'New Message Notification', 'cariera' ),
							'description'   => esc_html__( 'Enable/Disable email notifications when user receives a new message.', 'cariera' ),
							'default'       => 1,
							'type'          => 'switch',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'private_messages_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera' ),
							'default'       => esc_html__( 'You have a new message!', 'cariera' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'private_messages_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera' ),
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
You have a new message from {sender_name}.'
								)
							),
							'type'          => 'editor',
							'class_wrapper' => '',
							'attributes'    => [],
						],

					],
				],

			] // END.
		);

	}

	/**
	 * Register plugin settings with WordPress's Settings API.
	 *
	 * @since	1.4.8
	 * @version 1.6.0
	 */
	public function register_settings() {
		if ( ! cariera_core_theme_status() ) {
			return;
		}

		$this->init_settings();

		foreach ( $this->settings as $section ) {
			foreach ( $section[1] as $option ) {
				if ( isset( $option['default'] ) ) {
					add_option( $option['id'], $option['default'] );
				}
				register_setting( $this->settings_group, $option['id'] );
			}
		}
	}

	/**
	 * Load settings page content
	 *
	 * @since 1.4.8
	 */
	public function output() {
		if ( ! cariera_core_theme_status() ) { ?>
			<div class="activate-theme-alert">
				<p><?php esc_html_e( 'Please activate the theme to be able to edit the core settings.', 'cariera' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cariera_theme' ) ); ?>" class="cariera-btn"><?php echo esc_html( 'Activate Cariera' ); ?></a>
			</div>

			<?php
			return;
		}

		$version = '';
		if ( defined( 'CARIERA_VERSION' ) ) {
			$version = CARIERA_VERSION;
		}

		$this->init_settings();
		?>

		<!-- Build Settings Page -->
		<div class="wrap cariera-settings-wrap">
			<h2><?php esc_html_e( 'Cariera Core Settings', 'cariera' ); ?></h2>

			<form class="cariera-options" method="post" action="options.php">
				<?php settings_fields( $this->settings_group ); ?>

				<div class="options-nav">
					<h2 class="nav-tab-wrapper">
						<div class="logo">
							<img src="<?php echo esc_url( get_template_directory_uri()  . '/assets/images/gnodesign-logo.svg' ); ?>" class="gnodesign-logo" />
						</div>
						<?php
						foreach ( $this->settings as $key => $section ) {
							echo '<a href="#settings-' . esc_attr( sanitize_title( $key ) ) . '" class="nav-tab">' . esc_html( $section[0] ) . '</a>';
						}
						?>
					</h2>
				</div>

				<?php
				if ( ! empty( $_GET['settings-updated'] ) ) {
					flush_rewrite_rules();
					echo '<div class="updated fade cariera-updated"><p>' . esc_html__( 'Settings successfully saved', 'cariera' ) . '</p></div>';
				}
				?>

				<div class="cariera-settings">
					<div class="settings-header">
						<h4 class="headline"><?php esc_html_e( 'Cariera Theme:', 'cariera' ); ?><span class="version"><?php echo esc_html__( 'Version', 'cariera' ) . ' ' . esc_html( $version ); ?></span></h4>
						<p class="submit">
							<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'cariera' ); ?>" />
						</p>
					</div>

					<?php
					foreach ( $this->settings as $key => $section ) {
						$section_args = isset( $section[2] ) ? (array) $section[2] : [];

						echo '<div id="settings-' . esc_attr( sanitize_title( $key ) ) . '" class="settings_panel">';
						if ( ! empty( $section_args['before'] ) ) {
							echo '<p class="before-settings">' . wp_kses_post( $section_args['before'] ) . '</p>';
						}
							echo '<table class="form-table settings parent-settings">';
						foreach ( $section[1] as $option ) {
							$value = get_option( $option['id'] );
							$this->output_field( $option, $value );
						}
							echo '</table>';
						if ( ! empty( $section_args['after'] ) ) {
							echo '<p class="after-settings">' . wp_kses_post( $section_args['after'] ) . '</p>';
						}
						echo '</div>';
					}
					?>

					<p class="submit main-submit">
						<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'cariera' ); ?>" />
					</p>
				</div>

			</form>
		</div>
		
		<?php get_template_part( 'templates/backend/admin/support-link' ); ?>

		<script type="text/javascript"></script>
		<?php
	}

	/**
	 * Checkbox input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_checkbox( $option, $attributes, $value, $ignored_placeholder ) {
		?>
		<label>
		<input type="hidden" name="<?php echo esc_attr( $option['id'] ); ?>" value="0" />
		<input
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			type="checkbox"
			value="1"
			<?php
			echo implode( ' ', $attributes ) . ' ';
			checked( '1', $value );
			?>
		/> <?php echo wp_kses_post( $option['cb_label'] ); ?></label>
		<?php
		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Checkbox input switch.
	 *
	 * @since 1.4.8
	 */
	protected function input_switch( $option, $attributes, $value, $ignored_placeholder ) {
		?>
		<div class="switch-container">
			<label class="switch">
				<input type="hidden" name="<?php echo esc_attr( $option['id'] ); ?>" value="0" />
				<input id="setting-<?php echo esc_attr( $option['id'] ); ?>" name="<?php echo esc_attr( $option['id'] ); ?>" type="checkbox" value="1" <?php echo implode( ' ', $attributes ) . ' '; ?><?php checked( '1', $value ); ?>	/>
				<span class="switch-btn"><span data-on="<?php esc_html_e( 'on', 'cariera' ); ?>" data-off="<?php esc_html_e( 'off', 'cariera' ); ?>"></span></span>
			</label>
			<?php
			if ( ! empty( $option['description'] ) ) {
				echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Text area input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_textarea( $option, $attributes, $value, $placeholder ) {
		?>
		<textarea
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			class="large-text"
			cols="50"
			rows="3"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			<?php
			echo implode( ' ', $attributes ) . ' ';
			echo $placeholder;
			?>
		>
			<?php echo esc_textarea( $value ); ?>
		</textarea>
		<?php

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Select input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_select( $option, $attributes, $value, $ignored_placeholder ) {
		?>
		<select
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			<?php
			echo implode( ' ', $attributes );
			?>
		>
		<?php
		foreach ( $option['options'] as $key => $name ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $name ) . '</option>';
		}
		?>
		</select>
		<?php

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Radio input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_radio( $option, $ignored_attributes, $value, $ignored_placeholder ) {
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php echo esc_html( $option['label'] ); ?></span>
			</legend>
			<?php
			if ( ! empty( $option['description'] ) ) {
				echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
			}

			foreach ( $option['options'] as $key => $name ) {
				echo '<label><input name="' . esc_attr( $option['id'] ) . '" type="radio" value="' . esc_attr( $key ) . '" ' . checked( $value, $key, false ) . ' />' . esc_html( $name ) . '</label><br>';
			}
			?>
		</fieldset>
		<?php
	}

	/**
	 * Editor input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_editor( $option, $ignored_attributes, $value, $ignored_placeholder ) {
		wp_editor(
			$value,
			$option['id'],
			[
				'textarea_name' => $option['id'],
				'editor_height' => 200,
			]
		);
	}

	/**
	 * Editor input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_title( $option, $ignored_attributes, $value, $ignored_placeholder ) {
		echo '<div class="settings-title ' . esc_attr( $option['id'] ) . '">';
			echo '<h3>' . esc_html( $option['title'] ) . '</h3>';
			echo '<span>' . wp_kses_post( $option['description'] ) . '</span>';
		echo '</div>';
	}

	/**
	 * Editor input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.1
	 */
	protected function input_button( $option, $attributes, $value, $placeholder ) {
		?>
		<a href="#" class="cariera-btn" <?php echo implode( ' ', $attributes ) . ' '; ?>><?php echo esc_html( $option['title'] ); ?></a>
		<?php if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		} ?>
		<p class="message"></p>
		<?php
	}

	/**
	 * Page input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_page( $option, $ignored_attributes, $value, $ignored_placeholder ) {
		$args = [
			'name'             => $option['id'],
			'id'               => $option['id'],
			'sort_column'      => 'menu_order',
			'sort_order'       => 'ASC',
			'show_option_none' => esc_html__( '--no page--', 'cariera' ),
			'echo'             => false,
			'selected'         => absint( $value ),
		];

		echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'cariera' ) . "' id=", wp_dropdown_pages( $args ) );

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Hidden input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_hidden( $option, $attributes, $value, $ignored_placeholder ) {
		$human_value = $value;
		if ( $option['human_value'] ) {
			$human_value = $option['human_value'];
		}
		?>
		<input
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			type="hidden"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			<?php
			echo implode( ' ', $attributes );
			?>
		/><strong><?php echo esc_html( $human_value ); ?></strong>
		<?php

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Password input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_password( $option, $attributes, $value, $placeholder ) {
		?>
		<input
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			class="regular-text"
			type="password"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			<?php
			echo implode( ' ', $attributes ) . ' ';
			echo $placeholder;
			?>
		/>
		<?php

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Number input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_number( $option, $attributes, $value, $placeholder ) {
		echo isset( $option['before'] ) ? wp_kses_post( $option['before'] ) : '';
		?>
		<input
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			class="small-text"
			type="number"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			<?php
			echo implode( ' ', $attributes ) . ' ';
			echo $placeholder;
			?>
		/>
		<?php
		echo isset( $option['after'] ) ? wp_kses_post( $option['after'] ) : '';
		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Text input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_text( $option, $attributes, $value, $placeholder ) {
		?>
		<input
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			class="regular-text"
			type="text"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			<?php
			echo implode( ' ', $attributes ) . ' ';
			echo $placeholder;
			?>
		/>
		<?php

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Outputs the field row.
	 *
	 * @since   1.4.8
	 * @version 1.5.2
	 */
	protected function output_field( $option, $value ) {
		$placeholder    = ! empty( $option['placeholder'] ) ? 'placeholder="' . esc_attr( $option['placeholder'] ) . '"' : '';
		$class          = ! empty( $option['class_wrapper'] ) ? $option['class_wrapper'] : '';
		$option['type'] = ! empty( $option['type'] ) ? $option['type'] : 'text';
		$attributes     = [];
		if ( ! empty( $option['attributes'] ) && is_array( $option['attributes'] ) ) {
			foreach ( $option['attributes'] as $attribute_name => $attribute_value ) {
				$attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		echo '<tr valign="top" class="' . esc_attr( $class ) . '">';

		if ( ! empty( $option['label'] ) ) {
			echo '<th scope="row"><label for="setting-' . esc_attr( $option['id'] ) . '">' . esc_html( $option['label'] ) . '</a></th><td>';
		} else {
			echo '<td colspan="2">';
		}

		$method_name = 'input_' . $option['type'];
		if ( method_exists( $this, $method_name ) ) {
			$this->$method_name( $option, $attributes, $value, $placeholder );
		} else {
			// Allows for custom fields in admin setting panes.
			do_action( 'cariera_admin_field_' . $option['type'], $option, $attributes, $value, $placeholder );
		}
		echo '</td></tr>';
	}

	/**
	 * Multiple settings stored in one setting array that are shown when the `enable` setting is checked.
	 *
	 * @since 1.4.8
	 */
	protected function input_multi_enable_expand( $option, $attributes, $values, $placeholder ) {
		echo '<div class="setting-enable-expand">';
		$enable_option               = $option['enable_field'];
		$enable_option['id']         = $option['id'] . '[' . $enable_option['id'] . ']';
		$enable_option['type']       = 'checkbox';
		$enable_option['attributes'] = [ 'class="sub-settings-expander"' ];
		$this->input_checkbox( $enable_option, $enable_option['attributes'], $values[ $option['enable_field']['id'] ], null );

		echo '<div class="sub-settings-expandable">';
		$this->input_multi( $option, $attributes, $values, $placeholder );
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Multiple settings stored in one setting array.
	 *
	 * @since 1.4.8
	 */
	protected function input_multi( $option, $ignored_attributes, $values, $ignored_placeholder ) {
		echo '<table class="form-table settings child-settings">';
		foreach ( $option['settings'] as $sub_option ) {
			$value            = isset( $values[ $sub_option['id'] ] ) ? $values[ $sub_option['id'] ] : $sub_option['default'];
			$sub_option['id'] = $option['id'] . '[' . $sub_option['id'] . ']';
			$this->output_field( $sub_option, $value );
		}
		echo '</table>';
	}

	/**
	 * Proxy for text input field.
	 *
	 * @since 1.4.8
	 */
	protected function input_input( $option, $attributes, $value, $placeholder ) {
		$this->input_text( $option, $attributes, $value, $placeholder );
	}
}
