<?php
/**
 * Meta class about Add-Ons.
 *
 * @package LoginPress
 * @since 3.0.5
 */

if ( ! class_exists( 'LoginPress_Addons_Meta' ) ) :

	class LoginPress_Addons_Meta {

		/**
		 * Class Constructor.
		 */
		function __construct() {
			$this->addons_options_array();
		}

		/**
		 * The addons options array.
		 *
		 * @since 3.0.5
		 */
		public static function addons_options_array() {

			$addons_array = array(
				'login-logout-menu'    => array(
					'title'      => __( 'Login Logout Menu', 'loginpress' ),
					'short_desc' => __( 'Login Logout Menu Section', 'loginpress' ),
					'slug'       => 'login-logout-menu',
					'is_free'    => true,
					'is_active'  => false,
				),
				'auto-login'           => array(
					'title'      => __( 'Auto Login', 'loginpress' ),
					'short_desc' => __( 'No More Manual Login', 'loginpress' ),
					'slug'       => 'auto-login',
					'is_free'    => false,
					'is_active'  => false,
				),
				'login-redirects'      => array(
					'title'      => __( 'Login Redirects', 'loginpress' ),
					'short_desc' => __( 'Automatically redirects the login', 'loginpress' ),
					'slug'       => 'login-redirects',
					'is_free'    => false,
					'is_active'  => false,
				),
				'limit-login-attempts' => array(
					'title'      => __( 'Limit Login Attempts', 'loginpress' ),
					'short_desc' => __( 'Limits for login attempts', 'loginpress' ),
					'slug'       => 'limit-login-attempts',
					'is_free'    => false,
					'is_active'  => false,
				),
				'hide-login'           => array(
					'title'      => __( 'Hide Login', 'loginpress' ),
					'short_desc' => __( 'Hide your login page', 'loginpress' ),
					'slug'       => 'hide-login',
					'is_free'    => false,
					'is_active'  => false,
				),
				'social-login'         => array(
					'title'      => __( 'Social Login', 'loginpress' ),
					'short_desc' => __( 'Third Party login access', 'loginpress' ),
					'slug'       => 'social-login',
					'is_free'    => false,
					'is_active'  => false,
				),
				'login-widget'         => array(
					'title'      => __( 'Login Widget', 'loginpress' ),
					'short_desc' => __( 'Creates a login widget', 'loginpress' ),
					'slug'       => 'login-widget',
					'is_free'    => false,
					'is_active'  => false,
				),
				// 'protect-content'      => array(
				// 	'title'      => __( 'Protect Content', 'loginpress' ),
				// 	'short_desc' => __( 'Protected Contents', 'loginpress' ),
				// 	'slug'       => 'protect-content',
				// 	'is_free'    => false,
				// 	'is_active'  => false,
				// ),
				// 'custom-fields'        => array(
				// 	'title'      => __( 'Custom Registration Fields', 'loginpress' ),
				// 	'short_desc' => __( 'Custom Registration Fields', 'loginpress' ),
				// 	'slug'       => 'custom-fields',
				// 	'is_free'    => false,
				// 	'is_active'  => false,
				// ),
			);

			if ( ! get_option( 'loginpress_pro_addons' ) ) {
				add_option( 'loginpress_pro_addons', $addons_array );
			}

			if ( sizeof( $addons_array ) != sizeof( get_option( 'loginpress_pro_addons' ) ) ) {
				update_option( 'loginpress_pro_addons', $addons_array );
			}
		}

		/**
		 * The addon details.
		 *
		 * @since 3.0.5
		 * @return void
		 */
		public static function addons_details() {

			$addons_details_array = array(
				'login-logout-menu'    => array(
					'title'   => 'Login Logout Menu',
					'excerpt' => __( 'Login Logout Menu is a handy plugin which allows you to add login, logout, register and profile menu items in your selected menu.', 'loginpress' ),
				),
				'login-redirects'      => array(
					'title'   => 'Login Redirects',
					'excerpt' => __( 'Redirects users based on their roles. This is helpful, If you have an editor and want to redirect him to his editor stats page. Restrict your subscribers, guests or even customers to certain pages instead of wp-admin. This add-on has a cool UX/UI to manage all the login redirects you have created on your site.', 'loginpress' ),
				),
				'social-login'         => array(
					'title'   => 'Social Login',
					'excerpt' => __( 'Social login from LoginPress is an add-on which provides facility your users to log in and Register via Facebook, Google, and Twitter. This add-on will eliminate the Spam and Bot registrations. This add-on will help your users to hassle-free registrations/logins on your site.', 'loginpress' ),
				),
				'login-widget'         => array(
					'title'   => 'Login Widget',
					'excerpt' => __( 'This LoginPress add-on is a widget you can use into your blog sidebar. It uses an Ajax way to login via the sidebar. You may need to know HTML/CSS to give it style according to your site even we have styled it in general.', 'loginpress' ),
				),
				'limit-login-attempts' => array(
					'title'   => 'Limit Login Attempts',
					'excerpt' => __( 'Everybody needs a control of their Login page. This will help you to track your login attempts by each user. You can limit the login attempts for each user. Brute force attacks are the most common way to gain access to your website. This add-on acts as a sheild to these hacking attacks and gives you control to set the time between each login attempts.', 'loginpress' ),
				),
				'auto-login'           => array(
					'title'   => 'Auto Login',
					'excerpt' => __( 'This LoginPress add-on lets you (Administrator) generates a unique URL for your certain users who you dont want to provide a password to login to your site. This Pro add-on gives you a list of all the users who you have given auto-generated login links. You can disable someones access and delete certain users.', 'loginpress' ),
				),
				'hide-login'           => array(
					'title'   => 'Hide Login',
					'excerpt' => __( 'This LoginPress add-on lets you change the login page URL to anything you want. It will give a hard time to spammers who keep hitting to your login page. This is helpful for Brute force attacks. One caution to use this add-on is you need to remember the custom login url after you change it. We have an option to email your custom login url so you remember it.', 'loginpress' ),
				),
				// 'protect-content'      => array(
				// 	'title'   => 'Protect Content',
				// 	'excerpt' => __( 'Protect Content add-on allows you to protect your page/post/CPT specific content.', 'loginpress' ),
				// ),
				// 'custom-fields'        => array(
				// 	'title'   => 'Custom Registration Fields',
				// 	'excerpt' => __( 'This addon lets you create custom fields like text,radio button, checkboxes even an image.', 'loginpress' ),
				// ),
			);
			return $addons_details_array;
		}
	} // Enf of Class.

endif;
new LoginPress_Addons_Meta();
