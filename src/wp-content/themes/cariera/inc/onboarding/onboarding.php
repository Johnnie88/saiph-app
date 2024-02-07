<?php

namespace Cariera\Onboarding;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Onboarding {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * TGMPA plugin loader.
	 */
	protected $tgmpa;

	/**
	 * Theme name.
	 */
	protected $theme;

	/**
	 * Gnodesign website url.
	 */
	protected $gnodesign_url;

	/**
	 * User capability.
	 */
	protected $capability;

	/**
	 * Theme license.
	 */
	protected $license;

	/**
	 * Theme activation status.
	 *
	 * @since  1.5.1
	 */
	private static $status;

	/**
	 * Constructor
	 *
	 * @since 1.5.1
	 */
	public function __construct() {
		// Helpers.
		$this->theme         = 'Cariera';
		$this->gnodesign_url = 'cariera_theme';
		$this->capability    = 'edit_theme_options';
		$this->license       = new \Cariera\Onboarding\License();
		self::$status        = $this->license->activation_status();

		// Plugins if active.
		if ( self::$status ) {
			require get_template_directory() . '/inc/onboarding/plugins/activate-plugins.php';

			if ( class_exists( 'TGM_Plugin_Activation' ) ) {
				$this->tgmpa = isset( $GLOBALS['tgmpa'] ) ? $GLOBALS['tgmpa'] : TGM_Plugin_Activation::get_instance();
			}
		}

		// Actions.
		add_action( 'admin_menu', [ $this, 'add_menu_item' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'onboarding_assets' ] );
		add_action( 'after_switch_theme', [ $this, 'redirect' ], 30 );

		// Plugins.
		add_filter( 'tgmpa_load', [ $this, 'load_tgmpa' ], 10, 1 );
		add_action( 'wp_ajax_cariera_plugins', [ $this, 'ajax_plugins' ], 10, 0 );
		add_action( 'cariera_onboarding_plugins', [ $this, 'plugins' ] );
		add_action( 'cariera_onboarding_import', [ $this, 'import' ] );
	}

	/**
	 * Add admin menu page
	 *
	 * @since 1.5.1
	 */
	public function add_menu_item() {
		// Main Menu Page.
		add_menu_page(
			$this->theme,
			$this->theme,
			$this->capability,
			$this->gnodesign_url,
			[ $this, 'page_template' ],
			get_template_directory_uri() . '/assets/images/admin-icon.png',
			2
		);

		// Welcome Submenu Page.
		add_submenu_page(
			$this->gnodesign_url,
			esc_html__( 'Welcome', 'cariera' ),
			esc_html__( 'Welcome', 'cariera' ),
			$this->capability,
			$this->gnodesign_url,
			[ $this, 'page_template' ]
		);
	}

	/**
	 * Get activation status of the theme
	 *
	 * @since 1.7.0
	 */
	public static function activation_status() {
		return self::$status;
	}

	/**
	 * Loading scripts and styles for the onboarding
	 *
	 * @since 1.5.1
	 */
	public function onboarding_assets() {
		$this->load_scripts();
		$this->load_styles();
	}

	/**
	 * Onboarding scripts
	 *
	 * @since 1.5.1
	 */
	public function load_scripts() {
		$version = \Cariera\get_assets_version();

		wp_register_script( 'cariera-onboarding', get_template_directory_uri() . '/assets/dist/js/onboarding.js', [ 'jquery' ], $version, true );
		wp_register_script( 'jquery-confirm', get_template_directory_uri() . '/assets/vendors/jquery-confirm/jquery-confirm.js', [ 'jquery' ], '3.2.0', true );

		$tgma_url = self::$status ? $this->tgmpa->get_tgmpa_url() : '';

		wp_localize_script(
			'cariera-onboarding',
			'cariera_onboarding',
			[
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'tgm_plugin_nonce' => [
					'update'  => wp_create_nonce( 'tgmpa-update' ),
					'install' => wp_create_nonce( 'tgmpa-install' ),
				],
				'tgm_bulk_url'     => $tgma_url,
				'wpnonce'          => wp_create_nonce( 'cariera_onboarding_nonce' ),
				'verify_text'      => esc_html__( '...verifying', 'cariera' ),
			]
		);
	}

	/**
	 * Onboarding styles
	 *
	 * @since 1.5.1
	 */
	public function load_styles() {
		$version = \Cariera\get_assets_version();
		$suffix = is_rtl() ? '.rtl' : '';

		wp_register_style( 'cariera-onboarding', get_template_directory_uri() . '/assets/dist/css/onboarding' . $suffix . '.css', [], $version );
		wp_register_style( 'jquery-confirm', get_template_directory_uri() . '/assets/vendors/jquery-confirm/jquery-confirm.css', [], '3.2.0' );
	}

	/**
	 * Onboarding main page template
	 *
	 * @since   1.5.1
	 * @version 1.7.2
	 */
	public function page_template() {
		get_template_part( 'templates/backend/onboarding/main', null, [ 'status' => self::$status ] );
	}

	/**
	 * Redirection on activate.
	 *
	 * @since 1.5.1
	 */
	public function redirect() {
		$redirect = admin_url( 'admin.php?page=' . $this->gnodesign_url . '' );

		if ( is_admin() && isset( $_GET['activated'] ) ) {
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Conditionally load TGMPA
	 *
	 * @since 1.5.1
	 */
	public function load_tgmpa( $status ) {
		return is_admin() || current_user_can( 'install_themes' );
	}

	/**
	 * Get registered TGMPA plugins
	 *
	 * @since 1.5.1
	 */
	protected function get_tgmpa_plugins() {
		$plugins = [
			'all'      => [], // Meaning: all plugins which still have open actions.
			'install'  => [],
			'update'   => [],
			'activate' => [],
		];

		foreach ( $this->tgmpa->plugins as $slug => $plugin ) {
			if ( $this->tgmpa->is_plugin_active( $slug ) && false === $this->tgmpa->does_plugin_have_update( $slug ) ) {
				continue;
			} else {
				$plugins['all'][ $slug ] = $plugin;

				if ( ! $this->tgmpa->is_plugin_installed( $slug ) ) {
					$plugins['install'][ $slug ] = $plugin;
				} else {
					if ( false !== $this->tgmpa->does_plugin_have_update( $slug ) ) {
						$plugins['update'][ $slug ] = $plugin;
					}
					if ( $this->tgmpa->can_plugin_activate( $slug ) ) {
						$plugins['activate'][ $slug ] = $plugin;
					}
				}
			}
		}

		return $plugins;
	}

	/**
	 * Install plugins AJAX function
	 *
	 * @since 1.5.1
	 */
	public function ajax_plugins() {

		if ( ! check_ajax_referer( 'cariera_onboarding_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) || self::$status == 0 ) {
			exit( 0 );
		}

		$json      = [];
		$tgmpa_url = $this->tgmpa->get_tgmpa_url();
		$plugins   = $this->get_tgmpa_plugins();

		// Activating plugins.
		foreach ( $plugins['activate'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = [
					'url'           => $tgmpa_url,
					'plugin'        => [ $slug ],
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-activate',
					'action2'       => - 1,
					'message'       => esc_html__( 'Activating', 'cariera' ),
				];
				break;
			}
		}

		// Updating plugins.
		foreach ( $plugins['update'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = [
					'url'           => $tgmpa_url,
					'plugin'        => [ $slug ],
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-update',
					'action2'       => - 1,
					'message'       => esc_html__( 'Updating', 'cariera' ),
				];
				break;
			}
		}

		// Install plugin.
		foreach ( $plugins['install'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = [
					'url'           => $tgmpa_url,
					'plugin'        => [ $slug ],
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-install',
					'action2'       => - 1,
					'message'       => esc_html__( 'Installing', 'cariera' ),
				];
				break;
			}
		}

		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) ); // Used for checking if duplicates happen, move to next plugin.
			wp_send_json( $json );
		} else {
			wp_send_json(
				[
					'done'    => 1,
					'message' => esc_html__( 'Success', 'cariera' ),
				]
			);
		}

		exit;
	}

	/**
	 * Plugins install page setup
	 *
	 * @since 1.5.1
	 */
	public function plugins() {
		if ( ! self::$status ) {
			echo '<div class="onboarding-notice error">';
			echo '<p>' . esc_html__( 'Activate the theme to be able to install required plugins.', 'cariera' ) . '</p>';
			echo '</div>';

			return;
		}

		// Variables.
		$url    = wp_nonce_url( add_query_arg( [ 'plugins' => 'go' ] ), 'cariera' );
		$method = '';
		$fields = array_keys( $_POST );
		$creds  = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields );

		tgmpa_load_bulk_installer();

		if ( false === $creds ) {
			return true;
		}

		if ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );
			return true;
		}

		// Are there plugins that need installing/activating?
		$plugins          = $this->get_tgmpa_plugins();
		$required_plugins = $recommended_plugins = [];
		$count            = count( $plugins['all'] );

		// Split the plugins into required and recommended.
		foreach ( $plugins['all'] as $slug => $plugin ) {
			if ( ! empty( $plugin['required'] ) ) {
				$required_plugins[ $slug ] = $plugin;
			} else {
				$recommended_plugins[ $slug ] = $plugin;
			}
		} ?>

		<?php if ( $count ) { ?>
			<div class="onboarding-notice">
				<p><?php esc_html_e( 'Your website needs a few essential plugins. The following plugins will be installed and activated.', 'cariera' ); ?></p>
			</div>
		<?php } ?>

		<?php if ( $count ) { ?>    
			<form action="" method="post">
				<ul class="onboarding-install-plugins">

					<?php if ( ! empty( $required_plugins ) ) : ?>
						<?php foreach ( $required_plugins as $slug => $plugin ) : ?>
							<li data-slug="<?php echo esc_attr( $slug ); ?>">
								<input type="checkbox" name="default_plugins[<?php echo esc_attr( $slug ); ?>]" class="checkbox" id="default_plugins_<?php echo esc_attr( $slug ); ?>" value="1" checked>

								<label for="default_plugins_<?php echo esc_attr( $slug ); ?>">
									<i></i>
									<span><?php echo esc_html( $plugin['name'] ); ?></span>
									<span class="badge"><?php esc_html_e( 'Required', 'cariera' ); ?></span>
									<?php echo false !== $this->tgmpa->does_plugin_have_update( $slug ) ? '<span class="badge update">' . esc_html__( 'Update available', 'cariera' ) . '</span>' : ''; ?>
									<span class="message"></span>
								</label>

								<div class="loader"><span class="circle"></span></div>
								<span class="checkmark">
									<div class="checkmark_stem"></div>
									<div class="checkmark_kick"></div>
								</span>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php if ( ! empty( $recommended_plugins ) ) : ?>
						<?php foreach ( $recommended_plugins as $slug => $plugin ) : ?>
							<li data-slug="<?php echo esc_attr( $slug ); ?>">
								<input type="checkbox" name="default_plugins[<?php echo esc_attr( $slug ); ?>]" class="checkbox" id="default_plugins_<?php echo esc_attr( $slug ); ?>" value="1" checked>

								<label for="default_plugins_<?php echo esc_attr( $slug ); ?>">
									<i></i>
									<span><?php echo esc_html( $plugin['name'] ); ?></span>
									<?php echo false !== $this->tgmpa->does_plugin_have_update( $slug ) ? '<span class="badge update">' . esc_html__( 'Update available', 'cariera' ) . '</span>' : ''; ?>
									<span class="message"></span>
								</label>

								<div class="loader"><span class="circle"></span></div>
								<span class="checkmark">
									<div class="checkmark_stem"></div>
									<div class="checkmark_kick"></div>
								</span>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>

				</ul>

				<a href="#" class="onboarding-btn" data-callback="install_plugins">
					<span class="text"><?php esc_html_e( 'Install & Activate', 'cariera' ); ?></span>
					<span class="btn-loader"></span>
				</a>
			</form>
			<?php
		} else {
			echo '<div class="onboarding-notice success">';
			echo '<p>' . esc_html__( 'All plugins are installed and up to date. You can import the demo content now.', 'cariera' ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Plugins install page setup
	 *
	 * @since   1.5.1
	 * @version 1.7.0
	 */
	public function import() {
		// If theme is not active.
		if ( ! self::$status ) {
			echo '<div class="onboarding-notice error">';
			echo '<p>' . esc_html__( 'Activate the theme to be able to import the demo data.', 'cariera' ) . '</p>';
			echo '</div>';

			return;
		}

		get_template_part( 'templates/backend/onboarding/import-requirements' );

		// If core plugin is not installed and activated.
		if ( ! \Cariera\cariera_core_is_activated() ) {
			echo '<div class="onboarding-notice error">';
			echo '<p>' . esc_html__( 'Install & activate all the required plugins to be able to import the demo data.', 'cariera' ) . '</p>';
			echo '</div>';

			return;
		}
	}
}
