<?php

/**
 * WordPress Header and Footer Setup
 *
 * @package wp-headers-and-footers
 */

if ( ! class_exists( 'WPHeaderAndFooter_Setting' ) ) :
	/**
	 * The WPHeaderAndFooter Settings class
	 */
	class WPHeaderAndFooter_Setting {

		/**
		 * Settings sections array
		 *
		 * @var array $settings_api The settings API array.
		 */
		private $settings_api;

		/**
		 * Settings sections array
		 *
		 * @var object $diagnostics The diagnostics object of another class.
		 */
		private $diagnostics;
		/**
		 * The constructor of WPHeaderAndFooter Settings class
		 *
		 * @since 1.0.0
		 * @version 2.1.0
		 */
		public function __construct() {

			include_once WPHEADERANDFOOTER_DIR_PATH . 'classes/class-settings-api.php';
			include_once WPHEADERANDFOOTER_DIR_PATH . 'classes/class-diagnostics-log.php';

			$this->settings_api = new WPHeaderAndFooter_Settings_API();
			$this->diagnostics  = new WPHeadersAndFooters_Diagnostics_Log();

			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'register_options_page' ) );
		}

		/**
		 * Admin initialize function.
		 */
		public function admin_init() {
			// Set the settings.
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );

			// Initialize settings.
			$this->settings_api->admin_init();
		}

		/**
		 * Register the plugin settings panel
		 *
		 * @since 1.1.0
		 */
		public function register_options_page() {

			add_submenu_page( 'options-general.php', __( 'WP Headers and Footers', 'wp-headers-and-footers' ), __( 'WP Headers and Footers', 'wp-headers-and-footers' ), 'manage_options', 'wp-headers-and-footers', array( $this, 'wp_header_and_footer_callback' ) );
		}

		/**
		 * The settings section.
		 *
		 * @since 1.1.0
		 * @version 2.1.0
		 */
		public function get_settings_sections() {

			$diagnostic_log = $this->diagnostics->wp_headers_and_footers_get_sysinfo();

			$sections = array(
				array(
					'id'    => 'wpheaderandfooter_basics',
					'title' => __( 'Scripts', 'wp-headers-and-footers' ),
				),
				array(
					'id'    => 'wpheaderandfooter_settings',
					'title' => __( 'Settings', 'wp-headers-and-footers' ),
					'desc'  => __( 'Set your priorities for each script tag.', 'wp-headers-and-footers' ),
				),
				array(
					'id'    => 'wpheaderandfooter_diagnostic_log',
					'title' => __( 'Help & Troubleshooting', 'wp-headers-and-footers' ),
					'desc'  => $diagnostic_log,
				),
			);
			return $sections;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @since 1.0.0
		 * @version 2.0.0
		 *
		 * @return array settings fields
		 */
		public function get_settings_fields() {
			$settings_fields = array(
				'wpheaderandfooter_basics' => array(
					array(
						'name'  => 'wp_header_textarea',
						'label' => __( 'Scripts in Header', 'wp-headers-and-footers' ),
						/* Translators: The header textarea description */
						'desc'  => sprintf( __( 'These scripts will be printed in the %1$s section.', 'wp-headers-and-footers' ), '&#60head&#62' ),
						'type'  => 'textarea',
					),
					array(
						'name'  => 'wp_body_textarea',
						'label' => __( 'Scripts in Body', 'wp-headers-and-footers' ),
						/* Translators: The body textarea description */
						'desc'  => sprintf( __( 'These scripts will be printed below the %1$s tag.', 'wp-headers-and-footers' ), '&#60body&#62' ),
						'type'  => 'textarea',
					),
					array(
						'name'  => 'wp_footer_textarea',
						'label' => __( 'Scripts in Footer', 'wp-headers-and-footers' ),
						/* Translators: The footer textarea description */
						'desc'  => sprintf( __( 'These scripts will be printed below the %1$s tag.', 'wp-headers-and-footers' ), '&#60footer&#62' ),
						'type'  => 'textarea',
					),
				),
				'wpheaderandfooter_settings' => array(
					array(
						'name'        => 'wp_header_priority',
						'label'       => __( "Header's Priority:", 'wp-headers-and-footers' ),
						/* Translators: The header textarea description */
						'desc'        => sprintf( __( 'The priority for %1$s section. %2$sDefault is 10%3$s', 'wp-headers-and-footers' ), '&#60head&#62', '<i>', '</i>' ),
						'type'        => 'number',
						'placeholder' => '1',
					),
					array(
						'name'        => 'wp_body_priority',
						'label'       => __( "Body's Priority:", 'wp-headers-and-footers' ),
						/* Translators: The body textarea description */
						'desc'        => sprintf( __( 'The priority for %1$s tag. %2$sDefault is 10%3$s', 'wp-headers-and-footers' ), '&#60body&#62', '<i>', '</i>' ),
						'type'        => 'number',
						'placeholder' => '10',
					),
					array(
						'name'        => 'wp_footer_priority',
						'label'       => __( "Footer's Priority:", 'wp-headers-and-footers' ),
						/* Translators: The footer textarea description */
						'desc'        => sprintf( __( 'The priority for %1$s tag. %2$sDefault is 10%3$s', 'wp-headers-and-footers' ), '&#60footer&#62', '<i>', '</i>' ),
						'type'        => 'number',
						'placeholder' => '99',
					),
					array(
						'name'  => 'remove_all_settings',
						'label' => __( 'Reset Settings:', 'wp-headers-and-footers' ),
						/* Translators: The footer textarea description */
						'desc'  => sprintf( __( 'Remove all scripts and settings on uninstall.', 'wp-headers-and-footers' ) ),
						'type'  => 'checkbox',
					),
				),
			);

			return $settings_fields;
		}

		/**
		 * The header and footer settings section and forms callback
		 *
		 * @since 1.1.0
		 * @version 2.0.0
		 */
		public function wp_header_and_footer_callback() {
			echo $this::wp_hnf_admin_page_header();

			echo '<div class="wrap wp-headers-and-footers">';
			echo '<h1 style="display:none;">' . __( 'Insert Headers And Footers', 'wp-headers-and-footers' ) . '</h1>';
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();

			echo '</div>';
		}

		/**
		 * Get all the pages
		 *
		 * @return array page names with key value pairs
		 */
		public function get_pages() {
			$pages         = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ( $pages as $page ) {
					$pages_options[ $page->ID ] = $page->post_title;
				}
			}

			return $pages_options;
		}

		/**
		 * Header HTML.
		 * Call on Header and Footer page at dashboard.
		 *
		 * @since 2.1.0
		 */
		public static function wp_hnf_admin_page_header() {
			?>
			<div class="wp_hnf-header-wrapper">
				<div class="wp_hnf-header-container">
					<div class="wp_hnf-header-logo">
						<a href="<?php echo esc_url( 'https://wpbrigade.com' ); ?>" target="_blank"><img src="<?php echo esc_url( WPHEADERANDFOOTER_DIR_URL . 'asset/img/logo.svg' ); ?>"></a>
					</div>
					<div class="wp_hnf-header-cta">
					<a href="#" id="wpheaderandfooter_diagnostic_log-header"><?php echo esc_html( 'Diagnostic' ); ?><span><?php echo esc_html( ' Log' ); ?></span></a>

					<a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/wp-headers-and-footers/' ); ?>" class="wp_hnf-pro-cta" target="_blank">
						<?php echo esc_html__( 'Support', 'wp-headers-and-footers' ); ?>
					</a>
					</div>
				</div>
			</div>
			<?php
		}
	}
endif;
