<?php
/**
 * Account menu of the dashboard menu.
 *
 * This template can be overridden by copying it to cariera-child/templates/dashboard/account-menu.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

// Pages for the Dashboard Listing Menu.
$profile = apply_filters( 'cariera_dashboard_user_profile_page', get_option( 'cariera_dashboard_profile_page' ) );
?>

<ul class="dashboard-nav-account" data-submenu-title="<?php esc_html_e( 'Account', 'cariera' ); ?>">
	<?php if ( cariera_get_option( 'cariera_dashboard_profile_page_enable' ) ) { ?>
		<li class="dashboard-menu-item_my-profile <?php echo $post->ID == $profile ? esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo esc_url( get_permalink( $profile ) ); ?>">
				<i class="icon-user"></i><?php esc_html_e( 'My Profile', 'cariera' ); ?>
			</a>
		</li>
	<?php } ?>

	<li>
		<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><i class="icon-power"></i><?php esc_html_e( 'Logout', 'cariera' ); ?></a>
	</li>
</ul>
