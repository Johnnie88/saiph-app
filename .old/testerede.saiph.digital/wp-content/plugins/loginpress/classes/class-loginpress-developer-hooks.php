<?php
/**
 * LoginPress has some hooks for developers.
 *
 * @since 1.1.7
 * @version 3.0.0
 */
if ( ! class_exists( 'LoginPress_Developer_Hooks' ) ) {

	/**
	 * Developer friendly hooks.
	 *
	 * @since 1.1.7
	 * @version 3.0.0
	 */
	class LoginPress_Developer_Hooks {

		/**
		 * Class Constructor.
		 */
		public function __construct() {
			$this->_hooks();
		}

		/**
		 * Hooks function for Remember me.
		 *
		 * @return void
		 */
		public function _hooks(){

			add_filter( 'loginpress_remember_me', array( $this, 'loginpress_remember_me_callback' ), 10, 1 );
		}

		/**
		 * Turn off the remember me option from WordPress login form.
		 *
		 * @param bool $activate Is activated ot not.
		 * @since 1.1.7
		 */
		public function loginpress_remember_me_callback( $activate ) {

			if ( ! $activate ) {
				return;
			}

			// Add the hook into the login_form
			add_action( 'login_form', array( $this, 'loginpress_login_form' ), 99 );

			// Reset any attempt to set the remember option
			add_action( 'login_head', array( $this, 'unset_remember_me_option' ), 99 );
		}

		/**
		 * unset remember me option.
		 *
		 * @return void
		 */
		function unset_remember_me_option() {

			// Remove the remember me post value
			if ( isset( $_POST['rememberme'] ) ) {
				unset( $_POST['rememberme'] );
			}
		}

		/**
		 * Login Form customization for remember me.
		 *
		 * @return void
		 */
		function loginpress_login_form() {

			ob_start( array( $this, 'remove_forgetmenot_class' ) );
		}

		/**
		 * Forget me not class removal.
		 *
		 * @param string $content The content being removed.
		 * @return string $content Update content.
		 */
		function remove_forgetmenot_class( $content ) {

			$content = preg_replace( '/<p class="forgetmenot">(.*)<\/p>/', '', $content);
			return $content;
		}

	}

}
$loginpress_developer_hooks = new LoginPress_Developer_Hooks();
?>
