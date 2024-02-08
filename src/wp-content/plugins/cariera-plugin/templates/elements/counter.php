<?php
/**
 * Elementor Element: Counter
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/counter.php.
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

<div class="counter-container">
	<div class="counter <?php echo esc_attr( $settings['layout'] ); ?> <?php echo esc_attr( $settings['theme'] ); ?>">

		<?php if ( $settings['enable_icon'] == 'enable' ) { ?>
			<div class="counter-icon">
				<?php
				if ( $settings['icon_type'] == 'icon' ) {
					\Elementor\Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] );
				} else {
					echo '<img src="' . esc_url( $settings['image']['url'] ) . '">';
				}
				?>
			</div>
		<?php } ?>

		<div class="counter-details">
			<div class="counter-number-wrapper">
				<span class="counter-number" data-from="0" data-to="<?php echo esc_attr( $number ); ?>">0</span>

				<?php if ( $settings['value'] === 'custom' && ! empty( $settings['suffix'] ) ) { ?>
					<span class="counter-suffix"><?php echo esc_html( $settings['suffix'] ); ?></span>
				<?php } ?>
			</div>

			<h4 class="title"><?php echo esc_html( $settings['title'] ); ?></h4>                
		</div>
	</div>
</div>
