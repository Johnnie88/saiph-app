<?php
/**
 * Header Extra Nav Notifications template
 *
 * This template can be overridden by copying it to cariera-child/templates/header/notifications.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.0
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! \Cariera\cariera_core_is_activated() || ! class_exists( '\Cariera_Core\Core\Notifications' ) ) {
	return;
}

if ( ! get_option( 'cariera_notifications' ) ) {
	return;
}

$notification = new \Cariera_Core\Core\Notifications();
$active       = $notification->active();
$results      = $notification->output();
?>

<div class="extra-menu-item extra-notifications">
	<a href="#" id="notifications-trigger" aria-label="<?php esc_attr_e( 'Notification trigger', 'cariera' ); ?>">
		<i class="icon-bell"></i>

		<?php if ( $active ) { ?>
			<span class="notification-count">
				<?php echo esc_html( $active ); ?>
			</span>
		<?php } ?>
	</a>

	<!-- Header Notifications Widget -->
	<div class="header-notifications-widget">
		<div class="title-bar">
			<h4 class="title"><?php esc_html_e( 'Notifications', 'cariera' ); ?></h4>
		</div>

		<div class="main-content">
			<div class="loader"><span></span></div>

			<?php
			if ( $results ) {
				echo '<div class="cariera-notifications-wrapper cariera-scroll">';
				echo wp_kses_post( $results );
				echo '</div>';
			} else {
				?>
				<p><?php esc_html_e( 'You don\'t have any notifications', 'cariera' ); ?></p>
			<?php } ?>
		</div>

		<?php if ( $results ) { ?>
			<div class="notifications-footer">
				<a href="#" id="notifications-read"><i class="icon-check"></i><?php esc_html_e( 'Mark all as read', 'cariera' ); ?></a>
			</div>
		<?php } ?>
	</div>
</div>
