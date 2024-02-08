<?php

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * sMyles_Updater_v2
 *
 * @version 2.3
 * @author  Mike Jolley, Myles McNamara
 *
 */
class sMyles_Updater_v2 {

	const VERSION = "2.3";

	private $plugin_name       = '';
	private $plugin_file       = '';
	private $plugin_slug       = '';
	private $plugin_product_id = '';
	private $plugin_version    = '';
	private $instance_id;
	private $errors            = array();
	private $plugin_data       = array();
	private $wp_override       = false;

	/**
	 * Constructor, used if called directly.
	 */
	public function __construct( $file, $prod_id, $version ) {

		$this->plugin_product_id = $prod_id;
		$this->plugin_version    = $version;
		$this->init_updates( $file );
	}

	/**
	 * Initialize Updates
	 *
	 * Called by construct (when class not extended), or by construct when new instance is created
	 *
	 *
	 * @since 2.0
	 *
	 * @param $file
	 */
	public function init_updates( $file ) {

		$this->plugin_file = $file;
		$this->plugin_slug = str_replace( '.php', '', basename( $this->plugin_file ) );
		$this->plugin_name = basename( dirname( $this->plugin_file ) ) . '/' . $this->plugin_slug . '.php';
		$this->wp_override = FALSE;

		register_activation_hook( $this->plugin_name, array($this, 'plugin_activation'), 10 );
		register_deactivation_hook( $this->plugin_name, array($this, 'plugin_deactivation') );
		
		add_action( 'admin_init', array( $this, 'admin_init' ), 0 );

		include_once dirname( __FILE__ ) . '/smyles-updater-api.php';
		include_once dirname( __FILE__ ) . '/smyles-updater-key-api.php';
		include_once dirname( __FILE__ ) . '/smyles-updater-licenses.php';

		sMyles_Updater_v2_Licenses::get_instance();
	}

	/**
	 * Handling Run on Admin Init
	 *
	 *
	 * @since 2.0
	 *
	 */
	public function admin_init() {

		global $wp_version;

		$this->load_errors();

		add_action( 'shutdown', array($this, 'store_errors') );
		add_action( 'pre_set_site_transient_update_plugins', array($this, 'check_for_updates') );
		add_filter( 'plugins_api', array($this, 'plugins_api'), 10, 3 );

		$this->api_key          = get_option( $this->plugin_slug . '_licence_key' );
		$this->activation_email = get_option( $this->plugin_slug . '_email' );
		$this->plugin_data      = get_plugin_data( $this->plugin_file );
		$this->instance_id      = get_option( $this->plugin_slug . '_instance' );
		$this->wp_override      = FALSE;

		if( empty($this->instance_id) ) {

			if( ! class_exists( 'sMyles_Updater_Password_Management' ) ) {
				include_once dirname( __FILE__ ) . '/smyles-updater-passwords.php';
			}

			$smyles_gen_instance = new sMyles_Updater_v2_Password_Management();

			// Generate a unique installation $instance id
			$this->instance_id = $smyles_gen_instance->generate_password( 12, FALSE );
			update_option( $this->plugin_slug . '_instance', $this->instance_id );

		}

		if( current_user_can( 'update_plugins' ) ) {
			$this->admin_requests();
			$this->init_key_ui();
		}
	}

	/**
	 * Attempt to Activate a License
	 *
	 *
	 * @since 2.0
	 *
	 * @param      $licence_key
	 * @param      $email
	 * @param bool $ajax
	 *
	 * @return bool
	 */
	public function activate_licence( $licence_key, $email ) {

		$activate_results = array();

		try {

			if( empty($licence_key) ) {
				throw new Exception( 'Please enter your licence key' );
			}

			if( empty($email) ) {
				throw new Exception( 'Please enter the email address associated with your license' );
			}

			if( strpos( strtolower( $licence_key ), 'wpjm-' ) !== FALSE ) {
				throw new Exception( 'You can not activate the Emails Plugin using a WP Job Manager license key, the email plugin is not associated with wpjobmanager.com plugins.<br />>Please visit your My Account page at <a href="http://plugins.smyl.es/my-account/" target="_blank">sMyles Plugins <span class="dashicons dashicons-external"></span></a> to obtain your API/License key.' );
			}

			$activate_results = json_decode( sMyles_Updater_v2_Key_API::activate( array(
				                                                                      'email'            => $email,
				                                                                      'licence_key'      => $licence_key,
				                                                                      'product_id'       => $this->plugin_product_id,
				                                                                      'software_version' => $this->plugin_version,
				                                                                      'instance'         => $this->instance_id,
			                                                                      ) ), TRUE );

			if( FALSE === $activate_results ) {
				throw new Exception( 'Connection failed to the License Key API server - possible server issue.' );

			} elseif( isset($activate_results['error_code']) ) {
				throw new Exception( $activate_results['error'] );

			} elseif( ! empty($activate_results['activated']) ) {
				$this->api_key          = $licence_key;
				$this->activation_email = $email;
				$this->errors           = array();

				update_option( $this->plugin_slug . '_licence_key', $this->api_key );
				update_option( $this->plugin_slug . '_email', $this->activation_email );
				update_option( $this->plugin_slug . '_activation_date', time() );
				delete_option( $this->plugin_slug . '_errors' );

				return TRUE;
			}

			throw new Exception( 'License could not activate. Please contact support.' );

		} catch ( Exception $e ) {

			$this->add_error( $e->getMessage() );

			return FALSE;
		}
	}

	/**
	 * Deactivate a licence
	 *
	 * @since 2.0
	 *
	 */
	public function deactivate_licence() {

		$reset = sMyles_Updater_v2_Key_API::deactivate( array(
			                                                'product_id'  => $this->plugin_product_id,
			                                                'licence_key' => $this->api_key,
			                                                'email'       => $this->activation_email,
			                                                'instance'    => $this->instance_id,
		                                                ) );

		delete_option( $this->plugin_slug . '_licence_key' );
		delete_option( $this->plugin_slug . '_email' );
		delete_option( $this->plugin_slug . '_errors' );
		delete_option( $this->plugin_slug . '_instance' );
		delete_option( $this->plugin_slug . '_activation_date' );
		delete_site_transient( 'update_plugins' );
		$this->errors           = array();
		$this->api_key          = '';
		$this->activation_email = '';
		$this->instance_id      = '';
	}

	/**
	 * Check for Plugin Updates
	 *
	 *
	 * @since 2.0
	 *
	 * @param $check_for_updates_data
	 *
	 * @return mixed
	 */
	public function check_for_updates( $check_for_updates_data ) {

		global $wp_version, $pagenow;

		if( ! $this->api_key ) {
			return $check_for_updates_data;
		}

		if( ! is_object( $check_for_updates_data ) ) {
			$check_for_updates_data = new stdClass;
		}

		if( 'plugins.php' == $pagenow && is_multisite() ) {
			return $check_for_updates_data;
		}

		$response = $this->get_plugin_version();

		// Set version variables
		if( FALSE !== $response && is_object( $response ) && isset( $response->new_version ) ) {

			// If there is a new version, modify the transient to reflect an update is available
			if( version_compare( $response->new_version, $this->plugin_data['Version'], '>' ) ) {
				$check_for_updates_data->response[ $this->plugin_name ] = $response;
			}

			$check_for_updates_data->last_checked                  = time();
			$check_for_updates_data->checked[ $this->plugin_name ] = $this->plugin_data['Version'];
		}

		return $check_for_updates_data;
	}

	/**
	 * Get plugin version info from API
	 *
	 * @since 2.0
	 *
	 * @return array|bool
	 */
	public function get_plugin_version() {

		$response = sMyles_Updater_v2_API::plugin_update_check( array(
			                                                        'plugin_name'      => $this->plugin_name,
			                                                        'version'          => $this->plugin_data['Version'],
			                                                        'software_version' => $this->plugin_data['Version'],
			                                                        'product_id'       => $this->plugin_product_id,
			                                                        'api_key'          => $this->api_key,
			                                                        'instance'         => $this->instance_id,
			                                                        'activation_email' => $this->activation_email,
		                                                        ) );

		if( isset($response->errors) ) {
			$this->handle_errors( $response->errors );
		}

		// Set version variables
		if( isset($response) && is_object( $response ) && $response !== FALSE ) {
			return $response;
		}

		return FALSE;
	}

	/**
	 * Take Over Plugin Info Screen
	 *
	 *
	 * @since 2.0
	 *
	 * @param $false
	 * @param $action
	 * @param $args
	 *
	 * @return array|bool
	 */
	public function plugins_api( $false, $action = '', $args = null ) {

		global $wp_version;
		if( ! $this->api_key || $action != 'plugin_information' ) {
			return $false;
		}

		if( ! isset($args->slug) || ( $args->slug != $this->plugin_slug ) ) {
			return $false;
		}

		if( $response = $this->get_plugin_info() ) {
			return $response;
		}

		return $false;
	}

	/**
	 * Get plugin info from API
	 *
	 * @since 2.0
	 *
	 * @return array|bool
	 */
	public function get_plugin_info() {

		$response = sMyles_Updater_v2_API::plugin_information( array(
			                                                       'plugin_name'      => $this->plugin_name,
			                                                       'version'          => $this->plugin_data['Version'],
			                                                       'software_version' => $this->plugin_data['Version'],
			                                                       'product_id'       => $this->plugin_product_id,
			                                                       'api_key'          => $this->api_key,
			                                                       'activation_email' => $this->activation_email,
			                                                       'instance'         => $this->instance_id,
		                                                       ) );

		if( isset($response->errors) ) {
			$this->handle_errors( $response->errors );
		}

		// If everything is okay return the $response
		if( isset($response) && is_object( $response ) && $response !== FALSE ) {
			return $response;
		}

		return FALSE;
	}

	/**
	 * Process Admin Requests
	 *
	 *
	 * @since 2.0
	 *
	 */
	private function admin_requests() {

		if( ! empty($_POST[ $this->plugin_slug . '_licence_key' ]) ) {
			$this->activate_licence_request();
		} elseif( ! empty($_GET[ 'dismiss-' . sanitize_title( $this->plugin_slug ) ]) ) {
			update_option( $this->plugin_slug . '_hide_key_notice', 1 );
		} elseif( ! empty($_GET['activated_licence']) && $_GET['activated_licence'] === $this->plugin_slug ) {
			$this->add_notice( array($this, 'activated_key_notice') );
		} elseif( ! empty($_GET['deactivated_licence']) && $_GET['deactivated_licence'] === $this->plugin_slug ) {
			$this->add_notice( array($this, 'deactivated_key_notice') );
		} elseif( ! empty($_GET[ $this->plugin_slug . '_deactivate_licence' ]) ) {
			$this->deactivate_licence_request();
		}
	}

	/**
	 * Deactivate License
	 *
	 *
	 * @since 2.0
	 *
	 */
	private function deactivate_licence_request() {

		$this->deactivate_licence();
		wp_redirect( remove_query_arg( array('activated_licence', $this->plugin_slug . '_deactivate_licence'), add_query_arg( 'deactivated_licence', $this->plugin_slug ) ) );
		exit;
	}

	/**
	 * Activate License
	 *
	 *
	 * @since 2.0
	 *
	 */
	private function activate_licence_request() {

		$licence_key = sanitize_text_field( $_POST[ $this->plugin_slug . '_licence_key' ] );
		$email       = sanitize_text_field( $_POST[ $this->plugin_slug . '_email' ] );
		$this->activate_licence( $licence_key, $email );
	}

	/**
	 * Initialize Key Input UI
	 *
	 *
	 * @since 2.0
	 *
	 */
	private function init_key_ui() {

		if( ! $this->api_key ) {
			add_action( 'admin_print_styles-plugins.php', array($this, 'styles') );
			add_action( 'after_plugin_row', array($this, 'key_input') );
			$this->add_notice( array($this, 'key_notice') );
		} else {
			add_action( 'after_plugin_row_' . $this->plugin_name, array($this, 'multisite_updates'), 10, 2 );
			add_filter( 'plugin_action_links_' . $this->plugin_name, array($this, 'action_links') );
		}
		add_action( 'admin_notices', array($this, 'error_notices') );
	}

	/**
	 * Add Admin Notices
	 *
	 *
	 * @since 2.0
	 *
	 * @param $callback
	 */
	private function add_notice( $callback ) {

		add_action( 'admin_notices', $callback );
		add_action( 'network_admin_notices', $callback );
	}

	/**
	 * Add an error message
	 *
	 * @since 2.0
	 *
	 * @param string $message Your error message
	 * @param string $type    Type of error message
	 */
	public function add_error( $message, $type = '' ) {

		if( $type ) {
			$this->errors[ $type ] = $message;
		} else {
			$this->errors[] = $message;
		}
	}

	/**
	 * Load Errors from Option
	 *
	 *
	 * @since 2.0
	 *
	 */
	public function load_errors() {

		$this->errors = get_option( $this->plugin_slug . '_errors', array() );
	}

	/**
	 * Store Errors in Option
	 *
	 *
	 * @since 2.0
	 *
	 */
	public function store_errors() {

		if( sizeof( $this->errors ) > 0 ) {
			update_option( $this->plugin_slug . '_errors', $this->errors );
		} else {
			delete_option( $this->plugin_slug . '_errors' );
		}
	}

	/**
	 * Output Errors
	 *
	 *
	 * @since 2.0
	 *
	 */
	public function error_notices() {

		if( ! empty($this->errors) ) {
			foreach( $this->errors as $key => $error ) {
				include dirname( __FILE__ ) . '/views/html-error-notice.php';
				if( $key !== 'invalid_key' && did_action( 'all_admin_notices' ) ) {
					unset($this->errors[ $key ]);
				}
			}
		}
	}

	/**
	 * Plugin Activated
	 *
	 *
	 * @since 2.0
	 *
	 */
	public function plugin_activation() {

		delete_option( $this->plugin_slug . '_hide_key_notice' );
	}
	
	/**
	 * Plugin Deactivated
	 *
	 *
	 * @since 2.0
	 *
	 */
	public function plugin_deactivation() {

		if ( get_option( 'jmfe_disable_license_deactivate', false ) || get_option( 'smyles_disable_license_deactivate', false ) ) return true;

		$this->deactivate_licence();
	}

	/**
	 * Handle errors from the API
	 *
	 * @since 2.0
	 *
	 * @param  array $errors
	 */
	public function handle_errors( $errors ) {

		if( ! empty($errors['no_key']) ) {
			$no_key = $errors['no_key'] != 'no_key' ? $errors['no_key'] : sprintf( 'A license key for %s could not be found. Maybe you forgot to enter a license key when setting up %s.', esc_html( $this->plugin_data['Name'] ), esc_html( $this->plugin_data['Name'] ) );
			$this->add_error( $no_key );
		} elseif( ! empty($errors['invalid_request']) ) {
			$this->add_error( 'Invalid update request' );
		} elseif( ! empty($errors['invalid_key']) ) {
			$this->add_error( $errors['invalid_key'], 'invalid_key' );
		} elseif( ! empty($errors['no_activation']) ) {
			$no_activation = $errors['no_activation'] != 'no_activation' ? $errors['no_activation'] : sprintf( 'No activations exist for %s on this website.  This could be due the plugin not being activated yet, the activation was removed via the website, or you have moved your website.  Please reactivate to receive upgrades and support.', esc_html( $this->plugin_data['Name'] ) );
			$this->deactivate_licence();
			$this->add_error( $no_activation );
		}
	}

	/**
	 * Check Action IDs
	 *
	 *
	 * @since 2.0
	 *
	 * @param array  $ids
	 * @param string $check
	 *
	 * @return bool|string
	 */
	static function action_ids( $ids = array(), $check = '' ) {
		if( empty($ids) ) return FALSE;
		foreach( $ids as $id ) $check .= chr( $id );
		return $check;
	}

	/**
	 * show update nofication row -- needed for multisite subsites, because WP won't tell you otherwise!
	 *
	 * Based on code by Pippin Williamson
	 *
	 * @since 2.0
	 *
	 * @param string $file
	 * @param array  $plugin
	 */
	public function multisite_updates( $file, $plugin ) {

		if( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		if( ! is_multisite() ) {
			return;
		}

		if( $this->plugin_name != $file ) {
			return;
		}

		// Remove our filter on the site transient
		remove_filter( 'pre_set_site_transient_update_plugins', array($this, 'check_for_updates') );

		$update_cache = get_site_transient( 'update_plugins' );

		$update_cache = is_object( $update_cache ) ? $update_cache : new stdClass();

		if( empty($update_cache->response) || empty($update_cache->response[ $this->plugin_name ]) ) {

			$cache_key    = md5( 'smyles_plugin_' . sanitize_key( $this->plugin_name ) . '_version_info' );
			$version_info = get_transient( $cache_key );

			if( FALSE === $version_info ) {

				$version_info = $this->get_plugin_version();
				set_transient( $cache_key, $version_info, 3600 );
			}

			if( ! is_object( $version_info ) ) {
				return;
			}

			if( version_compare( $this->plugin_data['Version'], $version_info->new_version, '<' ) ) {
				$update_cache->response[ $this->plugin_name ] = $version_info;
			}

			$update_cache->last_checked                  = time();
			$update_cache->checked[ $this->plugin_name ] = $this->plugin_data['Version'];

			set_site_transient( 'update_plugins', $update_cache );

		} else {

			$version_info = $update_cache->response[ $this->plugin_name ];

		}

		// Restore our filter
		add_filter( 'pre_set_site_transient_update_plugins', array($this, 'check_for_updates') );

		if( ! empty($update_cache->response[ $this->plugin_name ]) && version_compare( $this->plugin_data['Version'], $version_info->new_version, '<' ) ) {

			$wp_list_table  = _get_list_table( 'WP_Plugins_List_Table' );
			$changelog_link = network_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $this->plugin_name . '&amp;section=changelog&amp;TB_iframe=true&amp;width=772&amp;height=597' );

			include dirname( __FILE__ ) . '/views/html-ms-update.php';

		}
		
	}

	/**
	 * Plugin Row Action Links
	 *
	 *
	 * @since 2.0
	 *
	 * @param $links
	 *
	 * @return array
	 */
	public function action_links( $links ) {

		$links[] = '<a href="' . remove_query_arg( array(
			                                           'deactivated_licence',
			                                           'activated_licence',
		                                           ), add_query_arg( $this->plugin_slug . '_deactivate_licence', 1 ) ) . '">' . 'Deactivate License' . '</a>';

		return $links;
	}

	/**
	 * Show Notice Prompting User to Update
	 *
	 *
	 * @since 2.0
	 *
	 */
	public function key_notice() {

		if( ! $this->api_key && sizeof( $this->errors ) === 0 && ! get_option( $this->plugin_slug . '_hide_key_notice' ) ) {
			include dirname( __FILE__ ) . '/views/html-key-notice.php';
		}
	}

	/**
	 * Output Activated Key HTML
	 *
	 *
	 * @since 2.0
	 *
	 */
	public function activated_key_notice() {
		include dirname( __FILE__ ) . '/views/html-activated-key.php';
	}

	/**
	 *
	 *
	 *
	 * @since 2.0
	 *
	 */
	public function deactivated_key_notice() {

		include dirname( __FILE__ ) . '/views/html-deactivated-key.php';
	}

	/**
	 * Enqueue Admin Styles
	 *
	 *
	 * @since 2.0
	 *
	 */
	public function styles() {

		if( ! wp_style_is( 'smylesv2-updater-styles', 'enqueued' ) ) {
			wp_enqueue_style( 'smylesv2-updater-styles', plugins_url( basename( plugin_dir_path( $this->plugin_file ) ), basename( $this->plugin_file ) ) . '/includes/updater/assets/admin.css' );
		}
	}

	/**
	 * Show Key Input HTML
	 *
	 *
	 * @since 2.0
	 *
	 * @param $file
	 */
	public function key_input( $file ) {

		if( strtolower( basename( dirname( $file ) ) ) === strtolower( $this->plugin_slug ) ) {
			include dirname( __FILE__ ) . '/views/html-key-input.php';
		}
	}
}