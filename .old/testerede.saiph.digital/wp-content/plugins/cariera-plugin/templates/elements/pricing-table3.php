<?php
/**
 * Elementor Element: Pricing Tables Version 3
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/pricing-table3.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="pricing-table3 <?php echo esc_attr( $highlight ); ?>">
	<div class="pricing-header">
		<span class="title"><?php echo esc_html( $settings['title'] ); ?></span>
		<div class="amount"><?php echo esc_html( $settings['price'] ); ?></div>

		<?php if ( $highlight ) { ?>
			<div class="featured"><i class="far fa-star"></i></div>
		<?php } ?>
	
		<div class="svg-shape">
			<div>
				<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="none" x="0px" y="0px" viewBox="0 0 300 100" style="enable-background:new 0 0 300 100;" xml:space="preserve" class="injected-svg js-svg-injector" data-parent="#SVGwave1BottomShapeID-12initialId">
				<style type="text/css">
					.wave-bottom-1-0{clip-path:url(#waveBottom1ID2);fill:#FFFFFF;}
					.wave-bottom-1-1{clip-path:url(#waveBottom1ID2);fill:#FFFFFF;fill-opacity:0;}
					.wave-bottom-1-2{clip-path:url(#waveBottom1ID2);fill:#FFFFFF;}
				</style>
				<g>
					<defs>
						<rect id="waveBottom1ID1" width="300" height="100"></rect>
					</defs>
					<clipPath id="waveBottom1ID2">
						<use xlink:href="#waveBottom1ID1" style="overflow:visible;"></use>
					</clipPath>
					<path class="wave-bottom-1-0 fill-white" opacity=".4" d="M10.9,63.9c0,0,42.9-34.5,87.5-14.2c77.3,35.1,113.3-2,146.6-4.7C293.7,41,315,61.2,315,61.2v54.4H10.9V63.9z"></path>
					<path class="wave-bottom-1-0 fill-white" opacity=".4" d="M-55.7,64.6c0,0,42.9-34.5,87.5-14.2c77.3,35.1,113.3-2,146.6-4.7c48.7-4.1,69.9,16.2,69.9,16.2v54.4H-55.7   V64.6z"></path>
					<path class="wave-bottom-1-1 fill-white" opacity=".4" d="M23.4,118.3c0,0,48.3-68.9,109.1-68.9c65.9,0,98,67.9,98,67.9v3.7H22.4L23.4,118.3z"></path>
					<path class="wave-bottom-1-2 fill-white" d="M-54.7,83c0,0,56-45.7,120.3-27.8c81.8,22.7,111.4,6.2,146.6-4.7c53.1-16.4,104,36.9,104,36.9l1.3,36.7l-372-3   L-54.7,83z"></path>
				</g>
				</svg>
			</div>
		</div>
	</div>

	<div class="pricing-body">
		<ul>
			<?php foreach ( (array) $settings['content'] as $item ) { ?>
				<li><?php echo $item['detail']; ?></li>
			<?php } ?>
		</ul>
	</div>

	<div class="pricing-footer">
		<a href="<?php echo esc_url( $settings['link']['url'] ); ?>" <?php echo $settings['link']['is_external'] ? 'target="_blank"' : ''; ?> class="btn btn-main btn-effect" <?php echo $settings['link']['nofollow'] ? 'rel="nofollow"' : ''; ?>><?php echo esc_html( $settings['button_text'] ); ?></a>
	</div>
</div>
