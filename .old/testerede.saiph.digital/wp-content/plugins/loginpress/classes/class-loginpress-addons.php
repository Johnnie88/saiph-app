<?php
/**
 * This is an Add-ons page. Purpose of this page is to show a list of all the add-ons available to extend the functionality of LoginPress.
 *
 * @package LoginPress
 * @since 1.0.19
 * @version 3.0.5
 */

if ( ! class_exists( 'LoginPress_Addons' ) ) :

	class LoginPress_Addons {

		private $addons_construct;
		private $addons_array;
		private $addons_meta;
		protected $plugins_list;

		/**
		 * Class Constructor.
		 */
		function __construct() {
			$this->includes();
			$this->addons_array_construct();
			$this->plugins_list = get_plugins();
			$this->addons_array = get_option( 'loginpress_pro_addons' );
		}

		private function includes() {
			require_once LOGINPRESS_DIR_PATH . 'classes/class-loginpress-addons-meta.php';
		}

		/**
		 * Construct addons array.
		 *
		 * @since 3.0.5
		 */
		public function addons_array_construct() {

			// Don't uncomment this bleow unless you know what you are doing.
			// delete_option( 'loginpress_pro_addons' );

			$this->addons_array = get_option( 'loginpress_pro_addons' );
			$this->addons_meta  = LoginPress_Addons_Meta::addons_details();
		}

		/**
		 * Render addons page.
		 *
		 * @since 1.0.19
		 * @version 3.0.5
		 * @return void HTML
		 */
		public function show_addon_page() {

			if ( class_exists( 'LoginPress_Pro' ) ) {

				if ( LoginPress_Pro::is_activated() ) {

					$expiration_date = LoginPress_Pro::get_expiration_date();

					if ( 'lifetime' == $expiration_date ) {
						echo esc_html__( 'You have a lifetime license, it will never expire.', 'loginpress' );
					} else {
						echo '<div class="main_notice_msg">' . sprintf(
							esc_html__( 'Your (%2$s) license key is valid until %1$s.', 'loginpress' ),
							'<strong>' . date_i18n( get_option( 'date_format' ), strtotime( $expiration_date, current_time( 'timestamp' ) ) ) . '</strong>',
							LoginPress_Pro::get_license_type()
						) . '</div>';
					} ?>

					<div class="addon_cards_wraper"> 
						<?php
						if ( isset( $this->addons_array ) && ! empty( $this->addons_array ) ) {
							foreach ( $this->addons_array as $addon ) {
								$this->addon_card( $addon );
							}
						}
						?>
					</div> 
					<?php
				} else {
					$expiration_date = LoginPress_Pro::get_expiration_date();
					$license_data    = LoginPress_Pro::get_registration_data();

					if ( isset( $license_data['license_data']['error'] ) && 'expired' === $license_data['license_data']['error'] ) {
						echo '<div class="main_notice_msg">' . sprintf( esc_html__( 'Your license key has been expired on %1$s.', 'loginpress' ), date_i18n( get_option( 'date_format' ), strtotime( $expiration_date, current_time( 'timestamp' ) ) ) ) . '</div>';
					} else {
						echo '<div class="main_notice_msg">' . sprintf( esc_html__( 'You need to activate your license to use the following add-ons.', 'loginpress' ) ) . '</div>';

					}

					?>
					<div class="addon_cards_wraper"> 
						<?php
						if ( isset( $this->addons_array ) && ! empty( $this->addons_array ) ) {
							foreach ( $this->addons_array as $addon ) {
								$this->addon_card_free( $addon );
							}
						}
						?>
					</div> 
					<?php
				}
			} else {
				echo '<div class="main_notice_msg">' . sprintf( esc_html__( 'You need to upgrade to LoginPress Pro to access these add-ons.', 'loginpress' ) ) . '</div>';
				?>

				<div class="addon_cards_wraper"> 
				<?php

				if ( isset( $this->addons_array ) && ! empty( $this->addons_array ) ) {
					foreach ( $this->addons_array as $addon ) {
						$this->addon_card_free( $addon );
					}
				}
				?>
				</div> 
		
				<?php
			}
		}

		/**
		 * Generate pro addons card.
		 *
		 * @param array $addon
		 * @since 1.0.19
		 * @version 3.0.5
		 * @return void HTML
		 */
		function addon_card( $addon ) {

			$addon_slug  = $addon['slug'];
			$addon_thumb = LOGINPRESS_DIR_URL . 'img/addons/' . $addon_slug . '.png';
			?>

			<div class="loginpress-extension <?php echo true == $addon['is_free'] ? 'loginpress-free-add-ons' : ''; ?> ">
				<a target="_blank" href="https://wpbrigade.com/wordpress/plugins/loginpress-pro/?utm_source=loginpress-lite&utm_medium=addons-coming-soon&utm_campaign=pro-upgrade" class="logoinpress_addons_links">
					<h3>
						<img src=<?php echo esc_url( $addon_thumb ); ?> class="logoinpress_addons_thumbnails"/>
						<span><?php echo esc_html( $this->addons_meta[ $addon_slug ]['title'] ); ?></span>
					</h3>
				</a>
				<?php echo '<p>' . $this->addons_meta[ $addon_slug ]['excerpt'] . '</p>'; ?>
				<p><?php echo $this->check_addon_status( $addon ); ?></p>
				<p><?php echo $this->ajax_responce( $this->addons_meta[ $addon_slug ]['title'], $addon['slug'] ); ?></p>
				</div>
			<?php
		}

		/**
		 * Ajax workflow.
		 *
		 * @param string $text
		 * @param string $slug
		 * @since 1.0.19
		 * @version 3.0.5
		 * @return void HTML
		 */
		function ajax_responce( $text, $slug ) {

			if ( $this->license_life( $slug ) ) {
				$message = __( $text . ' Something Wrong.', 'loginpress' );
			} else {
				$message = __( 'Your License Key isn\'t valid', 'loginpress' );
			}

			$html  = '<div id="loginpressEnableAddon' . $slug . '" class="loginpress-addon-enable" style="display:none;">
				<div class="loginpress-logo-container">
				<img src="' . plugins_url( '../../loginpress/img/loginpress-logo-divid-logo.svg', __FILE__ ) . '" alt="loginpress">
				<svg class="circular-loader" viewBox="25 25 50 50" >
					<circle class="loader-path" cx="50" cy="50" r="18" fill="none" stroke="#d8d8d8" stroke-width="1" />
				</svg>
				</div>
				<p>' . __( 'Activating ' . $text . '...', 'loginpress' ) . '</p>
				</div>';
			$html .= '<div id="loginpressActivatedAddon' . $slug . '" class="loginpress-install activated" style="display:none">
				<svg class="circular-loader2" viewBox="25 25 50 50" >
					<circle class="loader-path2" cx="50" cy="50" r="18" fill="none" stroke="#00c853" stroke-width="1" />
				</svg>
				<div class="checkmark draw"></div>
				<p>' . __( $text . ' Activated.', 'loginpress' ) . '</p>
				</div>';
			$html .= '<div id="loginpressUninstallingAddon' . $slug . '" class="loginpress-uninstalling activated" style="display:none">
				<div class="loginpress-logo-container">
					<img src="' . plugins_url( '../../loginpress/img/loginpress-logo-divid-logo.svg', __FILE__ ) . '" alt="loginpress">
					<svg class="circular-loader" viewBox="25 25 50 50" >
					<circle class="loader-path" cx="50" cy="50" r="18" fill="none" stroke="#d8d8d8" stroke-width="1" />
					</svg>
				</div>
				<p>' . __( 'Deactivating ' . $text . '...', 'loginpress' ) . '</p>
				</div>';
			$html .= '<div id="loginpressDeactivatedAddon' . $slug . '" class="loginpress-uninstall activated" style="display:none">
				<svg class="circular-loader2" viewBox="25 25 50 50" >
					<circle class="loader-path2" cx="50" cy="50" r="18" fill="none" stroke="#ff0000" stroke-width="1" />
				</svg>
				<div class="checkmark draw"></div>
				<p>' . __( $text . ' Deactivated.', 'loginpress' ) . '</p>
				</div>';
			$html .= '<div id="loginpressWrongAddon' . $slug . '" class="loginpress-wrong activated" style="display:none">
				<svg class="checkmark_login" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
					<circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"></circle>
					<path class="checkmark__check" stroke="#ff0000" fill="none" d="M16 16 36 36 M36 16 16 36"></path>
				</svg>
				<p>' . $message . '</p>
				</div>';

			return $html;
		}

		/**
		 * Render free addons cards.
		 *
		 * @param array $addon
		 * @since 1.0.19
		 * @version 3.0.5
		 * @return void HTML
		 */
		function addon_card_free( $addon ) {
			$addon_slug  = $addon['slug'];
			$addon_thumb = LOGINPRESS_DIR_URL . 'img/addons/' . $addon_slug . '.png';
			?>

			<div class="loginpress-extension <?php echo true == $addon['is_free'] ? 'loginpress-free-add-ons' : ''; ?> ">
				<a target="_blank" href="https://wpbrigade.com/wordpress/plugins/loginpress-pro/?utm_source=loginpress-lite&utm_medium=addons-coming-soon&utm_campaign=pro-upgrade" class="logoinpress_addons_links">
					<h3>
						<img src=<?php echo esc_url( $addon_thumb ); ?> class="logoinpress_addons_thumbnails"/>
						<span><?php echo esc_html( $this->addons_meta[ $addon_slug ]['title'] ); ?></span>
					</h3>
				</a>
				<?php
				echo '<p>' . $this->addons_meta[ $addon_slug ]['excerpt'] . '</p>';
				$this->check_free_addon_status( $addon );
				echo $this->ajax_responce( $this->addons_meta[ $addon_slug ]['title'], $addon['slug'] );
				?>
			</div>
			<?php
		}

		/**
		 * Check the license life.
		 *
		 * @param array $categories
		 * @since 1.0.19
		 * @version 3.0.5
		 * @return boolean
		 */
		function is_addon_licensed( $categories ) {

			if ( ! class_exists( 'LoginPress_Pro' ) ) {
				return false;
			}

			if ( LoginPress_Pro::get_license_id() === '2' && in_array( 'loginpress-pro-small-business', $categories ) ) {
				return true;
			} elseif ( LoginPress_Pro::get_license_id() === '3' && in_array( 'loginpress-pro-agency', $categories ) ) {
				return true;
			} elseif ( LoginPress_Pro::get_license_id() === '4' && in_array( 'loginpress-pro-agency', $categories ) ) {
				return true;
			} elseif ( LoginPress_Pro::get_license_id() === '5' ) {
				return true;
			} elseif ( LoginPress_Pro::get_license_id() === '6' ) {
				return true;
			} elseif ( LoginPress_Pro::get_license_id() === '7' && in_array( 'loginpress-pro-agency', $categories ) ) {
				return true;
			} elseif ( LoginPress_Pro::get_license_id() === '8' && in_array( 'loginpress-pro-agency', $categories ) ) {
				return true;
			} elseif ( LoginPress_Pro::get_license_id() === '9' && in_array( 'loginpress-pro-agency', $categories ) ) {
				return true;
			} elseif ( LoginPress_Pro::get_license_id() === '1' && in_array( 'loginpress-free-add-ons', $categories ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Get the Add-Ons data.
		 *
		 * @since 1.0.19
		 */
		function _get_addons() {

			// For Testing
			// delete_transient( 'loginpress_api_addons' );

			// Get the transient where the addons are stored on-site.
			$data = get_transient( 'loginpress_api_addons' );

			// If we already have data, return it.
			if ( ! empty( $data ) ) {
				return $data;
			}

			// Make sure this matches the exact URL from your site.
			$url = 'https://wpbrigade.com/wp-json/wpbrigade/v1/plugins?addons=loginpress-pro-add-ons';

			// Get data from the remote URL.
			$response = wp_remote_get( $url, array( 'timeout' => 20 ) );

			if ( ! is_wp_error( $response ) ) {

				// Decode the data that we got.
				$data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( ! empty( $data ) && is_array( $data ) ) {

					// Store the data for a week.
					set_transient( 'loginpress_api_addons', $data, 7 * DAY_IN_SECONDS );

					return $data;
				}
			}

			return false;
		}

		/**
		 * Convert the slug into an array
		 *
		 * @param object $categories
		 * @since 3.0.5
		 * @return array $arr
		 */
		function convert_to_array( $categories ) {

			$arr = array();
			foreach ( $categories as $category ) {
				$arr[] = $category->slug;
			}
			return $arr;
		}

		/**
		 * Check the life of the license, Is it legal or not.
		 *
		 * @param string $slug slug of the addon
		 * @since 3.0.5
		 * @return boolean
		 */
		public function license_life( $slug ) {

			$response = $this->_get_addons();
			if ( is_array( $response ) ) {
				foreach ( $response as $key => $value ) {

					if ( 'loginpress-' . $slug == $value->slug ) {
						return $this->is_addon_licensed( $this->convert_to_array( $value->categories ) );
					}
				}
			}
			return false;
		}

		/**
		 * Check addon status.
		 *
		 * @since 1.0.19
		 * @version 3.0.5
		 *
		 * @return void HTML
		 */
		public function check_addon_status( $addon ) {
			$slug = $addon['slug'];

			if ( $addon['is_free'] ) {
				$this->check_free_addon_status( $addon );
			} elseif ( $this->license_life( $slug ) ) {
				if ( true === $addon['is_active'] ) {
					?>

						<input name="loginpress_pro_addon_nonce" type="hidden" value="<?php echo wp_create_nonce( 'uninstall_' . $slug ); ?>">
						<input name="loginpress_pro_addon_slug" type="hidden" value="<?php echo $slug; ?>">
						<input id="<?php echo $slug; ?>" type="checkbox" checked class="loginpress-radio loginpress-radio-ios loginpress-uninstall-pro-addon" value="<?php echo $slug; ?>">
						<label for="<?php echo $slug; ?>" class="loginpress-radio-btn"></label>

					<?php } else { ?>

						<input name="loginpress_pro_addon_nonce" type="hidden" value="<?php echo wp_create_nonce( 'install-plugin_' . $slug ); ?>">
						<input name="loginpress_pro_addon_slug" type="hidden" value="<?php echo $slug; ?>">
						<input name="loginpress_pro_addon_id" type="hidden" value="<?php echo $slug; ?>">
						<input id="<?php echo $slug; ?>" type="checkbox" class="loginpress-radio loginpress-radio-ios loginpress-active-pro-addon" value="<?php echo $slug; ?>">
						<label for="<?php echo $slug; ?>" class="loginpress-radio-btn"></label>

					<?php
					}
			} else {
				?>
					<p><a target="_blank" href="https://wpbrigade.com/wordpress/plugins/loginpress-pro/?utm_source=loginpress-lite&utm_medium=addons-coming-soon&utm_campaign=pro-upgrade" class="button-primary"><?php esc_html_e( 'UPGRADE NOW', 'loginpress' ); ?></a></p>
				<?php

			}
		}

		/**
		 * Check installation status for free addons.
		 *
		 * @param array $addon
		 *
		 * @since 3.0.5
		 */
		function check_free_addon_status( $addon ) {
			if ( true === $addon['is_free'] ) {
				$slug = $addon['slug'] . '/' . $addon['slug'] . '.php';

				if ( is_plugin_active( $slug ) ) {
					?>

					<input name="loginpress_pro_addon_nonce" type="hidden" value="<?php echo wp_create_nonce( 'uninstall_' . $addon['slug'] ); ?>">
					<input name="loginpress_pro_addon_slug" type="hidden" value="<?php echo $addon['slug']; ?>">
					<input id="<?php echo $addon['slug']; ?>" type="checkbox" checked class="loginpress-radio loginpress-radio-ios loginpress-uninstall-pro-addon" value="<?php echo $addon['slug']; ?>">
					<label for="<?php echo $addon['slug']; ?>" class="loginpress-radio-btn"></label>
					
				<?php } elseif ( array_key_exists( $slug, $this->plugins_list ) ) { ?>

					<input name="loginpress_pro_addon_nonce" type="hidden" value="<?php echo wp_create_nonce( 'install-plugin_' . $addon['slug'] ); ?>">
					<input name="loginpress_pro_addon_slug" type="hidden" value="<?php echo $addon['slug']; ?>">
					<input id="<?php echo $addon['slug']; ?>" type="checkbox" class="loginpress-radio loginpress-radio-ios loginpress-active-pro-addon" value="<?php echo $addon['slug']; ?>">
					<label for="<?php echo $addon['slug']; ?>" class="loginpress-radio-btn"></label>

				<?php } else { ?>

					<input name="loginpress_pro_addon_nonce" type="hidden" value="<?php echo wp_create_nonce( 'install-plugin_' . $addon['slug'] ); ?>">
					<input name="loginpress_pro_addon_slug" type="hidden" value="<?php echo $addon['slug']; ?>">
					<input id="<?php echo $addon['slug']; ?>" type="checkbox" class="loginpress-radio loginpress-radio-ios loginpress-install-pro-addon" value="<?php echo $addon['slug']; ?>">
					<label for="<?php echo $addon['slug']; ?>" class="loginpress-radio-btn"></label>

					<?php
				}
			} else {
				?>
				<p><a target="_blank" href="https://wpbrigade.com/wordpress/plugins/loginpress-pro/?utm_source=loginpress-lite&utm_medium=addons-coming-soon&utm_campaign=pro-upgrade" class="button-primary"><?php esc_html_e( 'UPGRADE NOW', 'loginpress' ); ?></a></p>
				<?php
			}
		}

		/**
		 * Generate the markup for addons.
		 *
		 * @since 1.0.19
		 */
		/**
		 * Generate the markup for addons.
		 *
		 * @since 1.0.19
		 * @version 3.0.5
		 * @return void Add Ons HTML
		 */
		function _addon_html() {

			?>
			<!-- Style for Add-ons Page -->
			<style media="screen">
				.loginpress_page_loginpress-addons #wpcontent .loginpress-addons-wrap{
					padding: 0px 20px 0 0;
					max-width: 1370px;
					width: 100%;
					margin: 0 auto;
					box-sizing: border-box;
				}
				.loginpress_page_loginpress-addons{
					background-color: #F6F9FF;
				}
				.loginpress-extension p:empty {
					display: none;
				}
					#wpbody-content .loginpress-extension .button-primary{
					border:0;
					text-shadow:none;
					background: #516885;
					padding: 12px 18px;
					height:auto;
					font-size:15px;
					cursor: pointer;
					position: absolute;
					bottom: 20px;
					left: 50%;
					transform: translateX(-50%);
					box-shadow:none;
					border-radius:5px;
					transition: background-color .3s;
					font-size: 16px;
					line-height: 24px;
					color: #fff;
					font-family: "Poppins", sans-serif;
					font-weight: 500;
					text-decoration: none;
					}
				#wpbody-content .loginpress-extension .button-primary:active,
				#wpbody-content .loginpress-extension .button-primary:hover,
				#wpbody-content .loginpress-extension .button-primary:focus{
					background: #2B3D54;
					box-shadow: none;
					outline: none;
					}
				.notice_msg{
					box-shadow: rgba(0, 0, 0, 0.1) 0px 1px 1px 0px;
					background: rgb(255, 255, 255);
					border-left: 4px solid #46b450;
					margin: 5px 0 20px;
					padding: 15px;
				}
				.loginpress-extension button.button-primary{
					background: #f9fafa;
					border-radius: 0;
					box-shadow: none;
					color: #444;
					position: absolute;
					bottom: 15px;
					left: 50%;
					transform: translateX(-50%);
					border: 2px solid #a5dff6 !important;
					background: #d3f3ff54 !important;
					cursor: default;
					transition: background-color .3s;
				}
				.loginpress-extension button.button-primary:visited,
				.loginpress-extension button.button-primary:active,
				.loginpress-extension button.button-primary:hover,
				.loginpress-extension button.button-primary:focus{
					background: #36bcf2;
					color: #444;
					border: 0;
					outline: none;
					box-shadow: none;
				}
				.logoinpress_addons_thumbnails{
					max-width: 100px;
					position: absolute;
					top: 5px;
					left: 10px;
					height: auto;
					width: auto;
					max-height: 75px;
					position: static;
					vertical-align: middle;
					margin-right: 20px;
					margin-top: 0;
				}
				.loginpress-extension p {
					margin: 0;
					padding: 10px 20px;
					color: #5C7697;
					font-size: 13px;
					font-family: "Poppins", sans-serif;
				}
				.loginpress-addons-loading-errors {
					padding-top: 15px;
				}
				.loginpress-addons-loading-errors img {
					float: left;
					padding-right: 10px;
				}
				.loginpress-free-add-ons h3:after{
					content: "Free";
					position: absolute;
					top: 10px;
					right: -30px;
					width: 100px;
					height: 30px;
					background-color: #7FC22B;
					color: #fff;
					transform: rotate(45deg);
					line-height: 30px;
					text-align: center;
					font-size: 13px;
				}

				.loginpress-extension .logoinpress_addons_links{
					position: relative;
					background-color: #DEE5F2;
					text-decoration: none !important;
					display: inline-block;
					width: 100%;
					line-height: 90px;
					padding-bottom: 0px;
					height: auto;
				}

				@media only screen and (min-width: 1700px) {
					.loginpress-extension{
						width: calc(25% - 30px);
					}
				}
				@media only screen and (max-width: 1400px) {
					.loginpress-extension{
						width: calc(50% - 30px);
					}
				}
				@media only screen and (max-width: 670px) {
					.loginpress-extension:nth-child(n){
						width:calc(100% - 15px);
						margin: 0 0 20px;
					}

					.addon_cards_wraper{
						margin: 0;
					}
				}
				.loginpress-addon-enable{
					position: absolute;
					top: -2px;;
					left: -2px;
					bottom: -2px;
					right: -2px;
					background: #fff;
					z-index: 100;
				}
				.loginpress-logo-container{
					position: absolute;
					top: 50%;
					left: 50%;
					transform: translate(-50%, -50%);
					width: 250px;
					height: 250px;
					display: flex;
					flex-direction: column;
					align-items: center;
				}
				.loginpress-logo-container img{
					height: auto;
					position: absolute;
					top: 50%;
					left: 50%;
					transform: translate(-50%, -50%);
					width: 100%;
					max-width: 100px;
				}
				.loginpress-addon-enable p{
					font-weight: 700;
					position: absolute;
					bottom: 0;
					left: 0;
					width: 100%;
					text-align: center;
					box-sizing: border-box;
				}
				.loader-path {
					stroke-dasharray: 150,200;
					stroke-dashoffset: -10;
					-webkit-animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite;
					animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite;
					stroke-linecap: round;
				}
				@-webkit-keyframes rotate {
					100% {
						-webkit-transform: rotate(360deg);
						transform: rotate(360deg);
					}
				}

				@keyframes rotate {
					100% {
						-webkit-transform: rotate(360deg);
						transform: rotate(360deg);
					}
				}
				.circular-loader{
					-webkit-animation: rotate 2s ease-in-out infinite, color 6s ease-in-out infinite;
					animation: rotate 2s ease-in-out infinite, color 6s ease-in-out infinite;
					stroke-linecap: round;
				}
				@keyframes loader-spin {
					0% {
						transform: rotate(0deg);
					}
					100% {
						transform: rotate(360deg);
					}
				}
				@keyframes dash {
					0% {
						stroke-dasharray: 1,200;
						stroke-dashoffset: 0;
					}
					50% {
						stroke-dasharray: 89,200;
						stroke-dashoffset: -35;
					}
					100% {
						stroke-dasharray: 89,200;
						stroke-dashoffset: -124;
					}
				}
				.loginpress-install,.loginpress-uninstall,.loginpress-uninstalling, .loginpress-wrong{
					position: absolute;
					top: -2px;;
					left: -2px;
					bottom: -2px;
					right: -2px;
					background: rgb(255,255,255);
					z-index: 100;
				}
				.loader-path2{
					stroke-dasharray: 150,200;
					stroke-dashoffset: 150px;
					-webkit-animation: dashtwo 1s ease-in-out 1 forwards;
					animation: dashtwo 1s ease-in-out 1 forwards;
				}
				.checkmark__circle {
					stroke-width: 2;
					stroke: #ff0000;
				}
				.checkmark_login {
					width: 150px;
					height: 150px;
					border-radius: 50%;
					display: block;
					stroke-width: 2;
					stroke: #fff;
					stroke-miterlimit: 10;
					margin: 10% auto;
					animation: scale .3s ease-in-out .2s both;
					position: absolute;
					top: 50%;
					left: 50%;
					margin: -75px 0 0 -75px;
				}
				.checkmark__check {
					transform-origin: 50% 50%;
					stroke-dasharray: 29;
					stroke-dashoffset: 29;
					animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.5s forwards;
				}
				@keyframes stroke {
					100% {
						stroke-dashoffset: 0;
					}
				}
				@keyframes scale {
					0%, 100% {
						transform: none;
					}
					50% {
						transform: scale3d(1.1, 1.1, 1);
					}
				}
				@keyframes fill {
					100% {
						box-shadow: inset 0px 0px 0px 30px #7ac142;
					}
				}
				@keyframes dashtwo {
					0% {
						stroke-dashoffset: 150px;
					}
					100% {
						stroke-dashoffset: 20px;
					}
				}
				.circular-loader2, .circular-loader3{
					width: 200px;
					height: 200px;
					top: 50%;
					left: 50%;
					transform: translate(-50%, -50%) rotate(-90deg);
					position: absolute;
				}
				.loginpress-install.activated p{
					position: absolute;
					bottom: 0;
					left: 0;
					text-align: center;
					width: 100%;
					box-sizing: border-box;
				}
				.loginpress-wrong.activated p{
					position: absolute;
					bottom: 0;
					left: 0;
					text-align: center;
					width: 100%;
					box-sizing: border-box;
					color: #ff0000;
					font-weight: 700;
				}
				.checkmark {
					top: 50%;
					position: absolute;
					left: 50%;
					transform: translate(-50%, -50%);
					width: 140px;
					height: 140px;
				}
				.checkmark.draw:after {
					animation-duration: 800ms;
					animation-delay: 1s;
					animation-timing-function: ease;
					animation-name: checkmark;
					transform: scaleX(-1) rotate(135deg);
					opacity: 0;
					animation-fill-mode: forwards;
				}
				.checkmark:after {
					height: 4em;
					width: 2em;
					transform-origin: left top;
					border-right: 2px solid #00c853;
					border-top: 2px solid #00c853;
					content: '';
					left: 42px;
					top: 70px;
					position: absolute;
				}
				.loginpress-uninstall .checkmark:after{
					border-right: 2px solid #ff0000;
					border-top: 2px solid #ff0000;
				}
				.loginpress-uninstall p, .loginpress-uninstalling p{
					position: absolute;
					bottom: 0;
					left: 0;
					text-align: center;
					width: 100%;
					box-sizing: border-box;
				}
				@keyframes checkmark {
					0% {
						height: 0;
						width: 0;
						opacity: 1;
					}
					20% {
						height: 0;
						width: 2em;
						opacity: 1;
					}
					40% {
						height: 4em;
						width: 2em;
						opacity: 1;
					}
					100% {
						height: 4em;
						width: 2em;
						opacity: 1;
					}
				}
				.loginpress-extension input[type="checkbox"]{
					display: none;
				}
				.loginpress-extension .loginpress-radio-btn{
						outline: 0;
					display: block;
					width: 36px;
					height: 18px;
					position: relative;
					cursor: pointer;
					-webkit-user-select: none;
					-moz-user-select: none;
					-ms-user-select: none;
					user-select: none;
				}
				.loginpress-extension input[type=checkbox].loginpress-radio-ios + .loginpress-radio-btn {
					background: #fff;
					border-radius: 2em;
					padding: 2px;
					-webkit-transition: all .4s ease;
					transition: all .4s ease;
					border: 2px solid #D2DDF2;
					position: absolute;
					bottom: 20px;
					left: 50%;
					transform: translateX(-50%);
				}
				.loginpress-extension input[type=checkbox].loginpress-radio + .loginpress-radio-btn:after{
					position: relative;
					display: block;
					content: "";
					width: 18px;
					height: 18px;
				}
				.loginpress-extension input[type=checkbox].loginpress-radio-ios + .loginpress-radio-btn:after {
					border-radius: 2em;
					background: #fbfbfb;
					-webkit-transition: left 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), padding 0.3s ease, margin 0.3s ease;
					transition: left 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), padding 0.3s ease, margin 0.3s ease;
					border: 2px solid #D2DDF2;
					box-sizing: border-box;
					left: 0;
				}
				.loginpress-extension input[type=checkbox].loginpress-radio + .loginpress-radio-btn:hover {
					background-color: #e2e4e7;
				}
				.loginpress-extension input[type=checkbox].loginpress-radio-ios + .loginpress-radio-btn:active:after {
					border-width: 9px;
				}
				.loginpress-extension input[type=checkbox].loginpress-radio:checked + .loginpress-radio-btn:after {
					left: 18px;
					border-color: #fff;
					background: #33b3db;
					border-width: 9px;
				}
				.loginpress-extension input[type=checkbox].loginpress-radio:checked + .loginpress-radio-btn{
					background: #5C7697;
					border-color: #5C7697;
				}
				</style>

			<div class="wrap loginpress-addons-wrap">
				<h2 class='opt-title'><?php esc_html_e( 'Extend the functionality of LoginPress with these awesome Add-ons', 'loginpress' ); ?></h2>
				<div class="tabwrapper">
					<?php $this->show_addon_page(); ?>
				</div>
			</div>
			<?php
		}
	} // Enf of Class

endif;
