<?php
/**
 * Footer Social Media template
 *
 * This template can be overridden by copying it to cariera-child/templates/footer/social-media.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.0
 * @version     1.6.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$footericons = cariera_get_option( 'cariera_footer_socials', [] );


if ( empty( $footericons ) ) {
	return;
}
?>

<ul class="social-btns text-right">
	<?php foreach ( $footericons as $icon ) { ?>
		<li class="list-inline-item">
			<a class="social-btn-roll <?php echo esc_attr( $icon['social_type'] ); ?>" href="<?php echo esc_url( $icon['link_url'] ); ?>" target="_blank" aria-label="<?php esc_attr_e( 'Social media link', 'cariera' ); ?>">
				<div class="social-btn-roll-icons">
					<?php if ( 'twitter-x' === $icon['social_type'] ) { ?>
						<svg class="social-btn-roll-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg>
						<svg class="social-btn-roll-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg>
					<?php } else { ?>
						<i class="social-btn-roll-icon fab fa-<?php echo esc_attr( $icon['social_type'] ); ?>"></i>
						<i class="social-btn-roll-icon fab fa-<?php echo esc_attr( $icon['social_type'] ); ?>"></i>
					<?php } ?>
				</div>
			</a>
		</li>
	<?php } ?>
</ul>
