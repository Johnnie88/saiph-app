<?php
/**
 * Enable Custom Password for Register User.
 * 
 * @package LoginPress
 * @since 1.0.22
 * @version 3.0.0
 */
if ( ! class_exists( 'LoginPress_Custom_Password' ) ) :

	/**
	 * LoginPress Custom Passwords class.
	 * 
	 * @since 1.0.22
	 * @version 1.4.5
	 */
	class LoginPress_Custom_Password {

		/**
		 * Class Constructor.
		 */
		public function __construct() {
			$this->loginpress_key = get_option( 'loginpress_customization' );
			$this->_hooks();
		}

		public function _hooks() {

			add_action( 'register_form',                  array( $this, 'loginpress_reg_password_fields' ) );
			add_filter( 'random_password',                array( $this, 'loginpress_set_password' ) );
			add_action( 'register_new_user',              array( $this, 'loginpress_default_password_nag' ) );
			add_filter( 'registration_errors',            array( $this, 'loginpress_reg_pass_errors' ), 10, 3 );
			add_filter( 'wp_new_user_notification_email', array( $this, 'loginpress_new_user_email_notification' ) );
		}

		/**
		 * Custom Password Fields on Registration Form.
		 *
		 * @since   1.0.22
		 * @access  public
		 * @return  string html.
		 */
		public function loginpress_reg_password_fields() {
			?>
			<p class="loginpress-reg-pass-wrap">
				<label for="loginpress-reg-pass"><?php esc_html_e( 'Password', 'loginpress' ); ?></label>
			</p>

			<div class="loginpress-reg-pass-wrap-1 password-field">
				<input autocomplete="off" name="loginpress-reg-pass" id="loginpress-reg-pass" class="input custom-password-input" size="20" value="" type="password" />
				<span class="show-password-toggle dashicons dashicons-visibility"></span>
			</div>

			<p class="loginpress-reg-pass-2-wrap">
				<label for="loginpress-reg-pass-2"><?php esc_html_e( 'Confirm Password', 'loginpress' ); ?></label>
			</p>

			<div class="loginpress-reg-pass-wrap-2 password-field">
				<input autocomplete="off" name="loginpress-reg-pass-2" id="loginpress-reg-pass-2" class="input custom-password-input" size="20" value="" type="password" />
				<span class="show-password-toggle dashicons dashicons-visibility"></span>
			</div>
			<?php
		}

		/**
		 * Handles password field errors for registration form.
		 * 
		 * @param Object $errors WP_Error
		 * @param Object $sanitized_user_login user login.
		 * @param Object $user_email user email.
		 * @since 1.0.22
		 * @version 3.0.0
		 * @return WP_Error object.
		 */
		public function loginpress_reg_pass_errors( $errors, $sanitized_user_login, $user_email ) {

			// Ensure passwords aren't empty.
			if ( empty( $_POST['loginpress-reg-pass'] ) || empty( $_POST['loginpress-reg-pass-2'] ) ) {
				$errors->add( 'empty_password', sprintf( __( '%1$sError:%2$s Please enter your password twice.', 'loginpress' ), '<strong>', '</strong>' ) );
				
			// Ensure passwords are matched.
			} elseif ( $_POST['loginpress-reg-pass'] != $_POST['loginpress-reg-pass-2'] ) {

				//if passwords are not matched then set default passwords don't match message or show customized message
				$password_mismatch = array_key_exists( 'password_mismatch', $this->loginpress_key ) && ! empty( $this->loginpress_key['password_mismatch'] ) ? $this->loginpress_key['password_mismatch'] : sprintf( __( '%1$sError:%2$s Passwords don\'t match.', 'loginpress' ), '<strong>', '</strong>' );

				//Show error message of passwords don't match message
				$errors->add( 'password_mismatch', __( $password_mismatch, 'loginpress' ) );

				// Password Set? assign password to a user_pass
			} else {
				$_POST['user_pass'] = sanitize_text_field( $_POST['loginpress-reg-pass'] );
			}

			return $errors;
		}

		/**
		 * Let's set the user password.
		 * 
		 * @param string $password Auto-generated password passed in from filter.
		 * @since 1.0.22
		 * @version 3.0.0
		 * @return string Password Choose by User.
		 */
		public function loginpress_set_password( $password ) {

			// Make sure password field isn't empty.
			if ( isset( $_POST['user_pass'] ) && ! empty( $_POST['user_pass'] ) ) {
				$password = $_POST['user_pass'];
			}
			return esc_html( $password );
		}

		/**
		 * Sets the value of default password nag.
		 * 
		 * @param int $user_id.
		 * @since 1.0.22
		 * @version 3.0.0
		 */
		public function loginpress_default_password_nag( $user_id ) {

			// False => User not using WordPress default password.
			update_user_meta( $user_id, 'default_password_nag', false );
		}

		/**
		 * Filter the new user email notification.
		 *
		 * @param array $email The new user email notification parameters.
		 * @since 1.4.0
		 * @version 3.0.0
		 * @return array The new user email notification parameters.
		 */
		function loginpress_new_user_email_notification( $email ) {

			$email['message'] .= "\r\n" . __( 'If you have already set your own password, you may disregard this email and use the password you have already set.', 'loginpress' );

			return $email;
		}

	}

endif;
