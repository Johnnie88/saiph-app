<?php
/**
 * Cookie Bar
 *
 * This template can be overridden by copying it to cariera-child/templates/extra/cookie-bar.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_msg    = cariera_get_option( 'cariera_notice_message' );
$policy_page = cariera_get_option( 'cariera_policy_page' );
?>

<div class="cariera-cookies-bar">
	<div class="cariera-cookies-inner">
		<div class="cookies-info-text">
			<?php echo esc_html( $text_msg ); ?>
		</div>
		<div class="cookies-buttons">
			<a href="#" class="btn btn-main cookies-accept-btn"><?php esc_html_e( 'Accept', 'cariera' ); ?></a>
			<?php if ( $policy_page ) { ?>
				<a href="<?php echo esc_url( get_page_link( $policy_page ) ); ?>" class="cariera-more-btn" target="_blank">
					<?php esc_html_e( 'More info', 'cariera' ); ?>
				</a>
			<?php } ?>
		</div>
	</div>
</div>
