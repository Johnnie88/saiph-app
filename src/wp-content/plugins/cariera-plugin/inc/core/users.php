<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Users {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 *
	 * @since   1.5.2
	 * @version 1.7.1
	 */
	public function __construct() {

		// Register Login & Register script.
		add_action( 'wp_enqueue_scripts', [ $this, 'login_register_script' ] );

		// Shortcodes.
		add_shortcode( 'cariera_login_form', [ $this, 'login_form' ] );
		add_shortcode( 'cariera_registration_form', [ $this, 'registration_form' ] );
		add_shortcode( 'cariera_forgetpass_form', [ $this, 'forgetpass_form' ] );

		// Social Login Support.
		add_action( 'cariera_social_login', [ $this, 'social_login_support' ] );
		add_filter( 'wsl_render_auth_widget_alter_provider_icon_markup', [ $this, 'wsl_custom_markup' ], 10, 3 );

		// Login, Register, Foget Password AJAX Functions.
		add_action( 'wp_ajax_nopriv_cariera_ajax_login', [ $this, 'login_process' ] );
		add_action( 'wp_ajax_nopriv_cariera_ajax_register', [ $this, 'register_process' ] );
		add_action( 'wp_ajax_nopriv_cariera_ajax_forgotpass', [ $this, 'forgot_pass_process' ] );

		// User Columns.
		add_filter( 'user_row_actions', [ __CLASS__, 'user_table_actions' ], 10, 2 );
		add_filter( 'manage_users_columns', [ __CLASS__, 'add_column' ] );
		add_filter( 'manage_users_custom_column', [ __CLASS__, 'status_column' ], 10, 3 );

		// Status Action.
		add_action( 'load-users.php', [ __CLASS__, 'process_update_user_action' ] );
		add_filter( 'cariera_new_user_approve_validate_status_update', [ __CLASS__, 'validate_status_update' ], 10, 3 );
		add_action( 'cariera_new_user_approve_approve_user', [ __CLASS__, 'approve_user' ] );
		add_action( 'cariera_new_user_approve_deny_user', [ __CLASS__, 'deny_user' ] );

		// Resent Approval Mail.
		add_action( 'wp_ajax_cariera_resend_approval_mail', [ __CLASS__, 'resent_approval_mail' ] );
		add_action( 'wp_ajax_nopriv_cariera_resend_approval_mail', [ __CLASS__, 'resent_approval_mail' ] );

		// User Approval Frontend.
		add_action( 'wp', [ __CLASS__, 'frontend_approve_user' ] );
		add_shortcode( 'cariera_approve_user', [ __CLASS__, 'approve_user_shortcode' ] );

		// Pending user admin dashboard count.
		add_filter( 'admin_head', [ $this, 'pending_users' ] );

		// Extra User Avatar Field.
		add_action( 'show_user_profile', [ $this, 'extra_profile_fields' ], 10 );
		add_action( 'edit_user_profile', [ $this, 'extra_profile_fields' ], 10 );

		// Save Extra User Avatar Field.
		add_action( 'personal_options_update', [ $this, 'save_extra_profile_fields' ] );
		add_action( 'edit_user_profile_update', [ $this, 'save_extra_profile_fields' ] );

		// Modifying the Avatar function.
		add_filter( 'get_avatar', [ $this, 'custom_gravatar' ], 10, 6 );
		add_filter( 'get_avatar_url', [ $this, 'custom_avatar_url' ], 10, 3 );

		// User contact methods.
		add_action( 'user_contactmethods', [ $this, 'modify_user_contact_methods' ], 10 );

		// User Frontend "My Profile".
		add_shortcode( 'cariera_my_account', [ $this, 'my_profile' ] );
		add_action( 'wp_ajax_cariera_change_user_details', [ $this, 'change_user_details' ] );
		add_action( 'wp_ajax_cariera_change_user_password', [ $this, 'change_user_password' ] );
		add_action( 'wp_ajax_cariera_delete_account', [ $this, 'delete_account' ] );

		// User Dashboard.
		add_shortcode( 'cariera_dashboard', [ $this, 'user_dashboard' ] );
	}

	/**
	 * Login & Register script
	 *
	 * @since 1.4.8
	 */
	public function login_register_script() {
		// Registering and enqueue the login/register script.
		wp_register_script( 'cariera-user-ajax', CARIERA_URL . '/assets/dist/js/login-register.js', [ 'jquery' ], CARIERA_CORE_VERSION, true );

		// Redirection Settings.
		$login_redirect       = get_option( 'cariera_login_redirection' );
		$login_redirect_candi = get_option( 'cariera_login_redirection_candidate' );
		$dashboard_title      = cariera_get_page_by_title( 'Dashboard' );
		$dashboard_page       = apply_filters( 'cariera_dashboard_page', get_option( 'cariera_dashboard_page' ) );

		if ( $dashboard_title ) {
			$dashboard = get_permalink( $dashboard_title );
		} else {
			$dashboard = get_permalink( $dashboard_page );
		}

		// Redirection after login for all users.
		if ( 'dashboard' === $login_redirect ) {
			$redirect = $dashboard;
		} elseif ( 'home' === $login_redirect ) {
			$redirect = home_url( '/' );
		} elseif ( 'custom_page' === $login_redirect ) {
			$redirect = get_permalink( apply_filters( 'cariera_login_redirection_page', get_option( 'cariera_login_redirection_page' ) ) );
		} else {
			$redirect = isset( $_SERVER['REQUEST_URI'] ) ? home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '';
		}

		// Redirection after login for candidates.
		if ( 'dashboard' === $login_redirect_candi ) {
			$redirect_candi = $dashboard;
		} elseif ( 'home' === $login_redirect_candi ) {
			$redirect_candi = home_url( '/' );
		} elseif ( 'custom_page' === $login_redirect_candi ) {
			$redirect_candi = get_permalink( apply_filters( 'cariera_login_candi_redirection_page', get_option( 'cariera_login_candi_redirection_page' ) ) );
		} else {
			$redirect_candi = isset( $_SERVER['REQUEST_URI'] ) ? home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '';
		}

		wp_localize_script(
			'cariera-user-ajax',
			'cariera_user_ajax',
			[
				'ajaxurl'           => admin_url( 'admin-ajax.php', 'relative' ),
				'loadingmessage'    => '<span class="job-manager-message generic loading"><i></i>' . esc_html__( 'Please wait...', 'cariera' ) . '</span>',
				'moderate'          => get_option( 'cariera_moderate_new_user' ),
				'auto_login'        => get_option( 'cariera_auto_login' ),
				'redirection'       => $redirect,
				'redirection_candi' => $redirect_candi,
			]
		);
	}

	/**
	 * Login Form Shortcode
	 *
	 * @since   1.0.0
	 * @version 1.7.2
	 */
	public function login_form() {
		if ( is_user_logged_in() ) {
			return;
		}

		cariera_get_template_part( 'account/login-form' );
	}

	/**
	 * Registration Form Shortcode
	 *
	 * @since   1.4.8
	 * @version 1.7.2
	 */
	public function registration_form() {
		if ( is_user_logged_in() ) {
			return;
		}

		$registration = get_option( 'cariera_registration' );

		if ( 1 === intval( $registration ) ) {
			cariera_get_template_part( 'account/register-form' );
		} else {
			cariera_get_template_part( 'account/register-form-disabled' );
		}
	}

	/**
	 * Forget Password Form Shortcode
	 *
	 * @since   1.4.8
	 * @version 1.7.2
	 */
	public function forgetpass_form() {
		if ( is_user_logged_in() ) {
			return;
		}

		cariera_get_template_part( 'account/forgot-password-form' );
	}

	/**
	 * Social login support for third party plugins
	 *
	 * @since   1.4.8
	 * @version 1.7.2
	 */
	public function social_login_support() {
		cariera_get_template_part( 'account/social-login' );
	}

	/**
	 * Customizing the markup for the WSL plugin
	 *
	 * @since  1.4.8
	 */
	public function wsl_custom_markup( $provider_id, $provider_name, $authenticate_url ) {
		?>
		<a href="<?php echo esc_url( $authenticate_url ); ?>" rel="nofollow" data-provider="<?php echo esc_attr( $provider_id ); ?>" class="wp-social-login-provider wp-social-login-provider-<?php echo esc_attr( strtolower( $provider_id ) ); ?>">
		<span><i class="fab fa-<?php echo esc_attr( strtolower( $provider_id ) ); ?>"></i><?php echo esc_html( $provider_name ); ?></span>
		</a>
		<?php
	}

	/**
	 * AJAX Login function
	 *
	 * @since   1.4.8
	 * @version 1.6.6
	 */
	public function login_process() {
		$login_captcha = get_option( 'cariera_recaptcha_login' );

		if ( class_exists( 'Cariera_Core\Extensions\Recaptcha\Recaptcha' ) && \Cariera_Core\Extensions\Recaptcha\Recaptcha::is_recaptcha_enabled() && $login_captcha ) {
			$is_recaptcha_valid = array_key_exists( 'g-recaptcha-response', $_POST ) ? \Cariera_Core\Extensions\Recaptcha\Recaptcha::is_recaptcha_valid( sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) ) : false;

			if ( ! $is_recaptcha_valid ) {
				echo wp_json_encode(
					[
						'loggedin' => false,
						'message'  => '<span class="job-manager-message error">' . esc_html__( 'Captcha is not valid.', 'cariera' ) . ' </span>',
					]
				);
				wp_die();
			}
		}

		// First check the nonce, if it fails the function will break.
		check_ajax_referer( 'cariera-ajax-login-nonce', 'login_security' );

		$creds                  = [];
		$creds['user_login']    = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		$creds['user_password'] = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';
		$creds['remember']      = isset( $_POST['remember'] ) ? true : false;

		if ( filter_var( $creds['user_login'], FILTER_VALIDATE_EMAIL ) ) {
			$user_obj = get_user_by( 'email', $creds['user_login'] );
		} else {
			$user_obj = get_user_by( 'login', $creds['user_login'] );
		}
		$user_id = isset( $user_obj->ID ) ? $user_obj->ID : '0';

		// Login notification if user is "pending" or "denied" else it will continue.
		$user_login_auth = self::get_user_status( $user_id );
		if ( 'pending' === $user_login_auth && isset( $user_obj->ID ) ) {
			echo wp_json_encode(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . self::login_message( $user_obj ) . ' </span>',
				]
			);
			die();
		} elseif ( 'denied' === $user_login_auth && isset( $user_obj->ID ) ) {
			echo wp_json_encode(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . esc_html__( 'Your account has been denied and you can not login.', 'cariera' ) . ' </span>',
				]
			);
			die();
		}

		// Sign user in with the given credentials.
		if ( is_ssl() ) {
			$user_signon = wp_signon( $creds, true );
		} else {
			$user_signon = wp_signon( $creds, false );
		}

		$user_meta = get_userdata( $user_id );
		$role      = ! empty( $user_role ) ? $user_meta->roles[0] : '';

		if ( is_wp_error( $user_signon ) ) {
			$result = wp_json_encode(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . esc_html__( 'Wrong username or password.', 'cariera' ) . ' </span>',
				]
			);
		} else {
			wp_set_current_user( $user_signon->ID );
			$result = wp_json_encode(
				[
					'loggedin' => true,
					'message'  => '<span class="job-manager-message success">' . esc_html__( 'Login successful, redirecting...', 'cariera' ) . '</span>',
					'role'     => $role,
				]
			);
		}

		echo trim( $result );
		die();
	}

	/**
	 * Login Message regarding the user's status
	 *
	 * @since 1.4.8
	 */
	public static function login_message( $user ) {
		$approval = get_option( 'cariera_moderate_new_user' );

		if ( 'email' === $approval ) {
			return sprintf( __( 'Your account has not been verified yet, you must activate your account with the link sent to your email address. If you did not receive an email, please check your junk/spam folder or you can <a href="javascript:void(0);" class="cariera-resend-approval-mail" data-login="%s">click here</a> to resend the activation email.', 'cariera' ), $user->user_login );
		} elseif ( 'admin' === $approval ) {
			return esc_html__( 'Your account has not been activate yet, please be patient until an admin activates your account.', 'cariera' );
		} else {
			return esc_html__( 'Your account has to been activated yet.', 'cariera' );
		}
	}

	/**
	 * Registration validation
	 *
	 * @since   1.4.8
	 * @version 1.7.1
	 */
	public function registration_validation( $username, $email, $password, $privacy_policy, $user_role ) {
		global $reg_errors;

		$reg_errors      = new \WP_Error();
		$password_length = get_option( 'cariera_register_password_length' );

		$registration_captcha = get_option( 'cariera_recaptcha_register' );
		if ( class_exists( 'Cariera_Core\Extensions\Recaptcha\Recaptcha' ) && \Cariera_Core\Extensions\Recaptcha\Recaptcha::is_recaptcha_enabled() && $registration_captcha ) {
			$is_recaptcha_valid = array_key_exists( 'g-recaptcha-response', $_POST ) ? \Cariera_Core\Extensions\Recaptcha\Recaptcha::is_recaptcha_valid( sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) ) : false;
			if ( ! $is_recaptcha_valid ) {
				$reg_errors->add( 'field', esc_html__( 'reCAPTCHA is a required field', 'cariera' ) );
			}
		}

		if ( empty( $username ) || empty( $password ) || empty( $email ) || empty( $privacy_policy ) ) {
			$reg_errors->add( 'field', esc_html__( 'Required form field is missing', 'cariera' ) );
		}

		if ( 4 > strlen( $username ) ) {
			$reg_errors->add( 'username_length', esc_html__( 'Username too short, it should be at least 4 characters.', 'cariera' ) );
		}

		if ( username_exists( $username ) ) {
			$reg_errors->add( 'user_name', esc_html__( 'This Username already exists', 'cariera' ) );
		}

		if ( ! validate_username( $username ) ) {
			$reg_errors->add( 'username_invalid', esc_html__( 'The Username you entered is not valid', 'cariera' ) );
		}

		if ( $password_length > strlen( $password ) ) {
			$reg_errors->add( 'password', sprintf( esc_html__( 'Password length must be greater than %s', 'cariera' ), $password_length ) );
		}

		if ( ! is_email( $email ) ) {
			$reg_errors->add( 'email_invalid', esc_html__( 'Email is not valid, please provide a correct email address.', 'cariera' ) );
		}

		if ( email_exists( $email ) ) {
			$reg_errors->add( 'email', esc_html__( 'This Email already exists.', 'cariera' ) );
		}

		if ( empty( $privacy_policy ) ) {
			$reg_errors->add( 'privacy_policy', esc_html__( 'Please accept our Privacy Policy.', 'cariera' ) );
		}

		if ( 'administrator' === $user_role ) {
			$reg_errors->add( 'user_role_security', esc_html__( 'Nice try!', 'cariera' ) );
		}
	}

	/**
	 * Complete the registration and add the user in the DB
	 *
	 * @since 1.4.8
	 */
	public function registration_complete( $username, $password, $email, $user_role ) {
		$userdata = [
			'user_login' => $username,
			'user_email' => $email,
			'user_pass'  => $password,
			'role'       => $user_role,
		];

		return wp_insert_user( $userdata );
	}

	/**
	 * AJAX Register function
	 *
	 * @since   1.4.8
	 * @version 1.6.6
	 */
	public function register_process() {
		global $reg_errors;

		// First check the nonce, if it fails the function will break.
		check_ajax_referer( 'cariera-ajax-register-nonce', 'register_security' );

		// Check username if it's hiden or not.
		if ( get_option( 'cariera_register_hide_username' ) ) {
			$reg_email    = explode( '@', $_POST['register_email'] );
			$reg_username = sanitize_user( trim( $reg_email[0] ), true );

			if ( username_exists( $reg_username ) || 4 > strlen( $reg_username ) ) {
				$reg_username .= '_' . wp_rand( 1000, 99999 );

				if ( username_exists( $reg_username ) ) {
					$reg_username .= '_' . wp_rand( 10000, 99999 );
				}
			}
		} else {
			$reg_username = sanitize_user( $_POST['register_username'] );
		}

		// Check Privacy Policy if enabled.
		if ( 1 === intval( get_option( 'cariera_register_privacy_policy' ) ) ) {
			$privacy_policy = isset( $_POST['privacy_policy'] ) ? sanitize_text_field( $_POST['privacy_policy'] ) : '';
		} else {
			$privacy_policy = 1;
		}

		if ( ! get_option( 'cariera_user_role_candidate' ) && ! get_option( 'cariera_user_role_employer' ) ) {
			$user_role = get_option( 'default_role' );
		} else {
			$user_role = sanitize_text_field( $_POST['cariera_user_role'] );
		}

		// Validate Registration fields.
		$this->registration_validation( $reg_username, $_POST['register_email'], $_POST['register_password'], $privacy_policy, $user_role );

		// If there are no errors during registration.
		if ( 1 > count( $reg_errors->get_error_messages() ) ) {

			$username = $reg_username;
			$email    = sanitize_email( $_POST['register_email'] );
			$password = sanitize_text_field( $_POST['register_password'] );

			$user_id = $this->registration_complete( $username, $password, $email, $user_role );

			// When user is registered successfully.
			if ( ! is_wp_error( $user_id ) ) {
				$user_obj = get_user_by( 'ID', $user_id );

				// If account requires approval.
				if ( get_option( 'cariera_moderate_new_user' ) !== 'auto' ) {
					$code = cariera_random_key();
					update_user_meta( $user_id, 'account_approve_key', $code );
					update_user_meta( $user_id, 'user_account_status', 'pending' );

					$approval_url = get_permalink( get_option( 'cariera_moderate_new_user_page' ) );
					$code         = get_user_meta( $user_id, 'account_approve_key', true );
					$approval_url = add_query_arg(
						[
							'user_id'     => $user_id,
							'approve-key' => $code,
						],
						$approval_url
					);

					$user = get_userdata( $user_id );

					if ( 'email' === get_option( 'cariera_moderate_new_user' ) ) {
						$recipent_mail = $user->user_email;
					} else {
						$recipent_mail = get_option( 'admin_email' );
					}

					$mail_args = [
						'send_to'      => $recipent_mail,
						'email'        => $user->user_email,
						'display_name' => $user->user_login,
						'password'     => $password,
						'approval_url' => $approval_url,
					];

					do_action( 'cariera_new_user_approval_notification', $mail_args );

					$user_data = get_userdata( $user_id );
					$final     = [
						'status'   => true,
						'register' => true,
						'message'  => '<span class="job-manager-message success">' . self::register_message( $user_data ) . '</span>',
					];

					// Account doesn't require approval.
				} else {
					if ( get_option( 'cariera_auto_login' ) ) {
						// Signing in.
						$info                  = [];
						$info['user_login']    = $username;
						$info['user_password'] = $password;
						$info['remember']      = 1;

						$note = esc_html__( 'You have been successfully registered, you will be logged in shortly.', 'cariera' );

						if ( is_ssl() ) {
							wp_signon( $info, true );
						} else {
							wp_signon( $info, false );
						}
					} else {
						$note = esc_html__( 'You have been successfully registered, you can login now.', 'cariera' );
					}

					// Send a welcome email to user/admin.
					$user      = get_userdata( $user_id );
					$role      = $user->roles[0];
					$mail_args = [
						'email'        => $user->user_email,
						'display_name' => $user->user_login,
						'password'     => $password,
					];
					do_action( 'cariera_new_user_notification', $mail_args );

					$final = [
						'register' => true,
						'message'  => '<span class="job-manager-message success">' . $note . '</span>',
						'role'     => $role,
					];
				}

				do_action( 'cariera_core_register_process_completed', $final, $user_id );
			} else {
				$final = [
					'register' => false,
					'message'  => '<span class="job-manager-message error">' . esc_html__( 'Registration Error!', 'cariera' ) . '</span>',
				];
			}

			// There are errors during registration.
		} else {
			$final = [
				'register' => false,
				'message'  => '<span class="job-manager-message error"><ul><li>' . implode( '</li><li>', $reg_errors->get_error_messages() ) . '</li></ul></span>',
			];
		}

		echo wp_json_encode( $final );
		exit;
	}

	/**
	 * Message regarding the user status after registration
	 *
	 * @since 1.4.8
	 */
	public static function register_message( $user ) {
		$approval = get_option( 'cariera_moderate_new_user' );

		if ( 'email' === $approval ) {
			return esc_html__( 'Registration complete! Before you can login you must activate your account via the email sent to you.', 'cariera' );
		} elseif ( 'admin' === $approval ) {
			return esc_html__( 'Registration complete! Your account has to be activated by an admin before you can login.', 'cariera' );
		} else {
			return esc_html__( 'Your account has to be activated.', 'cariera' );
		}
	}

	/**
	 * AJAX Forgot Password function
	 *
	 * @since   1.4.8
	 * @version 1.6.0
	 */
	public function forgot_pass_process() {
		// First check the nonce, if it fails the function will break.
		check_ajax_referer( 'cariera-ajax-forgetpass-nonce', 'forgetpass_security' );

		$forgotpass_captcha = get_option( 'cariera_recaptcha_forgotpass' );
		if ( class_exists( 'Cariera_Core\Extensions\Recaptcha\Recaptcha' ) && \Cariera_Core\Extensions\Recaptcha\Recaptcha::is_recaptcha_enabled() && $forgotpass_captcha ) {
			$is_recaptcha_valid = array_key_exists( 'g-recaptcha-response', $_POST ) ? \Cariera_Core\Extensions\Recaptcha\Recaptcha::is_recaptcha_valid( sanitize_text_field( $_POST['g-recaptcha-response'] ) ) : false;

			if ( ! $is_recaptcha_valid ) {
				echo wp_json_encode(
					[
						'loggedin' => false,
						'message'  => '<span class="job-manager-message error">' . esc_html__( 'Captcha is not valid.', 'cariera' ) . ' </span>',
					]
				);
				wp_die();
			}
		}

		global $wpdb;

		$account = isset( $_POST['forgot_pass'] ) ? sanitize_text_field( wp_unslash( $_POST['forgot_pass'] ) ) : '';

		// Account checks.
		if ( empty( $account ) ) {
			$error = esc_html__( 'Enter a Username or Email address.', 'cariera' );
		} elseif ( is_email( $account ) ) {
			if ( email_exists( $account ) ) {
				$get_by = 'email';
			} else {
				$error = esc_html__( 'There is no user registered with that Email address.', 'cariera' );
			}
		} elseif ( validate_username( $account ) ) {
			if ( username_exists( $account ) ) {
				$get_by = 'login';
			} else {
				$error = esc_html__( 'There is no user registered with that Username.', 'cariera' );
			}
		} else {
			$error = esc_html__( 'Invalid username or e-mail address.', 'cariera' );
		}

		// If no error.
		if ( empty( $error ) ) {
			if ( filter_var( $account, FILTER_VALIDATE_EMAIL ) ) {
				$user_obj = get_user_by( 'email', $account );
			} else {
				$user_obj = get_user_by( 'login', $account );
			}
			$user_id = isset( $user_obj->ID ) ? $user_obj->ID : '0';

			// Do not send newly generated password if status is not "approved".
			$user_login_auth = self::get_user_status( $user_id );
			if ( 'pending' === $user_login_auth && isset( $user_obj->ID ) ) {
				echo wp_json_encode(
					[
						'loggedin' => false,
						'message'  => '<span class="job-manager-message error">' . self::login_message( $user_obj ) . ' </span>',
					]
				);
				die();
			} elseif ( 'denied' === $user_login_auth && isset( $user_obj->ID ) ) {
				echo wp_json_encode(
					[
						'loggedin' => false,
						'message'  => '<span class="job-manager-message error">' . esc_html__( 'Your account has been denied.', 'cariera' ) . ' </span>',
					]
				);
				die();
			}

			$random_password = wp_generate_password();
			$user            = get_user_by( $get_by, $account );
			$update_user     = wp_update_user(
				[
					'ID'        => $user->ID,
					'user_pass' => $random_password,
				]
			);

			if ( $update_user ) {

				$from_name  = get_option( 'cariera_emails_name', get_bloginfo( 'name' ) );
				$from_email = get_option( 'cariera_emails_from_email', get_bloginfo( 'admin_email' ) );
				$headers    = sprintf( "From: %s <%s>\r\n Content-type: text/html", $from_name, $from_email );

				/***** Mail Content */
				$subject = esc_html__( 'Password Reset', 'cariera' );

				ob_start();
				get_template_part( '/templates/emails/header' );
				?>

				<tr><td class="h2"><?php printf( esc_html__( 'Hello %s,', 'cariera' ), $user->user_login ); ?></td></tr>
				<tr><td><?php esc_html_e( 'Your password has been resetted successfully. You can log in on your account with the newly generated password provided below.', 'cariera' ); ?></td></tr>
				<tr><td style="padding-top: 15px;"><?php printf( esc_html__( 'Your new password is: %s', 'cariera' ), $random_password ); ?></td></tr>

				<?php
				get_template_part( '/templates/emails/footer' );
				$content = ob_get_clean();

				wp_mail( $user->user_email, $subject, $content, $headers );

				$success = esc_html__( 'Go to your inbox or spam/junk and get your new generated password.', 'cariera' );
			} else {
				$error = esc_html__( 'Something went wrong while updating your account.', 'cariera' );
			}
		}

		if ( ! empty( $error ) ) {
			echo wp_json_encode(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . $error . '</span>',
				]
			);
		}

		if ( ! empty( $success ) ) {
			echo wp_json_encode(
				[
					'loggedin' => true,
					'message'  => '<span class="job-manager-message success">' . $success . '</span>',
				]
			);
		}

		die();
	}

	/**
	 * Add the "approve" or "deny" link.
	 *
	 * @since 1.4.8
	 */
	public static function user_table_actions( $actions, $user ) {
		if ( $user->ID == get_current_user_id() ) {
			return $actions;
		}

		if ( is_super_admin( $user->ID ) ) {
			return $actions;
		}

		$user_status = self::get_user_status( $user->ID );

		$approve_link = add_query_arg(
			[
				'action' => 'approve',
				'user'   => $user->ID,
			]
		);
		$approve_link = remove_query_arg( [ 'new_role' ], $approve_link );
		$approve_link = wp_nonce_url( $approve_link, 'cariera' );

		$deny_link = add_query_arg(
			[
				'action' => 'deny',
				'user'   => $user->ID,
			]
		);
		$deny_link = remove_query_arg( [ 'new_role' ], $deny_link );
		$deny_link = wp_nonce_url( $deny_link, 'cariera' );

		$approve_action = '<a href="' . esc_url( $approve_link ) . '">' . esc_html__( 'Approve', 'cariera' ) . '</a>';
		$deny_action    = '<a href="' . esc_url( $deny_link ) . '">' . esc_html__( 'Deny', 'cariera' ) . '</a>';

		if ( 'pending' === $user_status ) {
			$actions[] = $approve_action;
			$actions[] = $deny_action;
		} elseif ( 'approved' === $user_status ) {
			$actions[] = $deny_action;
		} elseif ( 'denied' === $user_status ) {
			$actions[] = $approve_action;
		}

		return $actions;
	}

	/**
	 * Add the status column to the user table
	 *
	 * @since 1.4.8
	 */
	public static function add_column( $columns ) {
		$the_columns['user_status'] = esc_html__( 'Status', 'cariera' );

		$newcol  = array_slice( $columns, 0, -1 );
		$newcol  = array_merge( $newcol, $the_columns );
		$columns = array_merge( $newcol, array_slice( $columns, 1 ) );

		return $columns;
	}

	/**
	 * Show the status of the user in the status column
	 *
	 * @since 1.4.8
	 */
	public static function status_column( $val, $column_name, $user_id ) {
		switch ( $column_name ) {
			case 'user_status':
				$status = self::get_user_status( $user_id );
				if ( 'approved' === $status ) {
					$status_i18n = esc_html__( 'approved', 'cariera' );
				} elseif ( 'denied' === $status ) {
					$status_i18n = esc_html__( 'denied', 'cariera' );
				} elseif ( 'pending' === $status ) {
					$status_i18n = esc_html__( 'pending', 'cariera' );
				}
				return $status_i18n;
				break;

			default:
		}

		return $val;
	}

	/**
	 * Get user status
	 *
	 * @since 1.4.8
	 * @param int $user_id
	 */
	public static function get_user_status( $user_id ) {
		$user_status = get_user_meta( $user_id, 'user_account_status', true );

		if ( empty( $user_status ) ) {
			$user_status = 'approved';
		}

		return $user_status;
	}

	/**
	 * Get user status
	 *
	 * @since 1.4.8
	 */
	public static function validate_status_update( $do_update, $user_id, $status ) {
		$current_status = self::get_user_status( $user_id );

		if ( 'approve' === $status ) {
			$new_status = 'approved';
		} else {
			$new_status = 'denied';
		}

		if ( $current_status == $new_status ) {
			$do_update = false;
		}

		return $do_update;
	}

	/**
	 * Get user status
	 *
	 * @since 1.4.8
	 */
	public static function update_user_status( $user, $status ) {
		$user_id = absint( $user );
		if ( ! $user_id ) {
			return false;
		}

		if ( ! in_array( $status, [ 'approve', 'deny' ], true ) ) {
			return false;
		}

		$do_update = apply_filters( 'cariera_new_user_approve_validate_status_update', true, $user_id, $status );
		if ( ! $do_update ) {
			return false;
		}

		// Where it all happens!
		do_action( 'cariera_new_user_approve_' . $status . '_user', $user_id );
		do_action( 'cariera_new_user_approve_user_status_update', $user_id, $status );

		return true;
	}

	/**
	 * Process the user status update
	 *
	 * @since 1.4.8
	 */
	public static function process_update_user_action() {
		if ( isset( $_GET['action'] ) && in_array( $_GET['action'], [ 'approve', 'deny' ], true ) && ! isset( $_GET['new_role'] ) ) {
			check_admin_referer( 'cariera' );

			$sendback = remove_query_arg( [ 'approved', 'denied', 'deleted', 'ids', 'cariera-status-query-submit', 'new_role' ], wp_get_referer() );
			if ( ! $sendback ) {
				$sendback = admin_url( 'users.php' );
			}

			$wp_list_table = _get_list_table( 'WP_Users_List_Table' );
			$pagenum       = $wp_list_table->get_pagenum();
			$sendback      = add_query_arg( 'paged', $pagenum, $sendback );

			$status = sanitize_key( $_GET['action'] );
			$user   = absint( $_GET['user'] );

			self::update_user_status( $user, $status );

			if ( 'approve' === $_GET['action'] ) {
				$sendback = add_query_arg(
					[
						'approved' => 1,
						'ids'      => $user,
					],
					$sendback
				);
			} else {
				$sendback = add_query_arg(
					[
						'denied' => 1,
						'ids'    => $user,
					],
					$sendback
				);
			}

			wp_redirect( $sendback );
			exit;
		}
	}

	/**
	 * Approve User
	 *
	 * @since 1.4.8
	 */
	public static function approve_user( $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		wp_cache_delete( $user->ID, 'users' );
		wp_cache_delete( $user->data->user_login, 'userlogins' );

		// Send mail when user gets approved.
		$mail_args = [
			'email'        => stripslashes( $user->data->user_email ),
			'display_name' => $user->data->user_login,
			'site_url'     => home_url(),
		];
		do_action( 'cariera_new_user_approved_notification', $mail_args );

		// Change usermeta tag in database to approved.
		update_user_meta( $user->ID, 'user_account_status', 'approved' );
		update_user_meta( $user->ID, 'account_approve_key', '' );

		do_action( 'cariera_new_user_approve_user_approved', $user );
	}

	/**
	 * Deny User
	 *
	 * @since 1.4.8
	 */
	public static function deny_user( $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		// Send mail when user gets approved.
		$mail_args = [
			'email'        => stripslashes( $user->data->user_email ),
			'display_name' => $user->data->user_login,
			'site_url'     => home_url(),
		];
		do_action( 'cariera_new_user_denied_notification', $mail_args );

		update_user_meta( $user->ID, 'user_account_status', 'denied' );

		do_action( 'cariera_new_user_approve_user_denied', $user );
	}

	/**
	 * Resent Approval Mail
	 *
	 * @since 1.4.8
	 */
	public static function resent_approval_mail() {

		$user_login = isset( $_POST['login'] ) ? $_POST['login'] : '';

		if ( empty( $user_login ) ) {
			echo wp_json_encode(
				[
					'status'  => false,
					'message' => '<span class="job-manager-message error">' . esc_html__( 'Username or Email not correct.', 'cariera' ) . '</span>',
				]
			);

			die();
		}

		if ( filter_var( $user_login, FILTER_VALIDATE_EMAIL ) ) {
			$user_obj = get_user_by( 'email', $user_login );
		} else {
			$user_obj = get_user_by( 'login', $user_login );
		}

		if ( ! empty( $user_obj->ID ) ) {
			$user_login_auth = self::get_user_status( $user_obj->ID );

			if ( 'pending' === $user_login_auth ) {
				if ( get_option( 'cariera_moderate_new_user' ) === 'email' ) {
					$recipent_mail = stripslashes( $user_obj->data->user_email );
				} else {
					$recipent_mail = get_option( 'admin_email' );
				}

				$approval_url = get_permalink( get_option( 'cariera_moderate_new_user_page' ) );
				$code         = get_user_meta( $user_obj->data->ID, 'account_approve_key', true );
				$approval_url = add_query_arg(
					[
						'user_id'     => $user_obj->data->ID,
						'approve-key' => $code,
					],
					$approval_url
				);

				// Send Email.
				$mail_args = [
					'send_to'      => $recipent_mail,
					'email'        => $user_obj->data->user_email,
					'display_name' => $user_obj->data->user_login,
					'approval_url' => $approval_url,
				];

				do_action( 'cariera_new_user_approval_notification', $mail_args );

				echo wp_json_encode(
					[
						'status'  => true,
						'message' => '<span class="job-manager-message success">' . esc_html__( 'Email has been sent successfully.', 'cariera' ) . '</span>',
					]
				);

				die();
			}
		}

		echo wp_json_encode(
			[
				'status'  => false,
				'message' => '<span class="job-manager-message error">' . esc_html__( 'Your account is not available.', 'cariera' ) . '</span>',
			]
		);

		die();
	}

	/**
	 * Approve user via the frontend
	 *
	 * @since 1.4.8
	 */
	public static function frontend_approve_user() {
		$post = get_post();

		if ( is_object( $post ) ) {
			if ( strpos( $post->post_content, '[cariera_approve_user]' ) !== false ) {

				$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : 0;
				$code    = isset( $_GET['approve-key'] ) ? $_GET['approve-key'] : 0;

				if ( ! $user_id ) {
					$error = [
						'error'   => true,
						'message' => esc_html__( 'The user does not exist.', 'cariera' ),
					];
				}

				$user = get_user_by( 'ID', $user_id );
				if ( empty( $user ) ) {
					$error = [
						'error'   => true,
						'message' => esc_html__( 'The user does not exist.', 'cariera' ),
					];
				} else {
					$user_code = get_user_meta( $user_id, 'account_approve_key', true );
					if ( $code != $user_code ) {
						$error = [
							'error'   => true,
							'message' => esc_html__( 'Activation code is not the same.', 'cariera' ),
						];
					}
				}

				if ( empty( $error ) ) {
					$return                       = self::update_user_status( $user_id, 'approve' );
					$error                        = [
						'error'   => false,
						'message' => esc_html__( 'Congratulations, your account has been approved!', 'cariera' ),
					];
					$_SESSION['approve_user_msg'] = $error;
				} else {
					$_SESSION['approve_user_msg'] = $error;
				}
			}
		}
	}

	/**
	 * Approve user via the frontend
	 *
	 * @since 1.4.8
	 */
	public static function approve_user_shortcode( $atts ) {
		?>
		<div class="approve-user-wrapper">
			<?php if ( isset( $_SESSION['approve_user_msg'] ) ) { ?>
				<div class="job-manager-message <?php echo esc_attr( $_SESSION['approve_user_msg']['error'] ? 'error' : 'success' ); ?>">
					<h3><?php echo trim( $_SESSION['approve_user_msg']['message'] ); ?></h3>
				</div>
				<?php
				unset( $_SESSION['approve_user_msg'] );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Adding a pending number of users in the admin dashboard
	 *
	 * @since  1.5.2
	 */
	public function pending_users() {
		global $menu;

		$plural = esc_html__( 'Users', 'cariera' );

		$args = [
			'meta_query'  => [
				[
					'key'     => 'user_account_status',
					'value'   => 'pending',
					'compare' => 'LIKE',
				],
			],
			'count_total' => true,
		];

		$users = new \WP_User_Query( $args );

		$count_users = $users->get_total();

		foreach ( $menu as $key => $menu_item ) {
			if ( strpos( $menu_item[0], $plural ) === 0 ) {
				if ( $count_users ) {
					$menu[ $key ][0] .= " <span class='awaiting-mod update-plugins count-$count_users'><span class='pending-count'>" . number_format_i18n( $count_users ) . '</span></span>';
				}
				break;
			}
		}
	}

	/**
	 * Extra field for the user Avatar on the Backend
	 *
	 * @since   1.3.4
	 * @version 1.5.2
	 */
	public function extra_profile_fields( $user ) {
		?>
		<h3><?php esc_html_e( 'Cariera Avatar', 'cariera' ); ?></h3>
		<?php wp_enqueue_media(); ?>

		<table class="form-table">
			<tr>
				<th><label for="image"><?php esc_html_e( 'Avatar', 'cariera' ); ?></label></th>
				<td>
					<?php
					$custom_avatar_id = get_the_author_meta( 'cariera_avatar_id', $user->ID );
					$custom_avatar    = wp_get_attachment_image_src( $custom_avatar_id, 'full' );
					if ( $custom_avatar ) {
						echo '<img src="' . esc_attr( $custom_avatar[0] ) . '" style="width:100px; height: auto;"/><br>';
					}
					?>
					<input type="text" name="cariera_avatar_id" id="avatar" value="<?php echo esc_attr( get_the_author_meta( 'cariera_avatar_id', $user->ID ) ); ?>" class="regular-text" />
					<input type='button' class="cariera-user-avatar button-primary" value="<?php esc_html_e( 'Upload Image', 'cariera' ); ?>" id="uploadimage"/><br />
					<span class="description"><?php esc_html_e( 'This avatar will be displayed instead of default one', 'cariera' ); ?></span>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save the extra field
	 *
	 * @since   1.3.4
	 * @version 1.5.2
	 */
	public function save_extra_profile_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( isset( $_POST['cariera_avatar_id'] ) ) {
			update_user_meta( $user_id, 'cariera_avatar_id', sanitize_text_field( wp_unslash( $_POST['cariera_avatar_id'] ) ) );
		}
	}

	/**
	 * Modifying the Avatar function
	 *
	 * @since   1.3.4
	 * @version 1.5.2
	 */
	public function custom_gravatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
		if ( is_object( $id_or_email ) ) {
			$avatar_id = get_the_author_meta( 'cariera_avatar_id', $id_or_email->ID );

			if ( ! empty( $avatar_id ) ) {
				$avatar_url = wp_get_attachment_image_src( $avatar_id, 'thumbnail' );
				if ( ! empty( $avatar_url[0] ) ) {
					$avatar = '<img src="' . esc_url( $avatar_url[0] ) . '" class="avatar avatar-' . esc_attr( $size ) . ' wp-user-avatar wp-user-avatar-' . esc_attr( $size ) . ' photo avatar-default cariera-avatar" width="' . esc_attr( $size ) . '" height="' . esc_attr( $size ) . '" alt="' . esc_attr( $alt ) . '" />';
				}
			}
		} else {
			$avatar_id = get_the_author_meta( 'cariera_avatar_id', $id_or_email );

			if ( ! empty( $avatar_id ) ) {
				$avatar_url = wp_get_attachment_image_src( $avatar_id, 'thumbnail' );
				if ( ! empty( $avatar_url[0] ) ) {
					$avatar = '<img src="' . esc_url( $avatar_url[0] ) . '" class="avatar avatar-' . esc_attr( $size ) . ' wp-user-avatar wp-user-avatar-' . esc_attr( $size ) . ' photo avatar-default cariera-avatar" width="' . esc_attr( $size ) . '" height="' . esc_attr( $size ) . '" alt="' . esc_attr( $alt ) . '" />';
				}
			}
		}

		return $avatar;
	}

	/**
	 * Modifying the Avatar URL function
	 *
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function custom_avatar_url( $url, $id_or_email, $args = null ) {

		if ( is_object( $id_or_email ) ) {
			$avatar_id = get_the_author_meta( 'cariera_avatar_id', $id_or_email->ID );

			if ( ! empty( $avatar_id ) ) {
				$avatar_url = wp_get_attachment_image_src( $avatar_id, 'thumbnail' );
				if ( ! empty( $avatar_url[0] ) ) {
					$url = esc_url( $avatar_url[0] );
				}
			}
		} else {
			$avatar_id = get_the_author_meta( 'cariera_avatar_id', $id_or_email );

			if ( ! empty( $avatar_id ) ) {
				$avatar_url = wp_get_attachment_image_src( $avatar_id, 'thumbnail' );
				if ( ! empty( $avatar_url[0] ) ) {
					$url = esc_url( $avatar_url[0] );
				}
			}
		}

		return $url;
	}

	/**
	 * Add new fields in the user contact method
	 *
	 * @since  1.5.3
	 */
	public function modify_user_contact_methods( $profile_fields ) {

		// Add new fields.
		$profile_fields['phone'] = esc_html__( 'Phone', 'cariera' );

		return $profile_fields;
	}

	/**
	 * My Account shortcode
	 * Usage: [cariera_my_profile]
	 *
	 * @since   1.5.2
	 * @version 1.7.2
	 */
	public function my_profile() {
		cariera_get_template_part( 'account/my-profile' );
	}

	/**
	 * Change user details AJAX function
	 *
	 * @since   1.7.1
	 * @version 1.7.2
	 */
	public function change_user_details() {
		$current_user   = wp_get_current_user();
		$user_id        = $current_user->ID;
		$user_avatar_id = isset( $_POST['cariera_avatar_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cariera_avatar_id'] ) ) : '';
		$user_role      = isset( $_POST['cariera_user_role'] ) ? sanitize_text_field( wp_unslash( $_POST['cariera_user_role'] ) ) : '';
		$first_name     = isset( $_POST['first-name'] ) ? sanitize_text_field( wp_unslash( $_POST['first-name'] ) ) : '';
		$last_name      = isset( $_POST['last-name'] ) ? sanitize_text_field( wp_unslash( $_POST['last-name'] ) ) : '';
		$email          = isset( $_POST['user_email'] ) ? $_POST['user_email'] : '';
		$phone          = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';

		// If email field is empty.
		if ( isset( $email ) && empty( $email ) ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'Email field is empty.', 'cariera' ),
				]
			);
			die();
		}

		// If email is not valid.
		if ( ! is_email( $email ) ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'The Email you entered is not valid or empty. Please try again...', 'cariera' ),
				]
			);
			die();
		}

		// If email already exists.
		if ( email_exists( $email ) && ( email_exists( $email ) != $current_user->ID ) ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'This email is already used by another user, please try a different one.', 'cariera' ),
				]
			);
			die();
		}

		wp_update_user(
			[
				'ID'         => $user_id,
				'user_email' => sanitize_email( $email ),
			]
		);

		// Submitting the avatar.
		if ( isset( $_POST['cariera_avatar_id'] ) ) {
			update_user_meta( $user_id, 'cariera_avatar_id', $user_avatar_id );
		}

		// Submitting the user role field.
		if ( ! empty( $user_role ) ) {
			if ( 'administrator' === $user_role || empty( $user_role ) ) {
				$user_role = get_option( 'default_role' );
			}

			wp_update_user(
				[
					'ID'   => $user_id,
					'role' => $user_role,
				]
			);
		}

		// Submitting the first name field.
		if ( $first_name ) {
			update_user_meta( $user_id, 'first_name', $first_name );
		}

		// Submitting the last name field.
		if ( $last_name ) {
			update_user_meta( $user_id, 'last_name', $last_name );
		}

		// Submitting the phone field.
		if ( isset( $_POST['phone'] ) ) {
			update_user_meta( $user_id, 'phone', $phone );
		}

		do_action( 'cariera_change_user_details_before', $_POST );

		echo wp_json_encode(
			[
				'status' => true,
				'msg'    => esc_html__( 'Your profile details has been updated.', 'cariera' ),
			]
		);

		die();
	}

	/**
	 * Change user password AJAX function
	 *
	 * @since   1.7.1
	 * @version 1.7.1
	 */
	public function change_user_password() {
		$user             = wp_get_current_user();
		$current_password = isset( $_POST['current_password'] ) ? sanitize_text_field( wp_unslash( $_POST['current_password'] ) ) : '';
		$new_password     = isset( $_POST['new_password'] ) ? sanitize_text_field( wp_unslash( $_POST['new_password'] ) ) : '';
		$confrim_password = isset( $_POST['confirm_password'] ) ? sanitize_text_field( wp_unslash( $_POST['confirm_password'] ) ) : '';

		// If the fields are empty.
		if ( empty( $current_password ) || empty( $new_password ) || empty( $confrim_password ) ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'All fields are required.', 'cariera' ),
				]
			);
			die();
		}

		// If the new password is not the same with the confirm password.
		if ( $new_password !== $confrim_password ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'New password and confirm password are not same.', 'cariera' ),
				]
			);
			die();
		}

		// If the current password is not correct.
		if ( ! wp_check_password( $current_password, $user->data->user_pass, $user->ID ) ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'Your current password is not correct.', 'cariera' ),
				]
			);
			die();
		}

		do_action( 'cariera_change_user_password_before', $_POST );

		wp_set_password( $new_password, $user->ID );
		echo wp_json_encode(
			[
				'status' => true,
				'msg'    => esc_html__( 'Your password has been successfully changed.', 'cariera' ),
			]
		);

		die();
	}

	/**
	 * Delete Account AJAX function
	 *
	 * @since   1.7.1
	 * @version 1.7.1
	 */
	public function delete_account() {
		$user_id  = get_current_user_id();
		$user     = get_userdata( $user_id );
		$userdata = get_user_by( 'ID', $user_id );
		$password = isset( $_POST['current_pass'] ) ? sanitize_text_field( wp_unslash( $_POST['current_pass'] ) ) : '';

		// Nonce verification.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cariera_delete_account' ) ) {
			$return = [
				'status' => false,
				'msg'    => esc_html__( 'Your nonce did not verify.', 'cariera' ),
			];
			echo wp_json_encode( $return );
			exit;
		}

		// If password field is empty.
		if ( empty( $password ) ) {
			$return = [
				'status' => false,
				'msg'    => esc_html__( 'Please enter your password.', 'cariera' ),
			];
			echo wp_json_encode( $return );
			exit;
		}

		// If password is not correct.
		if ( ! is_object( $userdata ) || ! wp_check_password( $password, $userdata->data->user_pass, $user_id ) ) {
			$return = [
				'status' => false,
				'msg'    => esc_html__( 'Please enter the correct password.', 'cariera' ),
			];
			echo wp_json_encode( $return );
			exit;
		}

		// Mail args for the Send email notification.
		$mail_args = [
			'email'        => $user->user_email,
			'first_name'   => $user->first_name,
			'last_name'    => $user->last_name,
			'display_name' => $user->display_name,
		];

		do_action( 'cariera_delete_account_email', $mail_args );

		// Before deleting account action.
		do_action( 'cariera_delete_account_before', $user_id, $userdata );

		wp_delete_user( $user_id );

		$return = [
			'status' => true,
			'msg'    => esc_html__( 'Your account has been successfully deleted.', 'cariera' ),
		];
		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * My Account shortcode
	 * Usage: [cariera_dashboard]
	 *
	 * @since   1.5.2
	 * @version 1.7.2
	 */
	public function user_dashboard() {
		cariera_get_template_part( 'account/dashboard/dashboard' );
	}
}
