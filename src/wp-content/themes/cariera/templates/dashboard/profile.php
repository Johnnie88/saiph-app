<?php
/**
 * Profile section of the dashboard menu.
 *
 * This template can be overridden by copying it to cariera-child/templates/dashboard/profile.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();
$user_id      = get_current_user_id();
$user_img     = get_avatar( get_the_author_meta( 'ID', $user_id ), 80 );
?>

<div class="dashboard-profile-box">
	<span class="avatar-img">
		<div class="login-status"></div>
		<?php echo wp_kses_post( $user_img ); ?>
	</span>
	<span class="fullname">
		<?php echo esc_html( $current_user->first_name ) . ' ' . esc_html( $current_user->last_name ); ?>
	</span>
	<span class="user-role">
		<?php
		if ( $current_user->roles[0] == 'administrator' ) {
			esc_html_e( 'Administrator', 'cariera' );
		} elseif ( $current_user->roles[0] == 'employer' ) {
			esc_html_e( 'Employer', 'cariera' );
		} elseif ( $current_user->roles[0] == 'candidate' ) {
			esc_html_e( 'Candidate', 'cariera' );
		} else {
			echo esc_html( $current_user->roles[0] );
		}
		?>
	</span>
</div>
