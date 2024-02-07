<?php

/**
 * Forces user to change the password every 6 months by default.
 *
 * Newly created users are affected by default.
 *
 * Developer's hooks are provided to change the effect on existing users as well.
 *
 * @package LoginPress
 * @since 3.0.0
 */

class LoginPress_Force_Password_Reset {

	/**
	 * Returns the plugin & system information.
	 *
	 * @access public
	 * @return string
	 */
	private $_loginpress_password_reset_time_limit;

	/**
	 * Force Password Reset constructor.
	 *
	 * @since 3.0.0
	 * 
	 */
    public function __construct() {

		$time_limit = $this->loginpress_get_limit();

		if ( $time_limit === false ) {
			return;
		}

		$this->_loginpress_password_reset_time_limit =  array( 
			'loginpress_password_reset_time_limit' =>  $time_limit
		);

    	$this->_hooks();
	}

	/**
	 * Action Hooks.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function _hooks() {

		add_action( 'register_new_user',    array( $this, 'loginpress_update_expire_duration' ) );
		add_action( 'after_password_reset', array( $this, 'loginpress_update_expire_duration' ), 10 );
		add_action( 'profile_update',       array( $this, 'loginpress_user_profile_update' ), 10 );
		add_action( 'wp_login',             array( $this, 'loginpress_user_login_check' ), 10, 2 );
		add_action( 'login_message',        array( $this, 'loginpress_reset_pass_message_text' ), 0, 1 );
	}

	/**
	 * Return the password age limit setting.
	 *
	 * @since 3.0.0
	 * @return int $limit The password age limit.
	 */
	public function loginpress_get_limit() {

		$_loginpress_Setting = get_option( 'loginpress_setting' );

		if ( empty( $_loginpress_Setting['loginpress_password_reset_time_limit'] ) || ! isset( $_loginpress_Setting['loginpress_password_reset_time_limit'] ) ) {
			return false;
		}

		// By default if value is empty or equal to 0 add 182 Days by default.
		$limit = ( absint( $_loginpress_Setting['loginpress_password_reset_time_limit'] ) === 0 || empty( $_loginpress_Setting['loginpress_password_reset_time_limit'] ) ) ? 182 : absint( $_loginpress_Setting['loginpress_password_reset_time_limit'] );

		return esc_html( strtotime( "$limit days" ) );
	}

	/**
	 * Updates User meta for force change password
	 *
	 * @since 3.0.0
	 * @param [mixed object/int] $user User ID or User Object.
	 * @return void
	 */
	public function loginpress_update_expire_duration( $user ) {

		if ( is_object( $user ) ) {
			$user_id = $user->ID;
		} else {
			$user_id = $user;
		}

		// Update User meta with 6 months time-frame.
		update_user_meta( $user_id, 'loginpress_password_reset_limit', $this->_loginpress_password_reset_time_limit );
	}

	/**
	 * Callback function which Fires Fires after the user’s password is reset
	 *
	 * @param WP_User $user The User object of the user whose password was reset.
	 * 
	 * @return void
	 */
	public function loginpress_user_profile_update( $user_id ) {

		// Returns if Password is unchanged during Profile Update
		if ( ! isset( $_POST['pass1'] ) ) {
			return;
		}

		// Update User meta with 6 months time-frame.
		update_user_meta( $user_id, 'loginpress_password_reset_limit', $this->_loginpress_password_reset_time_limit );
	}

	/**
	 * Fires on login submit to check if user have reset the password less than 6 months ago.
	 *
	 * @since 3.0.0
	 * @param string  $user_login The user login name.
	 * @param WP_User $user WP_User object of the logged-in user.
	 * @return void
	 */
	public function loginpress_user_login_check( $user_login, $user ) {

		$_loginpress_Setting   = get_option( 'loginpress_setting' );
		$enable_password_reset = isset( $_loginpress_Setting['enable_password_reset'] ) && ! empty( $_loginpress_Setting['enable_password_reset'] ) ? $_loginpress_Setting['enable_password_reset'] : 'off';

		if ( $enable_password_reset !== 'off' ) {

			$restricted_roles = isset( $_loginpress_Setting['roles_for_password_reset'] ) && ! empty( $_loginpress_Setting['roles_for_password_reset'] ) ? $_loginpress_Setting['roles_for_password_reset'] : array( false );

			// Get the meta of the user since when user last reset password.
			$user_meta                = get_user_meta( $user->ID, 'loginpress_password_reset_limit', true );
			$reset_time_left          = ! empty( $user_meta ) && isset( $user_meta['loginpress_password_reset_time_limit'] ) ? $user_meta['loginpress_password_reset_time_limit'] : '' ;
			$loginpress_reset_for_all = (bool) apply_filters( 'loginpress_password_reset_for_all', false );

				// If current date is less 6 Months than that of stored before then take the user to lost password page.
				if ( ( strtotime( 'now' ) > $reset_time_left ) ) {
					if ( $loginpress_reset_for_all || in_array( ucfirst( $user->roles[0] ), $restricted_roles ) ) {

					// Logout the user.
					wp_logout();

					// redirect user to lost password page.
					wp_safe_redirect( add_query_arg( array(
						'action' => 'lostpassword',
						'expired' => 'expired',
					), wp_login_url() ), 302 ) ;

					exit;
				}
			}
		}
	}

	/**
	 * Function to convert simple days to years, months and days depending on input
	 *
	 * @version 3.0.0
	 * @return string $time_frame time converted to days, months and years.
	 */
	public function loginpress_convert_days() {

		$loginpress_Setting = get_option( 'loginpress_setting' );
		$limit = isset( $loginpress_Setting['loginpress_password_reset_time_limit'] ) && ! empty( $loginpress_Setting['loginpress_password_reset_time_limit'] ) ? $loginpress_Setting['loginpress_password_reset_time_limit'] : '';

		$years = ( $limit / 365 ) ; // days / 365 days
		$years = floor( $years ); // Remove all decimals
		$month = ( $limit % 365 ) / 30.5; // I choose 30.5 for Month (30,31) ;)
		$month = floor( $month ); // Remove all decimals
		$days  = ( $limit % 365 ) % 30.5; // the rest of days

		if ( $years != 0 ) {
			$year_string  = 1 === absint( $years ) ? __( 'Year', 'loginpress' ) : __( 'Years', 'loginpress' );
			$month_string = 1 === absint( $month ) ? __( 'Month', 'loginpress' ) : __( 'Months', 'loginpress' );
			$day_string   = 1 === absint( $days ) ? __( 'Day', 'loginpress' ) : __( 'Days', 'loginpress' );

			return sprintf( __( '%1$s %2$s, %3$s %4$s and %5$s %6$s', 'loginpress' ), $years, $year_string, $month, $month_string, $days, $day_string );

		} else if ( $month != 0 ) {
			$month_string = 1 === $month ? 'Month' : 'Months';
			$day_string   = 1 === $days ? 'Day' : 'Days';

			return sprintf( __( '%1$s %2$s and %3$s %4$s', 'loginpress' ), $month, $month_string, $days, $day_string );

		} else {
			$remain_string = 1 === $days ? __( 'Day', 'loginpress' ) : __( 'Days', 'loginpress' );
			return sprintf( '%1$s %2$s', $days, $remain_string );
		}
	}

	/**
	 * Function callback to change the Message upon Lost password
	 *
	 * @version 3.0.0
	 * @param string $user_login The user login name.
	 * @param WP_User $user WP_User object of the logged-in user.
	 * @return void
	 */
	public function loginpress_reset_pass_message_text( $message ) {

		$status = isset( $_GET['expired'] ) && ! empty( $_GET['expired'] ) ? sanitize_text_field( $_GET['expired'] ) : '';

		if ( 'expired' === $status ) {
			$limit           = $this->loginpress_convert_days();
			$default_message = sprintf( __( 'It\'s been %1$s%2$s%3$s since you last updated your password. Kindly update your password.', 'loginpress' ), '<b>', $limit, '</b>', '</br>' );
			$message         = apply_filters( 'loginpress_change_reset_message', $default_message, $limit );
			return '<p id="login_error">' . wp_kses_post( $message ) .'</p>';
		}

		return $message;
	}	
}
