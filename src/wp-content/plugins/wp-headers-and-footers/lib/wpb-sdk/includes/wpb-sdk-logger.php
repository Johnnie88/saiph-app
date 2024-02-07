<?php
/**
 * Logger responsible for gathering user logs and send.
 * In effort to provide better customer experience.
 *
 * @package WPB_SDK
 * @since 1.3.1
 */

namespace WPHeaderAndFooter_SDK;

/**
 * Class responsible for logs data collection.
 */
class Logger {

	/**
	 * Contains products data for logging.
	 *
	 * @var array
	 */
	private static $product_data;

	/**
	 * Contains products that have a Pro version.
	 *
	 * @var array
	 */
	private static $has_pro_version = array( 'wp-analytify', 'loginpress', 'simple-social-buttons' );

	/**
	 * Class constructor.
	 *
	 * @param array $product_data variable.
	 * @return void
	 */
	public function __construct( $product_data ) {

		self::$product_data = $product_data;
		$this->hooks();
	}

	/**
	 * Call wp hooks to initialize logger actions.
	 *
	 * @return void
	 */
	public function hooks() {

		add_action( 'init', array( $this, 'set_logs_schedule' ) );
		add_action( 'wpb_logger_cron_' . self::$product_data['slug'], array( $this, 'log_plugin' ) );
		add_action( 'admin_footer', array( $this, 'deactivation_model' ) );
		add_action( 'wp_ajax_wpb_sdk_' . self::$product_data['slug'] . '_deactivation', array( $this, 'ajax_deactivation' ) );

		register_activation_hook( self::$product_data['path'], array( __CLASS__, 'log_activation' ) );
		register_deactivation_hook( self::$product_data['path'], array( __CLASS__, 'product_deactivation' ) );
		register_uninstall_hook( self::$product_data['path'], array( __CLASS__, 'log_uninstallation' ) );
	}

	/**
	 * Set logs schedule.
	 */
	public function set_logs_schedule() {

		if ( ! wp_next_scheduled( 'wpb_logger_cron_' . self::$product_data['slug'] ) ) {
			wp_schedule_event( time(), 'weekly', 'wpb_logger_cron_' . self::$product_data['slug'] );
		}
	}

	/**
	 * Add deactivation model.
	 *
	 * @return void
	 */
	public function deactivation_model() {

		if ( function_exists( 'get_current_screen' ) ) {

			$screen = get_current_screen();

			if ( 'plugins.php' === $screen->parent_file ) {

				$product_slug = self::$product_data['slug'];
				$product_name = self::$product_data['name'];

				$has_pro_version = ( in_array( $product_slug, self::$has_pro_version ) ) ? true : false;

				include dirname( __DIR__ ) . '/views/wpb-sdk-deactivate-form.php';
			}
		}
	}

	/**
	 * Ajax callback when product deactivated.
	 * Shows deactivation model.
	 *
	 * @return void
	 */
	public function ajax_deactivation() {

		if ( isset( $_POST['nonce'] ) && empty( $_POST['nonce'] ) ) {
			return;
		}

		$nonce        = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );
		$verify_nonce = wp_verify_nonce( $nonce, 'deactivate-plugin_' . plugin_basename( self::$product_data['path'] ) );

		if ( ! $verify_nonce ) {
			return;
		}

		$this->log_deactivation();

		wp_die();
	}

	/**
	 * Send plugin logs on cron.
	 *
	 * @return $wpb_sdk_track SDK track record.
	 */
	public function log_plugin() {

		/**
		 * Adding a activation check to prevent 2 hits from getting send on activation
		 */
		if ( get_transient( 'wpb_logger_cron_' . self::$product_data['slug'] ) ) {
			return;
		}

		$logs_data = array_merge(
			self::get_logs_data(),
			array(
				'explicit_logs' => array(
					'action' => 'weekly',
				),
			)
		);

		$wpb_sdk_track = new Track( $logs_data );
		$wpb_sdk_track->send();
	}

	/**
	 * Send logs when plugin is activated.
	 */
	public static function log_activation() {

		/**
		 * Setting a transient to add a check in the weekly track report
		 */
		set_transient( 'wpb_logger_cron_' . self::$product_data['slug'], true, 60 );

		$logs_data = array_merge(
			self::get_logs_data(),
			array(
				'explicit_logs' => array(
					'action' => 'activate',
				),
			)
		);

		$wpb_sdk_track = new Track( $logs_data );
		$wpb_sdk_track->send();
	}

	/**
	 * Send logs when plugin is deactivated.
	 */
	public function log_deactivation() {
		$reason        = isset( $_POST['reason'] ) ? $_POST['reason'] : '';
		$reason_detail = isset( $_POST['reason_detail'] ) ? $_POST['reason_detail'] : '';

		$logs_data = array_merge(
			self::get_logs_data(),
			array(
				'explicit_logs' => array(
					'action'        => 'deactivate',
					'reason'        => sanitize_text_field( wp_unslash( $reason ) ),
					'reason_detail' => sanitize_text_field( wp_unslash( $reason_detail ) ),
				),
			)
		);

		$wpb_sdk_track = new Track( $logs_data );
		$wpb_sdk_track->send();
	}

	/**
	 * Remove cron schedules on deactivation.
	 */
	public static function product_deactivation() {

		wp_clear_scheduled_hook( 'wpb_logger_cron_' . self::$product_data['slug'] );
	}

	/**
	 * Send logs when plugin is uninstalled.
	 */
	public static function log_uninstallation() {

		$logs_data = array_merge(
			self::get_logs_data(),
			array(
				'explicit_logs' => array(
					'action' => 'uninstall',
				),
			)
		);

		$wpb_sdk_track = new Track( $logs_data );
		$wpb_sdk_track->send();
	}

	/**
	 * Collect all data for logging.
	 *
	 * @return array
	 */
	private static function get_logs_data() {

		global $wpdb;

		$data                  = array();
		$theme_data            = wp_get_theme();
		$curl_version          = '';
		$external_http_blocked = '';
		$users_count           = '';

		$admin_users = get_users( array( 'role' => 'Administrator' ) );
		$admin       = isset( $admin_users[0] ) ? $admin_users[0]->data : '';
		$admin_meta  = ! empty( $admin ) ? get_user_meta( $admin->ID ) : '';

		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( function_exists( 'count_users' ) ) {
			$users_count = count_users();
			$users_count = isset( $users_count['total_users'] ) ? intval( $users_count['total_users'] ) : '';
		}

		// Check external http request blocked.
		if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || ! WP_HTTP_BLOCK_EXTERNAL ) {
			$external_http_blocked = 'none';
		} else {
			$external_http_blocked = defined( 'WP_ACCESSIBLE_HOSTS' ) ? 'partially (accessible hosts: ' . esc_html( WP_ACCESSIBLE_HOSTS ) . ')' : 'all';
		}

		// Curl version.
		if ( function_exists( 'curl_init' ) ) {
			$curl         = curl_version();
			$curl_version = '(' . $curl['version'] . ' ' . $curl['ssl_version'] . ')';
		}

		$data['site_info']         = array(
			'site_url' => site_url(),
			'home_url' => home_url(),
		);
		$data['site_meta_info']    = array(
			'is_multisite'          => is_multisite(),
			'multisites'            => self::get_multisites(),
			'php_version'           => phpversion(),
			'wp_version'            => get_bloginfo( 'version' ),
			'server'                => isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '',
			'timezoneoffset'        => date('P'),
			'ext/mysqli'            => isset( $wpdb->use_mysqli ) && ! empty( $wpdb->use_mysqli ) ? true : false,
			'mysql_version'         => function_exists('mysqli_get_server_info') ? mysqli_get_server_info($wpdb->dbh) : mysql_get_server_info(),
			'memory_limit'          => ( defined( WP_MEMORY_LIMIT ) ? WP_MEMORY_LIMIT : ini_get( 'memory_limit' ) ) ? ini_get( 'memory_limit' ) : '',
			'external_http_blocked' => $external_http_blocked,
			'wp_locale'             => get_locale(),
			'db_charset'            => defined( 'DB_CHARSET' ) ? DB_CHARSET : '',
			'debug_mode'            => defined( 'WP_DEBUG' ) && WP_DEBUG ? true : false,
			'wp_max_upload'         => size_format( wp_max_upload_size() ),
			'php_time_limit'        => function_exists( 'ini_get' ) ? ini_get( 'max_execution_time' ) : '',
			'php_error_log'         => function_exists( 'ini_get' ) ? ini_get( 'error_log' ) : '',
			'fsockopen'             => function_exists( 'fsockopen' ) ? true : false,
			'open_ssl'              => defined( 'OPENSSL_VERSION_TEXT' ) ? OPENSSL_VERSION_TEXT : '',
			'curl'                  => $curl_version,
			'ip'                    => self::get_ip(),
			'user_count'            => $users_count,
			'admin_email'           => sanitize_email( get_bloginfo( 'admin_email' ) ),
			'theme_name'            => sanitize_text_field( $theme_data->Name ),
			'theme_version'         => sanitize_text_field( $theme_data->Version ),
		);
		$data['site_plugins_info'] = self::get_plugins();
		$data['user_info']         = array(
			'user_email'     => ! empty( $admin ) ? sanitize_email( $admin->user_email ) : '',
			'user_nickname'  => ! empty( $admin ) ? sanitize_text_field( $admin->user_nicename ) : '',
			'user_firstname' => isset( $admin_meta['first_name'][0] ) ? sanitize_text_field( $admin_meta['first_name'][0] ) : '',
			'user_lastname'  => isset( $admin_meta['last_name'][0] ) ? sanitize_text_field( $admin_meta['last_name'][0] ) : ''
		);
		$data['product_info']      = self::get_product_data();
		$data['sdk_version']       = '1.0.0';

		return $data;
	}

	/**
	 * Collect multisite data.
	 *
	 * @return array
	 */
	private static function get_multisites() {

		if ( ! is_multisite() ) {
			return false;
		}

		$sites_info = array();
		$sites      = get_sites();

		foreach ( $sites as $site ) {
			$sites_info[ $site->blog_id ] = array(
				'name'   => get_blog_details( $site->blog_id )->blogname,
				'domain' => $site->domain,
				'path'   => $site->path,
			);
		}

		return $sites_info;
	}

	/**
	 * Collect plugins information: Active/Inactive plugins.
	 *
	 * @return string
	 */
	private static function get_plugins() {

		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins ) ) {
				// Remove active plugins from list.
				unset( $plugins[ $key ] );
			}
		}

		return wp_json_encode(
			array(
				'active'   => $active_plugins,
				'inactive' => $plugins,
			)
		);
	}

	/**
	 * Get user IP information.
	 *
	 * @return string|null
	 */
	private static function get_ip() {

		$fields = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $fields as $ip_field ) {
			if ( ! empty( $_SERVER[ $ip_field ] ) ) {
				return $_SERVER[ $ip_field ];
			}
		}

		return null;
	}

	/**
	 * Get product data.
	 *
	 * @return array
	 */
	private static function get_product_data() {

		$product_data     = self::$product_data;
		$product_settings = array();

		// Pull settings data from db.
		foreach ( $product_data['settings'] as $option_name => $default_value ) {
			$get_option                       = get_option( $option_name );
			$product_settings[ $option_name ] = ! empty( $get_option ) ? $get_option : $default_value;
		}

		$product_data['settings'] = wp_json_encode( $product_settings );

		return $product_data;
	}
}
