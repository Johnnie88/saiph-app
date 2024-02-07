<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

/**
* Handling all the AJAX calls in LoginPress.
*
* @since 1.0.19
* @version 3.0.5
* @class LoginPress_AJAX
*/

if ( ! class_exists( 'LoginPress_AJAX' ) ) :

	class LoginPress_AJAX {

		/*
		 * * * * * * * * *
		* Class constructor
		* * * * * * * * * */
		public function __construct() {

			$this::init();
		}

		public function init() {

			$ajax_calls = array(
				'export'            => false,
				'import'            => false,
				'help'              => false,
				'deactivate'        => false,
				'optout_yes'        => false,
				'presets'           => false,
				'video_url'         => false,
				'youtube_video_url' => false,
				'activate_addon'    => false,
				'deactivate_addon'  => false,
			);

			foreach ( $ajax_calls as $ajax_call => $no_priv ) {

				add_action( 'wp_ajax_loginpress_' . $ajax_call, array( $this, $ajax_call ) );

				if ( $no_priv ) {
					add_action( 'wp_ajax_nopriv_loginpress_' . $ajax_call, array( $this, $ajax_call ) );
				}
			}
		}

		/**
		 * Activate Plugins.
		 *
		 * @since 1.2.2
		 * @version 3.0.0
		 */
		function activate_addon() {

			$plugin_slug = sanitize_text_field( $_POST['slug'] );

			check_ajax_referer( 'install-plugin_' . $plugin_slug, '_wpnonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'No cheating, huh!' );
			}
			if ( defined( 'LOGINPRESS_PRO_VERSION' ) && version_compare( LOGINPRESS_PRO_VERSION, '3.0.0', '>=' ) ) {
				$addons = get_option( 'loginpress_pro_addons' );

				if ( $addons ) {

					foreach ( $addons as $addon ) {
						if ( $plugin_slug == $addon['slug'] ) {

							if ( true == $addon['is_free'] ) {
								activate_plugins( $addon['slug'] . '/' . $addon['slug'] . '.php' );

								echo wp_create_nonce( 'uninstall_' . $plugin_slug );
							}

							$addons[ $plugin_slug ]['is_active'] = true;
							break;
						}
					}
					if ( class_exists( 'LoginPress_Pro' ) && $plugin_slug !== 'login-logout-menu' ) {
						if ( LoginPress_Pro::addon_wrapper( $plugin_slug ) ) {

							update_option( 'loginpress_pro_addons', $addons );
							do_action( 'loginpress_pro_addon_activation', $plugin_slug );
							echo wp_create_nonce( 'uninstall_' . $plugin_slug );

						} else {
							echo 'erroneous';
						}
					}
				} else {
					echo 'erroneous';
				}
			} else {
				$free_slug = 'login-logout-menu' == $plugin_slug ? $plugin_slug . '/' . $plugin_slug . '.php' : $plugin_slug;
				if ( ! is_plugin_active( $free_slug ) ) {
					activate_plugins( $free_slug );
				}

				echo wp_create_nonce( 'uninstall_' . $free_slug );
			}
			wp_die();
		}

		/**
		 * Deactivate Plugins.
		 *
		 * @since 1.2.2
		 * @version 3.0.0
		 */
		function deactivate_addon() {

			$plugin_slug = esc_html( $_POST['slug'] );

			check_ajax_referer( 'uninstall_' . $plugin_slug, '_wpnonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'No cheating, huh!' );
			}
			if ( defined('LOGINPRESS_PRO_VERSION') && version_compare( LOGINPRESS_PRO_VERSION, '3.0.0', '>=' ) ) {

				$addons = get_option( 'loginpress_pro_addons' );

				if ( $addons ) {

					foreach ( $addons as $addon ) {
						if ( $plugin_slug === $addon['slug'] ) {

							if ( true == $addon['is_free'] ) {
								deactivate_plugins( $addon['slug'] . '/' . $addon['slug'] . '.php' );
							}

							$addons[ $plugin_slug ]['is_active'] = false;

							break;
						}
					}

					update_option( 'loginpress_pro_addons', $addons );
				}

				echo wp_create_nonce( 'install-plugin_' . $plugin_slug );
			} else {
				$free_slug = 'login-logout-menu' === $plugin_slug ? $plugin_slug . '/' . $plugin_slug . '.php' : $plugin_slug;

				deactivate_plugins( $free_slug );

				echo wp_create_nonce( 'install-plugin_' . $free_slug );
			}
			wp_die();
		}

		/**
		 * Import LoginPress Settings, update loginPress settings meta.
		 *
		 * @since 1.0.19
		 * @version 3.0.0
		 */
		public function import() {

			check_ajax_referer( 'loginpress-import-nonce', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'No cheating, huh!' );
			}

			$lg_imp_tmp_name = $_FILES['file']['tmp_name'];
			$lg_file_content = file_get_contents( $lg_imp_tmp_name );
			$loginpress_json = json_decode( $lg_file_content, true );

			if ( json_last_error() == JSON_ERROR_NONE ) {

				foreach ( $loginpress_json as $object => $array ) {

					// Check for LoginPress customizer images.
					if ( 'loginpress_customization' == $object ) {

						update_option( $object, $array );

						foreach ( $array as $key => $value ) {

							// Array of loginpress customizer images.
							$imagesCheck = array( 'setting_logo', 'setting_background', 'setting_form_background', 'forget_form_background', 'gallery_background' );

							/**
							* [if json fetched data has array of $imagesCheck]
							 *
							* @var [array]
							*/
							if ( in_array( $key, $imagesCheck ) ) {

								global $wpdb;
								// Count the $value of that $key from {$wpdb->posts}.
								// $query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid='$value'";
								$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid='%s'", $value ) );

								if ( $count < 1 && ! empty( $value ) ) {
									$file             = array();
									$file['name']     = basename( $value );
									$file['tmp_name'] = download_url( $value ); // Downloads a url to a local temporary file.

									if ( is_wp_error( $file['tmp_name'] ) ) {
										@unlink( $file['tmp_name'] );
										// return new WP_Error( 'lpimgurl', 'Could not download image from remote source' );
									} else {
										$id                 = media_handle_sideload( $file, 0 ); // Handles a sideloaded file.
										$src                = wp_get_attachment_url( $id ); // Returns a full URI for an attachment file.
										$loginpress_options = get_option( 'loginpress_customization' ); // Get option that was updated previously.

										// Change the options array properly.
										$loginpress_options[ "$key" ] = $src;

										// Update entire array again for save the attachment w.r.t $key.
										update_option( $object, $loginpress_options );
									}
								} // media_upload.
							} // images chaeck.
						} // inner foreach.
					} // loginpress_customization check.

					if ( 'loginpress_setting' == $object ) {

						$loginpress_options = get_option( 'loginpress_setting' );
						// Check $loginpress_options is exists.
						if ( isset( $loginpress_options ) && ! empty( $loginpress_options ) ) {

							foreach ( $array as $key => $value ) {

								// Array of loginpress Settings that doesn't import.
								$setting_array = array( 'captcha_enable', 'captcha_language', 'captcha_theme', 'recaptcha_type', 'secret_key', 'secret_key_v2_invisible', 'secret_key_v3', 'site_key', 'site_key_v2_invisible', 'site_key_v3', 'good_score', 'enable_repatcha' );

								if ( ! in_array( $key, $setting_array ) ) {

									// Change the options array properly.
									$loginpress_options[ "$key" ] = $value;
									// Update array w.r.t $key exists.
									update_option( $object, $loginpress_options );
								}
							} // inner foreach.
						} else {

							update_option( $object, $array );
						}
					} // loginpress_setting check.

					if ( 'customize_presets_settings' == $object ) {

						update_option( 'customize_presets_settings', $array );

					}
				} // endforeach.
			} else {
				echo 'error';
			}
			wp_die();
		}

		/**
		 * Export LoginPress Settings
		 *
		 * @since 1.0.19
		 * @version 3.0.0
		 * @return json $loginpress_db settings of the loginPress in json format.
		 */
		public function export() {

			check_ajax_referer( 'loginpress-export-nonce', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'No cheating, huh!' );
			}

			$loginpress_db            = array();
			$loginpress_setting_opt   = array();
			$loginpress_customization = get_option( 'loginpress_customization' );
			$loginpress_setting       = get_option( 'loginpress_setting' );
			$loginpress_preset        = get_option( 'customize_presets_settings' );
			$loginpress_setting_fetch = array( 'captcha_enable', 'captcha_language', 'captcha_theme', 'recaptcha_type', 'secret_key', 'secret_key_v2_invisible', 'secret_key_v3', 'site_key', 'site_key_v2_invisible', 'site_key_v3', 'good_score', 'enable_repatcha' );

			if ( $loginpress_customization ) {

				$loginpress_db['loginpress_customization'] = $loginpress_customization;
			}
			if ( $loginpress_setting ) {

				foreach ( $loginpress_setting as $key => $value ) {
					if ( ! in_array( $key, $loginpress_setting_fetch ) ) {
						$loginpress_setting_opt[ $key ] = $value;
					}
				}

				$loginpress_db['loginpress_setting'] = $loginpress_setting_opt;
			}

			if ( $loginpress_preset ) {

				$loginpress_db['customize_presets_settings'] = $loginpress_preset;
			}

			$loginpress_db = json_encode( $loginpress_db );

			echo $loginpress_db;

			wp_die();
		}

		/**
		 * Download the log file from Help page.
		 *
		 * @since 1.0.19
		 * @version 3.0.0
		 * @return string
		 */
		public function help() {

			check_ajax_referer( 'loginpress-log-nonce', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'No cheating, huh!' );
			}

			include LOGINPRESS_DIR_PATH . 'classes/class-loginpress-log.php';

			echo LoginPress_Log_Info::get_sysinfo();

			wp_die();
		}

		/**
		 * Get response from user on plugin deactivation.
		 *
		 * @since 1.0.15
		 * @version 3.0.0
		 * @return response
		 */
		public function deactivate() {

			check_ajax_referer( 'loginpress-deactivate-nonce', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'No cheating, huh!' );
			}

			$email         = get_option( 'admin_email' );
			$_reason       = sanitize_text_field( wp_unslash( $_POST['reason'] ) );
			$reason_detail = sanitize_text_field( wp_unslash( $_POST['reason_detail'] ) );
			$reason        = '';

			if ( $_reason == '1' ) {
				$reason = 'I only needed the plugin for a short period';
			} elseif ( $_reason == '2' ) {
				$reason = 'I found a better plugin';
			} elseif ( $_reason == '3' ) {
				$reason = 'The plugin broke my site';
			} elseif ( $_reason == '4' ) {
				$reason = 'The plugin suddenly stopped working';
			} elseif ( $_reason == '5' ) {
				$reason = 'I no longer need the plugin';
			} elseif ( $_reason == '6' ) {
				$reason = 'It\'s a temporary deactivation. I\'m just debugging an issue.';
			} elseif ( $_reason == '7' ) {
				$reason = 'Other';
			}
			$fields = array(
				'email'             => $email,
				'website'           => get_site_url(),
				'action'            => 'Deactivate',
				'reason'            => $reason,
				'reason_detail'     => $reason_detail,
				'blog_language'     => get_bloginfo( 'language' ),
				'wordpress_version' => get_bloginfo( 'version' ),
				'php_version'       => PHP_VERSION,
				'plugin_version'    => LOGINPRESS_VERSION,
				'plugin_name'       => 'LoginPress Free',
			);

			$response = wp_remote_post(
				LOGINPRESS_FEEDBACK_SERVER,
				array(
					'method'      => 'POST',
					'timeout'     => 5,
					'httpversion' => '1.0',
					'blocking'    => false,
					'headers'     => array(),
					'body'        => $fields,
				)
			);

			wp_die();
		}

		/**
		 * Opt-out
		 *
		 * @since 1.0.15
		 * @version 3.0.0
		 */
		function optout_yes() {

			check_ajax_referer( 'loginpress-optout-nonce', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'No cheating, huh!' );
			}

			update_option( '_loginpress_optin', 'no' );
			wp_die();
		}

		static function presets() {

			check_ajax_referer( 'loginpress-preset-nonce', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'No cheating, huh!' );
			}

			$selected_preset = get_option( 'customize_presets_settings', true );

			if ( $selected_preset == 'default1' ) {
				include_once LOGINPRESS_ROOT_PATH . 'css/themes/default-1.php';
				echo first_presets();
			} elseif ( $selected_preset == 'minimalist' ) {
				include_once LOGINPRESS_ROOT_PATH . 'css/themes/free-minimalist.php';
				echo free_minimalist_presets();
			} else {
				do_action( 'loginpress_add_pro_theme', $selected_preset );
			}
			wp_die();
		}

		/**
		 * video_url
		 *
		 * @since 1.1.22
		 * @version 3.0.0
		 * @return string attachment URL.
		 */
		static function video_url() {

			check_ajax_referer( 'loginpress-attachment-nonce', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'No cheating, huh!' );
			}

			echo wp_get_attachment_url( (int) $_POST['src'] );

			wp_die();
		}
		/**	
		 * YouTube Video URL.	
		 *	
		 * @return void	
		 */
		static function youtube_video_url() {
			check_ajax_referer( 'loginpress-attachment-nonce', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'No cheating, huh!' );
			}
			$video_id = sanitize_text_field( $_POST['src'] );
			$url      = 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg';
			$response = wp_remote_get( $url );
			if ( 200 === $response['response']['code'] ) {
				echo $video_id;
			} else {
				echo '0';
			}
			wp_die();
		}
	}

endif;
new LoginPress_AJAX();
