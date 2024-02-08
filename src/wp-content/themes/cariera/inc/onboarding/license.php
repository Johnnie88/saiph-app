<?php

namespace Cariera\Onboarding;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/onboarding/cariera-base.php';


class License {

	public $plugin_file = __FILE__;
	public $response_obj;
	public $license_message;
	public $show_message = false;
	public $slug         = 'cariera';

	private $status;
	private $license_key_name = 'Cariera_lic_Key';

	/**
	 * Constructor
	 *
	 * @since   1.5.1
	 * @version 1.5.2
	 */
	public function __construct() {
		$license_key = get_option( $this->license_key_name, '' );

		if ( empty( $license_key ) ) {
			$license_key = get_option( $this->license_key_name, '' );
			if ( ! empty( $license_key ) ) {
				update_option( $this->license_key_name, $license_key ) || add_option( $this->license_key_name, $license_key );
				update_option( $this->license_key_name, '' );

			}
		}

		$license_email = get_option( 'Cariera_lic_email', '' );
		$template_dir  = get_stylesheet_directory();

		\Cariera_Base::add_on_delete(
			function() {
				$this->status = false;
				delete_option( $this->license_key_name );
				delete_option( 'Cariera_lic_email' );
				$this->deactivate_core_plugin();
			}
		);

		// If license is active.
		if ( \Cariera_Base::check_wp_plugin( $license_key, $license_email, $this->license_message, $this->response_obj, $template_dir . '/style.css' ) ) {
			$this->status = true;

			add_action( 'cariera_onboarding_license', [ $this, 'activated' ] );
			add_action( 'admin_post_Cariera_el_deactivate_license', [ $this, 'action_deactivate_license' ] );
			add_action( 'cariera_onboarding_license_sidebar', [ $this, 'sidebar_support_expired' ] );
			add_action( 'admin_notices', [ $this, 'support_expired_notice' ] );

			// If license is not active.
		} else {
			$this->status = false;

			if ( ! empty( $license_key ) && ! empty( $this->license_message ) ) {
				$this->show_message = true;
			}

			// License form action.
			add_action( 'cariera_onboarding_license', [ $this, 'license_form' ] );
			update_option( $this->license_key_name, '' ) || add_option( $this->license_key_name, '' );
			add_action( 'admin_post_Cariera_el_activate_license', [ $this, 'action_activate_license' ] );
			add_action( 'admin_notices', [ $this, 'license_activation_notice' ] );
		}
	}

	/**
	 * License activation action
	 *
	 * @since 1.5.1
	 */
	public function action_activate_license() {
		check_admin_referer( 'el-license' );

		$license_key   = ! empty( $_POST['el_license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['el_license_key'] ) ) : '';
		$license_email = ! empty( $_POST['el_license_email'] ) ? sanitize_email( wp_unslash( $_POST['el_license_email'] ) ) : '';

		update_option( $this->license_key_name, $license_key ) || add_option( $this->license_key_name, $license_key );
		update_option( 'Cariera_lic_email', $license_email ) || add_option( 'Cariera_lic_email', $license_email );
		update_option( '_site_transient_update_themes', '' );

		wp_safe_redirect( admin_url( 'admin.php?page=cariera_theme' ) );
	}

	/**
	 * License deactivation action
	 *
	 * @since 1.5.1
	 */
	public function action_deactivate_license() {
		check_admin_referer( 'el-license' );
		$message = '';

		if ( \Cariera_Base::remove_license_key( __FILE__, $message ) ) {
			$this->status = false;

			update_option( $this->license_key_name, '' ) || add_option( $this->license_key_name, '' );
			update_option( '_site_transient_update_themes', '' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=cariera_theme' ) );
	}

	/**
	 * License activation form
	 *
	 * @since 1.5.1
	 */
	public function license_form() { ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="Cariera_el_activate_license"/>

			<?php
			if ( ! empty( $this->show_message ) && ! empty( $this->license_message ) ) {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html( $this->license_message ); ?></p>
				</div>
			<?php } ?>

			<div class="el-license-field">
				<label for="el_license_key"><?php esc_html_e( 'License code', 'cariera' ); ?></label>
				<span class="description"><a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank"><?php esc_html_e( 'How to get purchase code?', 'cariera' ); ?></a></span>

				<input type="text" class="regular-text code" name="el_license_key" size="50" placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" required="required" autocomplete="off">
			</div>

			<div class="el-license-field">
				<label for="el_license_key"><?php esc_html_e( 'Your Email Address', 'cariera' ); ?></label>

				<?php $purchaseEmail = get_option( 'Cariera_lic_email', get_bloginfo( 'admin_email' ) ); ?>
				<input type="text" class="regular-text code" name="el_license_email" size="50" value="<?php echo esc_attr( $purchaseEmail ); ?>" required="required">
			</div>

			<div class="el-license-active-btn">
				<?php wp_nonce_field( 'el-license' ); ?>
				<?php submit_button( 'Activate License' ); ?>
			</div>
		</form>
		<?php
	}

	/**
	 * License deactivation form
	 *
	 * @since 1.5.1
	 */
	public function activated() {
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="Cariera_el_deactivate_license"/>

			<h3 class="el-license-title"><?php esc_html_e( 'License Info:', 'cariera' ); ?><span class="license"><?php echo esc_attr( substr( $this->response_obj->license_key, 0, 9 ) . 'XXXXXXXX-XXXXXXXX' . substr( $this->response_obj->license_key, -9 ) ); ?></span></h3>
			<hr>

			<h3><?php esc_html_e( 'Thank you for choosing Cariera!', 'cariera' ); ?></h3>            
			<p><?php esc_html_e( 'You can fully use the theme on this domain now. If you decide to move your site to a different domain make sure to first deactivate the license by clicking the button below then activate the license on your new domain.', 'cariera' ); ?></p>

			<div class="el-license-active-btn">
				<?php wp_nonce_field( 'el-license' ); ?>
				<?php submit_button( 'Deactivate License' ); ?>
			</div>
		</form>
		<?php
	}

	/**
	 * License info
	 *
	 * @since 1.5.1
	 */
	public function sidebar_support_expired() {
		$today = date( 'Y-m-d H:i:s' );

		// Support is still valid.
		if ( $this->response_obj->support_end > $today ) {
			?>
			<div class="license-support valid">
				<h3 class="title"><?php esc_html_e( 'Support is valid', 'cariera' ); ?></h3>
				<p><?php esc_html_e( 'Your support ends on:', 'cariera' ); ?></p>
				<span class="support-date"><?php echo esc_html( $this->response_obj->support_end ); ?></span>
			</div>
			<?php
			// Support has expired.
		} else {
			?>
			<div class="license-support expired">
				<h3 class="title"><?php esc_html_e( 'Support has expired!', 'cariera' ); ?></h3>
				<p><?php esc_html_e( 'Your support expired on:', 'cariera' ); ?></p>
				<span class="support-date"><?php echo esc_html( $this->response_obj->support_end ); ?></span>
				<a target="_blank" class="renew-btn" href="<?php echo esc_url( $this->response_obj->support_renew_link ); ?>"><?php esc_html_e( 'Renew Support', 'cariera' ); ?></a>
			</div>
			<?php
		}
	}

	/**
	 * License activation required admin notice
	 *
	 * @since 1.5.1
	 */
	public function license_activation_notice() {
		if ( $this->status == true ) {
			return;
		}
		?>

		<div class="notice notice-info">
			<p><?php esc_html_e( 'Cariera has not been activated! Make sure to activate your license to be able to use all core functionalities.', 'cariera' ); ?></p>

			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=cariera_theme' ) ); ?>" class="wp-core-ui button"><?php esc_html_e( 'Activate Cariera License', 'cariera' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * Support has expired admin notice
	 *
	 * @since 1.5.1
	 */
	public function support_expired_notice() {
		$today = date( 'Y-m-d H:i:s' );

		// Return if license is active and support is still valid.
		if ( $this->status == true && $this->response_obj->support_end > $today ) {
			return;
		}
		?>

		<div class="notice notice-error">
			<p><?php esc_html_e( 'Your support for Cariera has expired.', 'cariera' ); ?> <a href="<?php echo esc_url( $this->response_obj->support_renew_link ); ?>" target="_blank"><?php esc_html_e( 'Renew Support', 'cariera' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * Get activation status of the theme
	 *
	 * @since 1.5.1
	 */
	public function activation_status() {
		return $this->status;
	}

	/**
	 * Deactivate Core plugin when deactivate theme license
	 *
	 * @since 1.6.3
	 */
	public function deactivate_core_plugin() {
		if ( defined( 'CARIERA_PLUGIN_BASENAME' ) && is_plugin_active( CARIERA_PLUGIN_BASENAME ) ) {
			deactivate_plugins( CARIERA_PLUGIN_BASENAME );
		}
	}
}
