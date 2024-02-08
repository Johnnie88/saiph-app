<?php
/**
 * Elementor Element: Pricing Tables Version 2
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/pricing-table2.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attr    = '';
$img_src = wp_get_attachment_image_src( $settings['background_img']['id'], 'full' );
if ( $settings['background_img'] && ! empty( $img_src ) ) {
	$attr = 'style="background-image: url(' . esc_attr( $img_src[0] ) . ')"';
}
?>

<div class="pricing-table2 <?php echo esc_attr( $highlight ); ?>">
	<div class="pricing-header" <?php echo $attr; ?>>
		<span class="title"><?php echo esc_html( $settings['title'] ); ?></span>
		<div class="amount"><?php echo esc_html( $settings['price'] ); ?></div>
		<p class="description"><?php echo esc_html( $settings['description'] ); ?></p>

		<?php if ( ! empty( $settings['overlay_color'] ) ) { ?>
			<div class="overlay" style="background: <?php echo esc_attr( $settings['overlay_color'] ); ?>"></div>
		<?php } ?>

		<?php if ( $highlight ) { ?>
			<div class="featured"><i class="far fa-star"></i></div>
		<?php } ?>
	</div>

	<div class="pricing-body">
		<ul>
			<?php foreach ( (array) $settings['content'] as $item ) { ?>
				<li><?php echo $item['detail']; ?></li>
			<?php } ?>
		</ul>
	</div>

	<div class="pricing-footer">
		<a href="<?php echo esc_url( $settings['link']['url'] ); ?>" <?php echo $settings['link']['is_external'] ? 'target="_blank"' : ''; ?> <?php echo $settings['link']['nofollow'] ? 'rel="nofollow"' : ''; ?>><?php echo esc_html( $settings['button_text'] ); ?></a>
	</div>
</div>
