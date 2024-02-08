<?php
/**
 * This is an Promotion class. Purpose of this class is to show a list of all the add-ons available to extend the functionality of LoginPress.
 *
 * @package LoginPress
 * @since 1.1.24
 * @version 1.4.5
 */

if ( ! class_exists( 'LoginPress_Promotion_tabs' ) ) :

	class LoginPress_Promotion_tabs {

		/**
		 * Class Constructor.
		 */
		function __construct() {

			$this->_hooks();
		}

		/**
		 * Promotion hooks.
		 *
		 * @return void
		 */
		public function _hooks(){
			add_filter( 'loginpress_settings_tab', array( $this, 'loginpress_promotion_tab' ), 10, 1 );
		}

		/**
		* Loginpress_login_redirects_tab Setting tab for Login Redirects.
		* @param  array $loginpress_tabs Tabs of free version.
		* @return array $loginpress_promotion_tab Promotion tabs.
		* @since 1.1.24
		* @version 1.4.5
		*/
		public function loginpress_promotion_tab( $loginpress_tabs ) {
			$_loginpress_promotion_tab = array(
				
				array(
					'id'        => 'loginpress_autologin',
					'title'     => __( 'Auto Login', 'loginpress' ),
					'sub-title'	=> __( 'No More Manual Login', 'loginpress' ),
					'desc'      => $this->_tabs_description( 'loginpress-auto-login' )
				),
				array(
					'id'        => 'loginpress_hidelogin',
					'title'     => __( 'Hide Login', 'loginpress' ),
					'sub-title'	=> __( 'Hide your login page', 'loginpress' ),
					'desc'      => $this->_tabs_description( 'loginpress-hide-login' )
				),
				array(
					'id'        => 'loginpress_limit_login_attempts',
					'title'     => __( 'Limit Login Attempts', 'loginpress' ),
					'sub-title'	=> __( 'Limits for login attempts', 'loginpress' ),
					'desc'      => $this->_tabs_description( 'loginpress-limit-login-attempts' )
				),
				array(
					'id'        => 'loginpress_login_redirects',
					'title'     => __( 'Login Redirects', 'loginpress' ),
					'sub-title'	=> __( 'Automatically redirects the login', 'loginpress' ),
					'desc'      => $this->_tabs_description( 'loginpress-login-redirects' )
				),
				array(
					'id'        => 'loginpress_social_logins',
					'title'     => __( 'Social Login', 'loginpress' ),
					'sub-title'	=> __( 'Third Party login access', 'loginpress' ),
					'desc'      => $this->_tabs_description( 'loginpress-social-login' )
				),
				array(
					'id'    => 'loginpress_premium',
					'title' => sprintf( __( 'Upgrade to Pro%1$s for More Features%2$s', 'loginpress' ), '<span>', '</span>' )
				),
			);
			$loginpress_promotion_tab = array_merge( $loginpress_tabs, $_loginpress_promotion_tab );
			return $loginpress_promotion_tab;
		}

		/**
		 * Return promoted Add-on description.
		 *
		 * @since 1.1.24
		 * @version 3.0.0
		 * @return string
		 */
		public function _tabs_description( $slug, $button = true ) {

			$desc = '';

			if ( 'loginpress-hide-login' === $slug ) {

				$desc .= '<p class="loginpress-addon-promotion-desc prom-content">' . esc_html__( 'This LoginPress add-on lets you change the login page URL to anything you want. It will give a hard time to spammers who keep hitting to your login page. This is helpful for Brute force attacks. One caution to use this add-on is you need to remember the custom login url after you change it. We have an option to email your custom login url so you remember it.', 'loginpress' ) . '</p>' . $this->_addon_video( 'How Hide Login Works', 'FSE2BH_biZg' ) . $this->upgrade_now( 'utm_source=loginpress-hide-login&utm_medium=addons-coming-soon&utm_campaign=pro-upgrade', $button );
			} else if ( 'loginpress-limit-login-attempts' === $slug ) {

				$desc .= '<p class="loginpress-addon-promotion-desc prom-content">' . esc_html__( 'Everybody needs a control of their Login page. This will help you to track your login attempts by each user. You can limit the login attempts for each user. Brute force attacks are the most common way to gain access to your website. This add-on acts as a sheild to these hacking attacks and gives you control to set the time between each login attempts.', 'loginpress' ) . '</p>' . $this->_addon_video( 'How Limit Login Login Attempts Works', '1-L14gHC8R0' ) . $this->upgrade_now( 'utm_source=loginpress-limit-login-attempts&utm_medium=addons-coming-soon&utm_campaign=pro-upgrade', $button );
			} else if ( 'loginpress-social-login' === $slug ) {

				$desc .= '<p class="loginpress-addon-promotion-desc prom-content">' . esc_html__( 'Social login from LoginPress is an add-on which provides facility your users to login and Register via Facebook, Google and Twitter. This add-on will eliminate the Spam and Bot registrations. This add-on will help your users to hassle free registrations/logins on your site.', 'loginpress' ) . '</p>' . $this->_addon_video( 'How Social Logins Works', 'EReYVYmdyeY' ) . $this->upgrade_now( 'utm_source=loginpress-social-login&utm_medium=addons-coming-soon&utm_campaign=pro-upgrade', $button );
			} else if ( 'loginpress-login-redirects' === $slug ) {

				$desc .= '<p class="loginpress-addon-promotion-desc prom-content">' . esc_html__( 'Redirect users based on their roles and specific usernames. This is helpful, If you have an editor and want to redirect him to his editor stats page. Restrict your subscribers, guests or even customers to certain pages instead of wp-admin. This add-on has a cool UX/UI to manage all the login redirects you have created on your site.', 'loginpress' ) . '</p>' . $this->_addon_video( 'How Login Redirects Works', 'EYqt8-iegeQ' ) . $this->upgrade_now( 'utm_source=loginpress-login-redirects&utm_medium=addons-coming-soon&utm_campaign=pro-upgrade', $button );
			} else if ( 'loginpress-auto-login' === $slug ) {

				$desc .= '<p class="loginpress-addon-promotion-desc prom-content">' . esc_html__( 'This LoginPress add-on lets you (Adminstrator) generates a unique URL for your certain users who you don\'t want to provide a password to login into your site. This Pro add-on gives you a list of all the users who you have given auto generated login links. You can disable someones access and delete certain users.', 'loginpress' ) . '</p>' . $this->_addon_video( 'How Auto Login Works', 'M2M3G2TB9Dk' ) . $this->upgrade_now( 'utm_source=loginpress-auto-login&utm_medium=addons-coming-soon&utm_campaign=pro-upgrade', $button );
			}
			return $desc;
		}

		/**
		 * Return video of the Add-on.
		 *
		 * @return string
		 */
		public function _addon_video( $title, $code ) {
			return '<hr /><div class="loginpress-addon-promotion-video">
				<h3><span class="dashicons dashicons-dashboard"></span>&nbsp;&nbsp;' . esc_html__( $title, 'loginpress' ) . '</h3>
				<div class="inside">
					<iframe width="800" height="400" src="https://www.youtube.com/embed/' . $code . '?showinfo=0" frameborder="0" allowfullscreen="" style=" max-width: 100%;" class="loginPress-feature-video"></iframe>
				</div>
			</div>';
		}

		/**
		* Return Upgrade Button of the promoted Add-on.
		*
		* @return string
		*/
		public function upgrade_now( $url, $button ) {

			if ( $button ) {
				return '<div class="loginpress-promotion-big-button"><a target="_blank" href="https://loginpress.pro/?' . $url . '" class="button-primary upgrade_now_link">' . esc_html__( 'UPGRADE NOW', 'loginpress' ) . '</a></div>';
			}
		}
	} // Enf of Class.

endif;
$loginpress_promotion_tabs = new LoginPress_Promotion_tabs;
