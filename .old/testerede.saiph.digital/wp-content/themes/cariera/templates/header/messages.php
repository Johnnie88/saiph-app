<?php
/**
 * Header Extra Nav Messages template
 *
 * This template can be overridden by copying it to cariera-child/templates/header/messages.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.6.0
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! \Cariera\cariera_core_is_activated() || ! class_exists( '\Cariera_Core\Core\Messages' ) ) {
	return;
}

if ( ! get_option( 'cariera_private_messages' ) ) {
	return;
}
?>

<div class="extra-menu-item extra-messages">
	<a href="#private-messages" class="popup-with-zoom-anim private-messages-trigger" aria-label="<?php esc_attr_e( 'Messages modal trigger', 'cariera' ); ?>">
		<i class="icon-envelope"></i>

		<span class="notification-count d-none"><?php echo esc_html( '0' ); ?></span>
	</a>
</div>
