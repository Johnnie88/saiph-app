<?php
/**
 * Cariera Dashboard - Choose promotion template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/promotions/choose-promotion.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="promotions-modal" class="small-dialog zoom-anim-dialog mfp-hide">
	<div class="small-dialog-headline">
		<h3 class="title"><?php echo esc_html( $title ); ?></h3>
	</div>

	<div class="small-dialog-content">
		<div class="promo-packages-wrapper">
			<span class="loader"><span></span></span>

			<?php
			// Existing Promotional Packages.
			if ( ! empty( $products ) ) {
				?>
				<h2 class="title"><?php esc_html_e( 'Promotional Packages', 'cariera' ); ?></h2> 
				<ul class="promo-packages buy-packages">
					<?php
					foreach ( (array) $products as $product ) {
						if ( ! $product->is_type( $type ) || ! $product->is_purchasable() || $product->get_duration() <= 0 ) {
							continue;
						}
						?>

						<li class="promo-package" data-package-id="<?php echo esc_attr( $product->get_id() ); ?>" data-process="buy-package">
							<a href="#">
								<div class="package-icon">
									<i class="icon-energy"></i>
								</div>

								<div class="package-details">
									<h5><?php echo esc_html( $product->get_name() ); ?></h5>
									<?php if ( ! empty( $product->get_short_description() ) ) { ?>
										<p class="promo-desc"><?php echo wp_kses_post( $product->get_short_description() ); ?></p>
									<?php } ?>
									<p>
										<span><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
										<?php printf( esc_html__( 'Promotion lasts for %s days', 'cariera' ), number_format_i18n( $product->get_duration() ) ); ?>
									</p>
								</div>
							</a>
						</li>
					<?php } ?>
				</ul>
			<?php } ?>

			<?php if ( empty( $products ) && empty( $packages ) ) { ?>
				<span><?php esc_html_e( 'There are no promotional packages available at the moment.', 'cariera' ); ?></span>
			<?php } ?>
		</div>
	</div>
</div>
