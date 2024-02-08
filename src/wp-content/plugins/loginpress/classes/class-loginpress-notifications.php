<?php
if ( ! defined( 'ABSPATH' ) ) {
 	// Exit if accessed directly.
  	exit;
}

/**
 * Handling all the Notification calls in LoginPress.
 * 
 * @since  1.1.14
 * @version 3.0.0
 */
if ( ! class_exists( 'LoginPress_Notification' ) ) :

  	class LoginPress_Notification {

		/**
		 * Class Constructor.
		 */
		public function __construct() {
			$this->_hooks();
		}

		/**
		 * Hook into actions and filters
		 * 
		 * @since  1.0.0
		 * @version 3.0.0
		 */
    	private function _hooks() {
      		add_action( 'admin_init',             array( $this, 'loginpress_review_notice' ) );

			// if ( defined( 'LOGINPRESS_PRO_VERSION' ) && LOGINPRESS_PRO_VERSION < '3.0' ) {
			// 	add_action( 'admin_notices' , array( $this, 'loginpress_pro30_install_notice' ) );
			// }
			
			// add_action( 'admin_init' ,            array( $this, 'loginpress_addon_notice' ) );
			// add_action( 'admin_init',             array( $this, 'loginpress_friday_sale_notice' ) );
			// add_action( 'admin_init',             array( $this, 'loginpress_appsumo_notice' ) );
		}

		/**
		 * Ask users to review our plugin on wordpress.org
		 * 
		 * @since 1.0.11
		 * @version 3.0.0
		 */
		public function loginpress_review_notice() {

			$this->loginpress_review_dismissal();
			$this->loginpress_review_pending();

			$activation_time 	= get_site_option( 'loginpress_active_time' );
			$review_dismissal	= get_site_option( 'loginpress_review_dismiss' );

			// Update the $review_dismissal value in 3.0.0
			if ( 'yes_v3' == $review_dismissal ) {
				return;
			}

			if ( ! $activation_time ) {
				$activation_time = time();
				add_site_option( 'loginpress_active_time', $activation_time );
			}

			// 1296000 = 15 Days in seconds.
			if ( ( time() - $activation_time > 1296000 ) && current_user_can( 'manage_options' ) ) {
				wp_enqueue_style( 'loginpress_review_style', plugins_url( '../css/style-review.css', __FILE__ ), array(), LOGINPRESS_VERSION );
				add_action( 'admin_notices' , array( $this, 'loginpress_review_notice_message' ) );
			}
		}

		/**
		 * Check and Dismiss review message.
		 * 
		 * @since 1.0.11
		 * @version 3.0.0
		 */
		private function loginpress_review_dismissal() {

			if ( ! is_admin() ||
				! current_user_can( 'manage_options' ) ||
				! isset( $_GET['_wpnonce'] ) ||
				! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'loginpress-review-nonce' ) ||
				! isset( $_GET['loginpress_review_dismiss'] ) ) {

				return;
			}

			// Update the $review_dismissal value in 3.0.0
			update_site_option( 'loginpress_review_dismiss', 'yes_v3' );
		}

		/**
		 * Set time to current so review notice will popup after 14 days
		 * 
		 * @since 1.0.11
		 * @version 3.0.0
		 */
		function loginpress_review_pending() {

			if ( ! is_admin() ||
				! current_user_can( 'manage_options' ) ||
				! isset( $_GET['_wpnonce'] ) ||
				! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'loginpress-review-nonce' ) ||
				! isset( $_GET['loginpress_review_later'] ) ) :

				return;
			endif;

			// Reset Time to current time.
			update_site_option( 'loginpress_active_time', time() );
		}

		/**
		 * Review notice message
		 * 
		 * @since 1.0.11
		 * @version 3.0.0
		 */
		public function loginpress_review_notice_message() {

			$scheme      = ( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) ? '&' : '?';
			// Update the $review_dismissal value in 3.0.0
			$url         = $_SERVER['REQUEST_URI'] . $scheme . 'loginpress_review_dismiss=yes_v3';
			$dismiss_url = wp_nonce_url( $url, 'loginpress-review-nonce' );

			$_later_link = $_SERVER['REQUEST_URI'] . $scheme . 'loginpress_review_later=yes';
			$later_url   = wp_nonce_url( $_later_link, 'loginpress-review-nonce' ); ?>

			<div class="loginpress-review-notice">
				<div class="loginpress-review-thumbnail">
					<img src="<?php echo esc_url( plugins_url( '../img/loginpress.png', __FILE__ ) ); ?>" alt="notification-logo">
				</div>
				<div class="loginpress-review-text">
					<h3><?php esc_html_e( 'Leave A Review?', 'loginpress' ); ?></h3>
					<p><?php esc_html_e( 'We hope you\'ve enjoyed using LoginPress! Would you consider leaving us a review on WordPress.org?', 'loginpress' ); ?></p>
					<ul class="loginpress-review-ul">
						<li><a href="https://wordpress.org/support/view/plugin-reviews/loginpress?rate=5#postform" target="_blank"><span class="dashicons dashicons-external"></span><?php esc_html_e( 'Sure! I\'d love to!', 'loginpress' ); ?></a></li>
						<li><a href="<?php echo esc_url( $dismiss_url ); ?>"><span class="dashicons dashicons-smiley"></span><?php esc_html_e( 'I\'ve already left a review', 'loginpress' ); ?></a></li>
						<li><a href="<?php echo esc_url( $later_url ); ?>"><span class="dashicons dashicons-calendar-alt"></span><?php esc_html_e( 'Maybe Later', 'loginpress' ); ?></a></li>
						<li><a href="<?php echo esc_url( $dismiss_url ); ?>"><span class="dashicons dashicons-dismiss"></span><?php esc_html_e( 'Never show again', 'loginpress' ); ?></a></li>
					</ul>
				</div>
			</div>
			<?php
		}

		/**
		 * Review notice message
		 * 
		 * @since 1.1.3
		 * @version 3.0.0
		 */
		public function loginpress_addon_notice_text() {

			$scheme      = ( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) ? '&' : '?';
			$url         = $_SERVER['REQUEST_URI'] . $scheme . 'loginpress_addon_dismiss_1=yes';
			$dismiss_url = wp_nonce_url( $url, 'loginpress-addon-nonce' );
			wp_enqueue_style( 'loginpress_review_style', plugins_url( '../css/style-review.css', __FILE__ ), array(), LOGINPRESS_VERSION );
			?>
			<div class="loginpress-alert-notice">
				<a href="<?php echo $dismiss_url ?>" class="notice-dismiss" ><span class="screen-reader-text"></span></a>
				<a href="https://wpbrigade.com/wordpress/plugins/loginpress/addons/?utm_source=loginpress-lite&utm_medium=addons-notice-banner&utm_campaign=pro-upgrade" class="loginpress-addon-notice-link" target="_blank">
					<div class="loginpress-alert-thumbnail">
						<img src="<?php echo plugins_url( '../img/notification_logo.svg', __FILE__ ) ?>" alt="Notification Logo">
					</div>
					<div class="loginpress-alert-text">
						<div class="loginpress-text-inner">
							<h3><?php _e( 'Upgrade LoginPress Addons!', 'loginpress' ) ?></h3>
							<p><?php _e( 'Extend LoginPress with these add-ons and supercharge your login pages.', 'loginpress' ) ?></p>
						</div>
					</div>
				</a>
				<div class="loginpress-alert-button-section">
					<a href="https://wpbrigade.com/wordpress/plugins/loginpress/addons/?utm_source=loginpress-lite&utm_medium=addons-notice-more&utm_campaign=pro-upgrade" class="loginpress-alert-button" target="_blank"><?php _e( 'Learn More', 'loginpress' ) ?></a>
				</div>
			</div>
			<?php
		}
    
		/**
		 * LoginPress Pro 3.0 notice.
		 * 
		 * @since 3.0.0
		 */
		public function loginpress_pro30_install_notice() {
			printf( '<div class="%1$s"><p><a href="%2$s">%3$s</a> %4$s</p></div>',
				esc_attr( 'notice notice-error' ),
				get_site_url() . '/wp-admin/update.php?action=upgrade-plugin&amp;plugin=loginpress-pro/loginpress-pro.php&amp;_wpnonce=' . wp_create_nonce( "upgrade-plugin_loginpress-pro/loginpress-pro.php" ),
				esc_html__( 'Upgrade', 'loginpress' ),
				esc_html__( 'to LoginPress Pro 3.0', 'loginpress' )
			);
		}

		/**
		 * Check and Dismiss addon message. 
		 * 
		 * @since 1.1.3
		 * @version 3.0.0
		 */
		private function loginpress_addon_dismissal() {

			if ( ! is_admin() ||
				! current_user_can( 'manage_options' ) ||
				! isset( $_GET['_wpnonce'] ) ||
				! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'loginpress-addon-nonce' ) ||
				! isset( $_GET['loginpress_addon_dismiss_1'] ) ) :

				return;
			endif;

			add_site_option( 'loginpress_addon_dismiss_1', 'yes' );
		}

		/**
		 * Ask users to review our plugin on wordpress.org
		 * 
		 * @since 1.1.3
		 * @version 3.0.0
		 */
		public function loginpress_addon_notice() {

		$this->loginpress_addon_dismissal();

		$activation_time 	= get_site_option( 'loginpress_addon_active_time' );
		$addon_dismissal	= get_site_option( 'loginpress_addon_dismiss_1' );

		if ( 'yes' == $addon_dismissal ) return;

		if ( ! $activation_time ) :

			$activation_time = time();
			add_site_option( 'loginpress_addon_active_time', $activation_time );
		endif;

		// 432000 = 5 Days in seconds.
		// if ( time() - $activation_time > 432000 ) :

			add_action( 'admin_notices' , array( $this, 'loginpress_addon_notice_text' ) );
		// endif;

		}

		/**
		 * Ask users to review our plugin on wordpress.org
		 * 
		 * @since 1.1.3
		 * @version 3.0.0
		 */
		public function loginpress_friday_sale_notice() {

			$this->loginpress_deals_notice_dismiss( 'loginpress-friday-sale-nonce', 'loginpress_friday_21_sale_dismiss' );

			$activation_time 	= get_site_option( 'loginpress_friday_sale_active_time' );
			$addon_dismissal	= get_site_option( 'loginpress_friday_21_sale_dismiss' );

			if ( 'yes' == $addon_dismissal ) return;

			if ( ! $activation_time ) :

				$activation_time = time();
				add_site_option( 'loginpress_friday_sale_active_time', $activation_time );
			endif;

			if ( ! has_action( 'loginpress_pro_add_template' ) ) :
				// add_action( 'admin_notices' , array( $this, 'loginpress_friday_sale_notice_text' ) ); // turn off on update v1.5.10
				// add_action( 'admin_notices', array( $this, 'new_loginpress_friday_sale_notice_text' ) ); // turn off on update 1.1.19
			endif;
		}

		/**
		 * Ask users to review our plugin on wordpress.org
		 * 
		 * @since 1.2.1
		 * @version 3.0.0
		 */
		public function loginpress_appsumo_notice() {

			$this->loginpress_deals_notice_dismiss( 'loginpress-appsumo-nonce', 'loginpress_appsumo_dismiss' );

			$activation_time 	= get_site_option( 'loginpress_appsumo_active_time' );
			$addon_dismissal	= get_site_option( 'loginpress_appsumo_dismiss' );

			if ( 'yes' == $addon_dismissal ) return;

			if ( ! $activation_time ) :

				$activation_time = time();
				add_site_option( 'loginpress_appsumo_active_time', $activation_time );
			endif;

			if ( ! has_action( 'loginpress_pro_add_template' ) ) :
				add_action( 'admin_notices', array( $this, 'loginpress_appsumo_notice_text' ) );
			endif;
		}

		/**
		 * Review notice message
		 * 
		 * @since 1.1.14
		 * @version 3.0.0
		 */
		public function loginpress_friday_sale_notice_text() {

			$scheme      = ( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) ? '&' : '?';
			$url         = $_SERVER['REQUEST_URI'] . $scheme . 'loginpress_friday_21_sale_dismiss=yes';
			$dismiss_url = wp_nonce_url( $url, 'loginpress-friday-sale-nonce' );

			wp_enqueue_style( 'loginpress_review_style', plugins_url( '../css/style-review.css', __FILE__ ), array(), LOGINPRESS_VERSION );
			?>
			<div class="loginpress-alert-notice black_friday_notice">
				<a href="<?php echo $dismiss_url ?>" class="notice-dismiss" ><span class="screen-reader-text"></span></a>
				<a href="https://loginpress.pro/black-friday/?utm_source=loginpress-lite&utm_medium=freepluginbanner&utm_campaign=blackfriday2021" target="_blank">
					<div class="loginpress-alert-thumbnail">
						<img src="<?php echo esc_url( plugins_url( '../img/notification_logo.svg', __FILE__ ) ); ?>" alt="Notification Logo">
					</div>
					<div class="loginpress-alert-text black_friday">
						<img src="<?php echo esc_url( plugins_url( '../img/black-friday.png', __FILE__ ) ); ?>" alt="Black Friday 2021">
						<div class="loginpress-alert-button-section black_friday_sale_btn">
							<a href="https://loginpress.pro/black-friday/?utm_source=loginpress-lite&utm_medium=freepluginbanner-button&utm_campaign=blackfriday2021" class="loginpress-alert-button" target="_blank"><?php echo esc_html__( 'FLAT 50% OFF', 'loginpress' ); ?></a>
						</div>
					</div>
				</a>
			</div>
			<?php
		}

		/**
		 * HTML for Black Friday Deal Notice
		 * 
		 * @since 1.1.15
		 * @version 3.0.0
		 */
    	function new_loginpress_friday_sale_notice_text() {

			$scheme      = ( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) ? '&' : '?';
			$url         = $_SERVER['REQUEST_URI'] . $scheme . 'loginpress_friday_21_sale_dismiss=yes';
			$dismiss_url = wp_nonce_url( $url, 'loginpress-friday-sale-nonce' );

			if ( current_user_can( 'install_plugins' ) && ! has_action( 'loginpress_pro_add_template' ) ) {

        		wp_enqueue_style( 'loginpress_review_style', plugins_url( '../css/style-review.css', __FILE__ ), array(), LOGINPRESS_VERSION );

  				$message = '<p> ';
  				$message .= sprintf (__( '<strong>Biggest Winter Deal</strong> in the WordPress Universe! Get <strong>LoginPress Pro and all Premium Add-ons</strong> with <strong>20%% OFF</strong> [Limited Availability].<a href="https://loginpress.pro/pricing/?utm_source=loginpress-lite&utm_medium=freepluginbanner-button&utm_campaign=early20" target="_blank" style="text-decoration: none;"><span class="dashicons dashicons-smiley" style="margin-left: 10px;"></span> Grab The Deal</a>
  					<a href="%1$s" style="text-decoration: none; margin-left: 10px;"><span class="dashicons dashicons-dismiss"></span> I\'m good with free version</a>' ), $dismiss_url );
  				$message .=  "</p>";
  				$class = 'loginpress-notice-success';
  				$this->loginpress_admin_notice( $message, $class );
			}
		}

		/**
		 * HTML for AppSumo Deal Notice
		 * 
		 * @since 1.2.1
		 * @version 3.0.0
		 */
      	function loginpress_appsumo_notice_text() {

			$scheme      = ( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) ? '&' : '?';
			$url         = $_SERVER['REQUEST_URI'] . $scheme . 'loginpress_appsumo_dismiss=yes';
			$dismiss_url = wp_nonce_url( $url, 'loginpress-appsumo-nonce' );

  			if ( current_user_can( 'install_plugins' ) && ! has_action( 'loginpress_pro_add_template' ) ) {

            	wp_enqueue_style( 'loginpress_review_style', plugins_url( '../css/style-review.css', __FILE__ ), array(), LOGINPRESS_VERSION );

				$message = '<p>';
				$message .= sprintf (__( '<strong>Biggest Summer Deal</strong> in the WordPress Universe! Get <strong>LoginPress Pro and all Premium Add-ons</strong> with <strong>20%% OFF</strong> [Limited Availability].<a href="https://loginpress.pro/pricing/?utm_source=loginpress-lite&utm_medium=freepluginbanner-button&utm_campaign=early20" target="_blank" style="text-decoration: none;"><span class="dashicons dashicons-smiley" style="margin-left: 10px;"></span> Grab The Deal</a>
					<a href="%1$s" style="text-decoration: none; margin-left: 10px;"><span class="dashicons dashicons-dismiss"></span> I\'m good with free version</a>' ), $dismiss_url );
				$message .=  "</p>";
				$class = 'loginpress-notice-success';
				$this->loginpress_admin_notice( $message, $class );
  			}
  		}

		/**
		 * Add custom admin notice
		 * @param  string $message Custom Message
		 * @param  string $class   loginpress-notice-success,loginpress-notice-danger
		 * 
		 * @since 1.1.15
		 * @version 3.0.0
		 */
     	function loginpress_admin_notice( $message, $class = 'loginpress-notice-success' ) {
    		echo '<div class="loginpress-notification '. $class .'">
					<a class="" href="#" aria-label="Dismiss the welcome panel"></a>
					<div class="loginpress-notice-logo">
						<img src="' . plugins_url( '../img/loginpress.png', __FILE__ ) . '" alt="notification-logo">
					</div>
					<div class="loginpress-notice-description">
						<p>' . $message .'</p>
					</div>
				</div>';
     	}

		/**
		 * Check and Dismiss addon message.
		 * 
		 * @since 1.1.3
		 * @version 3.0.0
		 */
		private function loginpress_deals_notice_dismiss( $nonce, $option ) {
			//delete_site_option( $option );
			if ( ! is_admin() ||
				! current_user_can( 'manage_options' ) ||
				! isset( $_GET['_wpnonce'] ) ||
				! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), $nonce ) ||
				! isset( $_GET[$option] ) ) :

				return;
			endif;

			add_site_option( $option, 'yes' );
		}
  	}
endif;
new LoginPress_Notification();
