<?php
/**
 * Plugin Name: Cariera Core
 * Plugin URI:  https://themeforest.net/item/cariera-job-board-wordpress-theme/20167356
 * Description: This is the Core plugin of Cariera Theme.
 * Version:     1.7.2
 * Author:      Gnodesign
 * Author URI:  https://themeforest.net/user/gnodesign
 * Text Domain: cariera
 * Domain Path: /lang
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'autoload.php';

final class Cariera_Core {

	/**
	 * The single instance of the class.
	 *
	 * @var Cariera_Core
	 */
	private static $instance = null;

	/**
	 * Plugin version
	 */
	private $version = '1.7.2';

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  1.5.5
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor function.
	 *
	 * @since   1.5.5
	 * @version 1.7.2
	 */
	public function __construct() {
		// Define Constants.
		$this->define_constants();

		// Initialize Core installation.
		new \Cariera_Core\Install();

		// Main Actions.
		add_action( 'plugins_loaded', [ $this, 'init_plugin' ], 10 );

		// Include files.
		$this->include_files();
	}

	/**
	 * Define the constants
	 *
	 * @since 1.5.5
	 */
	protected function define_constants() {
		define( 'CARIERA_CORE', __FILE__ );
		define( 'CARIERA_CORE_VERSION', $this->version );
		define( 'CARIERA_URL', plugins_url( '', __FILE__ ) );
		define( 'CARIERA_CORE_PATH', __DIR__ );
		define( 'CARIERA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'CARIERA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	}

	/**
	 * Initializes plugin.
	 *
	 * @since   1.5.5
	 * @version 1.7.2
	 */
	public function init_plugin() {
		// Add Actions.
		add_action( 'init', [ $this, 'localization_init' ] );
		add_action( 'init', [ $this, 'image_sizes' ] );

		// Initialize the whole core plugin.
		$this->init();
	}

	/**
	 * Loading Text Domain file for translations
	 *
	 * @since  1.5.5
	 */
	public function localization_init() {
		load_plugin_textdomain( 'cariera', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Adds image sizes
	 *
	 * @since   1.5.5
	 */
	public function image_sizes() {
		add_image_size( 'cariera-avatar', 500, 500, true );
	}

	/**
	 * This will initialize the whole core plugin.
	 *
	 * @since 1.7.2
	 */
	protected function init() {
		\Cariera_Core\Init::instance();
	}

	/**
	 * Include files
	 *
	 * @since 1.7.2
	 */
	private function include_files() {
		include_once CARIERA_CORE_PATH . '/inc/core/promotions/promotions.php';
	}
}

/**
 * Function to run the plugin.
 *
 * @since 1.5.5
 */
function cariera_core_plugin() {
	return Cariera_Core::instance();
}

cariera_core_plugin();
