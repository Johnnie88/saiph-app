<?php
/**
 * LoginPress Settings
 *
 * @since 1.0.9
 * @version 3.0.0
 */
if ( ! class_exists( 'LoginPress_Settings' ) ):

	class LoginPress_Settings {

		private $settings_api;

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 */
		function __construct() {

			include_once( LOGINPRESS_ROOT_PATH . '/classes/class-loginpress-settings-api.php' );
			$this->settings_api = new LoginPress_Settings_API;

			add_action( 'admin_init', array( $this, 'loginpress_setting_init' ) );
			add_action( 'admin_menu', array( $this, 'loginpress_setting_menu' ) );
		}

		/**
		 * Initialize the settings
		 *
		 * @return void
		 */
		function loginpress_setting_init() {

			//set the settings.
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );

			//initialize settings.
			$this->settings_api->admin_init();

			//reset settings.
			$this->load_default_settings();
		}

		/**
		 * Load the default settings
		 *
		 * @return void
		 */
		function load_default_settings() {

			$_loginpress_Setting = get_option( 'loginpress_setting' );
			if ( isset( $_loginpress_Setting['reset_settings'] ) && 'on' == $_loginpress_Setting['reset_settings'] ) {

				$loginpress_last_reset = array( 'last_reset_on' => date('Y-m-d') );
				update_option( 'loginpress_customization', $loginpress_last_reset );
				update_option( 'customize_presets_settings', 'minimalist' );
				$_loginpress_Setting['reset_settings'] = 'off';
				update_option( 'loginpress_setting', $_loginpress_Setting );
				add_action( 'admin_notices', array( $this, 'settings_reset_message' ) );
			}
		}

		/**
		 * Reset settings message
		 *
		 * @return void
		 */
		function settings_reset_message() {

			$class = 'notice notice-success';
			$message = __( 'Default Settings Restored', 'loginpress' );

			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
		}

		/**
		 * Code for add loginpress icon in admin bar.
		 *
		 * @since 1.0.9
		 * @return void
		 */
		function loginpress_setting_menu() {

			/**
			 * The White-labeling to hide the sidebar menu for specific user/s.
			 */
			if ( apply_filters( 'loginpress_sidebar_hide_menu_item', false ) ) {
				return;
			}

			add_action('admin_head', 'loginpressicon'); // admin_head is a hook loginpressicon is a function we are adding it to the hook

			// LoginPress Dashicon
			function loginpressicon() {
				$ttf   = plugins_url( '../loginpressfonts/loginpress.ttf?gb7unf', __FILE__ );
				$woff  = plugins_url( '../loginpressfonts/loginpress.woff?gb7unf', __FILE__ );
				$svg   = plugins_url( '../loginpressfonts/loginpress.svg?gb7unf', __FILE__ );
				$eotie = plugins_url( '../loginpressfonts/loginpress.eot?gb7unf#iefix', __FILE__ );
				$eot   = plugins_url( '../loginpressfonts/loginpress.eot?gb7unf', __FILE__ );
				echo "<style>
				@font-face {
					font-family: 'loginpress';
					src:  url('".$eot."');
					src:  url('".$eotie."') format('embedded-opentype'),
					url('".$ttf."') format('truetype'),
					url('".$woff."') format('woff'),
					url('".$svg."') format('svg');
					font-weight: normal;
					font-style: normal;
				}

				.icon-loginpress-dashicon:before {
					content: '\\e560';
					color: #fff;
				}

				#adminmenu li#toplevel_page_loginpress-settings>a>div.wp-menu-image:before{
					content: '\\e560';
					font-family: 'loginpress' !important;
					speak: none;
					font-style: normal;
					font-weight: normal;
					font-variant: normal;
					text-transform: none;
					line-height: 1;

					/* ===== Better Font Rendering ===== */
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale;
				}
				</style>";
			}

			// Create LoginPress Parent Page.
			add_menu_page( 
				__( 'LoginPress', 'loginpress' ),
				'LoginPress',
				'manage_options',
				"loginpress-settings",
				array( $this, 'plugin_page' ),
				false,
				50
			);

			// Create Submenu for LoginPress > Settings Page.
			add_submenu_page(
				'loginpress-settings',
				__( 'Settings', 'loginpress' ),
				__( 'Settings', 'loginpress' ),
				'manage_options',
				"loginpress-settings",
				array( $this, 'plugin_page' )
			);

			// Create Submenu for LoginPress > Customizer Page.
			add_submenu_page(
				'loginpress-settings',
				__( 'Customizer', 'loginpress' ),
				__( 'Customizer', 'loginpress' ),
				'manage_options',
				"loginpress",
				'__return_null'
			);

			// Create Submenu for LoginPress > Help Page.
			add_submenu_page(
				'loginpress-settings',
				__( 'Help', 'loginpress' ),
				__( 'Help', 'loginpress' ),
				'manage_options',
				"loginpress-help",
				array( $this, 'loginpress_help_page' )
			);

			// Create Submenu for LoginPress > Import / Export Page.
			add_submenu_page(
				'loginpress-settings',
				__( 'Import/Export LoginPress Settings', 'loginpress' ),
				__( 'Import / Export', 'loginpress' ),
				'manage_options',
				"loginpress-import-export",
				array( $this, 'loginpress_import_export_page' )
			);

			// Create Submenu for LoginPress > Add-Ons Page.
			add_submenu_page(
				'loginpress-settings',
				__( 'Add-Ons', 'loginpress' ),
				__( 'Add-Ons', 'loginpress' ),
				'manage_options',
				"loginpress-addons",
				array( $this, 'loginpress_addons_page' )
			);

		}

		/**
		 * Render the settings section for LoginPress.
		 *
		 * @since 1.0.0
		 * @version 3.0.0
		 *
		 * @return void
		 */
		function get_settings_sections() {

			/**
			 * Add a general settings section of LoginPress.
			 * id: unique section id
			 * title: Title of the section
			 * sub-title: Sub title of the section
			 * description: Description of the section
			 * video link: Video link for the section
			 */
			$loginpress_general_tab = array(
				array(
					'id'    => 'loginpress_setting',
					'title' => __( 'Settings', 'loginpress' ),
					'sub-title' => __( 'Login Page Setting', 'loginpress' ),
					'desc'  => sprintf( __( '%3$sEverything else is customizable through %1$sWordPress Customizer%2$s.%4$s', 'loginpress' ), '<a href="' . admin_url( 'admin.php?page=loginpress' ) . '">', '</a>', '<p>', '</p>' ),
					'video_link' => 'GMAwsHomJlE',

				),
			);

			/**
			 * Add Promotion tabs in settings page.
			 *
			 * @since 1.1.22
			 * @version 1.1.24
			 */
			if ( ! has_action( 'loginpress_pro_add_template' ) ) {

				include LOGINPRESS_DIR_PATH . 'classes/class-loginpress-promotion.php';
			}

			$sections = apply_filters( 'loginpress_settings_tab', $loginpress_general_tab );

			return $sections;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @since 1.0.9
		 * @version 3.0.0
		 * @return array settings fields
		 */
		function get_settings_fields() {

			/**
			 * @param array $_free_fields array of free fields.
			 * @var array
			 */
			$_free_fields = array(
				array(
					'name'  			=> 'enable_password_reset',
					'label' 			=> __( 'Force Password Reset', 'loginpress' ),
					'desc'  			=> __( 'Enable to enforce password reset after certain duration.', 'loginpress' ),
					'extra_desc'  		=> __( 'Enable to enforce password reset after certain duration.', 'loginpress' ),
					'type'  			=> 'checkbox'
				),
				array(
					'name'              => 'loginpress_password_reset_time_limit',
					'label'             => __( 'Password Reset Duration', 'loginpress' ),
					'desc'              => __( 'Set the duration in days after which the user will be forced to change password again. e.g 10.', 'loginpress' ),
					'placeholder'       => __( '10', 'loginpress' ),
					'min'               => 0,
					'max'            	=> $this->change_force_time_limit( 500 ),
					'step'              => '1',
					'type'              => 'number',
					'default'           => 0,
					'sanitize_callback' => 'absint'
				),
				array(
					'name'              => 'roles_for_password_reset',
					'label'             => __( 'Password Reset For', 'loginpress' ),
					'desc'              => __( 'Choose the roles for password reset forcefully to secure the site\'s security.', 'loginpress' ),
					'type'              => 'multicheck',
					'options'           => $this->get_all_roles(),
				),
				array(
					'name'              => 'session_expiration',
					'label'             => __( 'Session Expire', 'loginpress' ),
					

					'desc'              => sprintf( __( 'Set the session expiration time in minutes. e.g: 10', 'loginpress' ) ), //<br /> When you set the time, here you need to set the expiration cookies. for this, you just need to logout at least one time. After login again, it should be working fine.<br />For removing the session expiration just pass empty value in “Expiration” field and save it. Now clear the expiration cookies by logout at least one time.
					'placeholder'       => __( '10', 'loginpress' ),
					'min'               => 0,
					// 'max'            => 100,
					'step'              => '1',
					'type'              => 'number',
					'default'           => 'Title',
					'sanitize_callback' => 'absint'
				),
				// array(
				//   'name'  => 'enable_privacy_policy',
				//   'label' => __( 'Enable Privacy Policy', 'loginpress' ),
				//   'desc'  => __( 'Enable Privacy Policy checkbox on registration page.', 'loginpress' ),
				//   'type'  => 'checkbox'
				// ),
				// array(
				//   'name'  => 'privacy_policy',
				//   'label' => __( 'Privacy & Policy', 'loginpress' ),
				//   'desc'  => __( 'Right down the privacy and policy description.', 'loginpress' ),
				//   'type'  => 'wysiwyg',
				//   'default' => __( sprintf( __( '%1$sPrivacy Policy%2$s.', 'loginpress' ), '<a href="' . admin_url( 'admin.php?page=loginpress-settings' ) . '">', '</a>' ) )
				// ),
				array(
					'name'  => 'auto_remember_me',
					'label' => __( 'Auto Remember Me', 'loginpress' ),
					'desc'  => sprintf( __( 'Enable to keep the %1$sRemember Me%2$s option always checked on the Login Page.', 'loginpress' ), '<a href="' . esc_url( 'https://loginpress.pro/doc/enable-the-auto-remember-me-checkbox/?utm_source=loginpress-lite' ) . '" target="_blank">', '</a>' ),
					'type'  => 'checkbox'
				),
				array(
					'name'  => 'enable_reg_pass_field',
					'label' => __( 'Custom Password Fields', 'loginpress' ),
					'desc'  => sprintf( __( 'Enable to add %1$sCustom Password Fields%2$s to the Registration Form.', 'loginpress' ), '<a href="' . esc_url( 'https://loginpress.pro/doc/custom-password-fields-on-the-registration-form/?utm_source=loginpress-lite' ) . '" target="_blank">', '</a>' ),
					'type'  => 'checkbox'
				),
				array(
					'name'    => 'login_order',
					'label'   => __( 'Login Order', 'loginpress' ),
					// 'desc'    => __( 'Enable users to login using their username and/or email address.', 'loginpress' ),
					'type'    => 'radio',
					'default' => 'default',
					'options' => array(
						'default'  => __( 'Both Username Or Email Address', 'loginpress' ),
						'username' => __( 'Only Username', 'loginpress' ),
						'email'    => __( 'Only Email Address', 'loginpress' )
					)
				),
				array(
					'name'  => 'enable_pci_compliance',
					'label' => __( 'Enable PCI Compliance', 'loginpress' ),
					'desc'  => sprintf( __( 'Enable to add %1$sPCI Compliance%2$s to WordPress Login Forms.', 'loginpress' ), '<a href="' . esc_url( 'https://loginpress.pro/doc/wordpress-login-page-pci-compliance/?utm_source=loginpress-lite' ) . '" target="_blank">', '</a>' ),
					'type'  => 'checkbox'
				),
				// array(
				//   'name'  => 'login_with_email',
				//   'label' => __( 'Login with Email', 'loginpress' ),
				//   'desc'  => __( 'Force user to login with Email Only Instead Username.', 'loginpress' ),
				//   'type'  => 'checkbox'
				// ),
				array(
					'name'  => 'reset_settings',
					'label' => __( 'Reset customizer settings', 'loginpress' ),
					'desc'  => sprintf( __( 'Enable to reset customizer settings.%1$sNote: All your customization will be reverted back to the LoginPress default theme.%2$s', 'loginpress' ), '<span class="loginpress-settings-span">', '</span>' ),
					'type'  => 'checkbox'
				),
			);

			// Hide Advertisement in version 1.1.3
			// if ( ! has_action( 'loginpress_pro_add_template' ) ) {
			//   array_unshift( $_free_fields , array(
			//     'name'  => 'enable_recaptcha_promo',
			//     'label' => __( 'Enable reCAPTCHA', 'loginpress' ),
			//     'desc'  => __( 'Enable LoginPress reCaptcha', 'loginpress' ),
			//     'type'  => 'checkbox'
			//   ) );
			// }

			/**
			 * Add option to remove language switcher option
			 *
			 * @since 1.5.11
			 */
			if ( version_compare( $GLOBALS['wp_version'], '5.9', '>=' ) && ! empty( get_available_languages() ) ) {
				$_free_fields = $this->loginpress_language_switcher( $_free_fields );
			}

			/**
			 * Add WooCommerce lostpassword_url field.
			 *
			 * @since 1.1.7
			 */
			if ( class_exists( 'WooCommerce' ) ) {
				$_free_fields = $this->loginpress_woocommerce_lostpasword_url( $_free_fields );
			}

			// Add loginpress_uninstall field in version 1.1.9
			$_free_fields     = $this->loginpress_uninstallation_tool( $_free_fields );
			$_settings_fields = apply_filters( 'loginpress_pro_settings', $_free_fields );
			$settings_fields  = array( 'loginpress_setting' => $_settings_fields );
			$tab              = apply_filters( 'loginpress_settings_fields', $settings_fields );

			return $tab;
		}

		/**
		 * get all roles for force rest password after six months in settings section
		 * @since 3.0.0
		 * 
		 * @return array
		 */
		function get_all_roles() {

			global $wp_roles;
			$loginpress_force_reset_roles = array();

			foreach( $wp_roles->roles as $role => $val ) {

				$loginpress_force_reset_roles[ $val['name'] ] = sanitize_text_field( $val['name'] );
			}
			return $loginpress_force_reset_roles;
		}

		/**
		 * Main settings page content.
		 * @since 1.0.19
		 * @version 3.0.0
		 */
		function plugin_page() {

			echo $this::loginpress_admin_page_header();
			echo '<div class="wrap">';
			echo '<div class="loginpress-video-popup"><div class="loginpress-cross"></div><div class="loginpress-video-overlay"></div><div class="loginpress-video-frame"><iframe id="loginpress-video"  allow="autoplay" frameborder="0"></iframe></div></div>';
			echo '<h2 class="loginpress-settings-heading">';
			esc_html_e( 'LoginPress - Rebranding your boring WordPress Login pages', 'loginpress' );
			echo '</h2>';
			echo '<div class="loginpress-admin-setting">';
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();

			echo '</div>';
			echo '</div>';
		}

		/**
		 * [loginpress_help_page callback function for sub-page Help]
		 * @since 1.0.19
		 * @version 3.0.0
		 */
		function loginpress_help_page(){

			echo LoginPress_Settings::loginpress_admin_page_header();
			include LOGINPRESS_DIR_PATH . 'classes/class-loginpress-log.php';

			$html = '<div class="loginpress-help-page">';
			$html .= '<h2>' . esc_html__( 'Help & Troubleshooting', 'loginpress' ) . '</h2>';
			$html .="<p>";
			$html .= sprintf( __( 'Free plugin support is available on the %1$s plugin support forums%2$s.', 'loginpress' ), '<a href="https://wordpress.org/support/plugin/loginpress" target="_blank">', '</a>' );
			$html .="<br />";

			if ( ! class_exists( 'LoginPress_Pro' ) ) {
				$html .= sprintf( __( 'For premium features, add-ons and priority email support, %1$s upgrade to pro%2$s.', 'loginpress' ), '<a href="https://loginpress.pro/pricing/?utm_source=loginpress-lite&utm_medium=help-page&utm_campaign=pro-upgrade" target="_blank">', '</a>' );
			} else {
				$html .= sprintf( __( 'For premium features, add-ons and priority email support, Please submit a question %1$shere%2$s!', 'loginpress' ), '<a href="https://loginpress.pro/contact/" target="_blank">','</a>' );
			}

			$html .="<br />";
			$html .= sprintf( __( 'Found a bug or have a feature request? Please submit an issue %1$shere%2$s!', 'loginpress' ), '<a href="https://loginpress.pro/contact/" target="_blank">','</a>' );
			$html .="</p>";
			$html .= '<pre><textarea rows="25" cols="75" readonly="readonly">';
			$html .= LoginPress_Log_Info::get_sysinfo();
			$html .= '</textarea></pre>';
			$html .= '<input type="button" class="button loginpress-log-file" value="' . __( 'Download Log File', 'loginpress' ) . '"/>';
			$html .= '<span class="log-file-sniper"><img src="'. admin_url( 'images/wpspin_light.gif' ) .'" /></span>';
			$html .= '<span class="log-file-text">' . __( 'LoginPress Log File Downloaded Successfully!' ) . '</span>';
			$html .= '</div>';
			echo $html;
		}

		/**
		 * [loginpress_import_export_page callback function for sub-page Import / Export]
		 * @since 1.0.19
		 * @version 3.0.0
		 */
		function loginpress_import_export_page(){

			echo LoginPress_Settings::loginpress_admin_page_header();
			include LOGINPRESS_DIR_PATH . 'include/loginpress-import-export.php';
		}

		/**
		 * [loginpress_addons_page callback function for sub-page Add-ons]
		 * @since 1.0.19
		 * @version 3.0.0
		 */
		function loginpress_addons_page() {

			echo LoginPress_Settings::loginpress_admin_page_header();
			$active_plugins = get_option('active_plugins');

			if ( in_array( 'loginpress-pro/loginpress-pro.php', $active_plugins ) && version_compare( LOGINPRESS_PRO_VERSION, '3.0.0', '<' ) ) {
				include LOGINPRESS_DIR_PATH . 'classes/class-loginpress-deprecated-addons.php';
			} else {
				include LOGINPRESS_DIR_PATH . 'classes/class-loginpress-addons.php';
			}
			$obj_loginpress_addons	= new LoginPress_Addons();
			$obj_loginpress_addons->_addon_html();
		}

		/**
		 * Get all the pages
		 *
		 * @return array page names with key value pairs
		 */
		function get_pages() {
			$pages = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ($pages as $page) {
					$pages_options[$page->ID] = $page->post_title;
				}
			}

			return $pages_options;
		}

		/**
		 * loginpress_woocommerce_lostpasword_url [merge a woocommerce lostpassword url field with the last element of array.]
		 * @param  array $fields_list
		 * @since 1.1.7
		 * @return array
		 */
		function loginpress_woocommerce_lostpasword_url( $fields_list ) {

			$array_elements   = array_slice( $fields_list, 0, -1 ); //slice a last element of array.
			$last_element     = end( $fields_list ); // last element of array.
			$lostpassword_url = array(
				'name'  => 'lostpassword_url',
				'label' => __( 'Lost Password URL', 'loginpress' ),
				'desc'  => __( 'Use WordPress default lost password URL instead of WooCommerce custom lost password URL.', 'loginpress' ),
				'type'  => 'checkbox'
			);
			$last_two_elements = array_merge( array( $lostpassword_url, $last_element ) ); // merge last 2 elements of array.
			return array_merge( $array_elements, $last_two_elements ); // merge an array and return.
		}

		/**
		* loginpress_language_switcher [merge a language switcher in the settings element of array.]
		*
		* @param  array $fields_list The free fields of LoginPress.
		* @since 1.5.11
		* @return array the total fields including the added field of language switcher
		*/
		function loginpress_language_switcher( $fields_list ) {

			$array_elements   = array_slice( $fields_list, 0, -1 ); //slice a last element of array.
			$last_element     = end( $fields_list ); // last element of array.
			$switcher_option  = array(
				'name'  => 'enable_language_switcher',
				'label' => __( 'Language Switcher', 'loginpress' ),
				'desc'  => sprintf( __( 'Enable to remove %1$sLanguage Switcher Dropdown%2$s on Login Forms.', 'loginpress' ), '<i>', '</i>' ),
				'type'  => 'checkbox'
			);
			$lang_switch_element = array_merge( array( $switcher_option , $last_element ) ); // merge last 2 elements of array.
			return array_merge( $array_elements, $lang_switch_element ); // merge an array and return.
		}

		/**
		 * loginpress_uninstallation_filed [merge a uninstall loginpress field with array of element.]
		 * @param  array $fields_list
		 * @since 1.1.9
		 * @return array
		 */
		function loginpress_uninstallation_filed( $fields_list ) {

			$loginpress_page_check = '';
			if ( is_multisite() ) {
				$loginpress_page_check = __( 'and LoginPress page', 'loginpress' );
			}

			$loginpress_db_check = array( array(
				'name'  => 'loginpress_uninstall',
				'label' => __( 'Remove Settings On Uninstall', 'loginpress' ),
				'desc'  => sprintf( esc_html__( 'Enable to remove all custom settings made %1$s by LoginPress upon uninstall.' ), $loginpress_page_check ),
				'type'  => 'checkbox'
			) );
			
			return array_merge( $fields_list, $loginpress_db_check ); // merge an array and return.
		}

		/**
		 * loginpress_uninstallation_tool [Pass return true in loginpress_multisite_uninstallation_tool filter's callback for enable uninsatalltion control on each site.]
		 * @param  array $_free_fields
		 * @since 1.1.9
		 * @return array
		 */
		function loginpress_uninstallation_tool( $_free_fields ) {

			if ( is_multisite() && ! apply_filters( 'loginpress_multisite_uninstallation_tool', false ) ) {
				if ( get_current_blog_id() == '1' ) {
					$_free_fields = $this->loginpress_uninstallation_filed( $_free_fields );
				}
			} else {
				$_free_fields = $this->loginpress_uninstallation_filed( $_free_fields );
			}

			return $_free_fields;
		}

		/**
		 * Filter to increase days for force reset password in settings
		 * @param  int $days
		 * @since 3.0.0
		 * @return int $days
		 */
		function change_force_time_limit( $days ) {

			$force_reset_duration = absint( apply_filters( 'increase_force_time_limit', $days ) );
			$force_reset_duration = 0 === $force_reset_duration ? 182 : $force_reset_duration;
			return $force_reset_duration;
		}

		/**
		 * Header HTML.
		 * Call on LoginPress pages at dashboard.
		 *
		 * @since 3.0.0
		 */
		public static function loginpress_admin_page_header() {

			if ( ! has_action( 'loginpress_pro_add_template' ) ) {
				$button_text = '<a href="https://loginpress.pro/pricing/?utm_source=loginpress-lite&utm_medium=top-links&utm_campaign=pro-upgrade" class="loginpress-pro-cta" target="_blank"><span class="dashicons dashicons-star-filled"></span>' . sprintf( __( 'Upgrade%1$s to Pro%2$s', 'loginpress' ), '<span>', '</span>' ) . '</a>';
			} else {
				$button_text = '<a href="https://loginpress.pro/contact/?utm_source=loginpress-lite&utm_medium=top-links&utm_campaign=pro-upgrade" class="loginpress-pro-cta" target="_blank">' . esc_html__( 'Support', 'loginpress' ) . '</a>';
			}
			?>
			<div class="loginpress-header-wrapper">
				<div class="loginpress-header-container">
					<div class="loginpress-header-logo">
						<a href="https://loginpress.pro/pricing/?utm_source=loginpress-lite&utm_medium=top-links&utm_campaign=pro-upgrade" target="_blank"><img src="<?php echo LOGINPRESS_DIR_URL . 'img/loginpress-logo.svg'; ?>"></a>
					</div>
					<div class="loginpress-header-cta">
						<?php echo $button_text; ?>
						<a href="https://loginpress.pro/documentation/?utm_source=loginpress-lite&utm_medium=top-links&utm_campaign=pro-upgrade" class="loginpress-documentation" target="_blank"><?php echo esc_html__( 'Documentation', 'loginpress' ); ?></a>
					</div>
				</div>
			</div>
			<?php
		}
	}
endif;
