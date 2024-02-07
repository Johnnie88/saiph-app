<?php
/**
 * Onboarding: Gnodesign Themes
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/gnodesign-themes.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$themes = [
	'autohub' => [
		'title'  => esc_html( 'Autohub - Automotive Directory Theme' ),
		'bg-img' => get_template_directory_uri() . '/assets/images/themes/autohub.jpg',
		'link'   => 'https://1.envato.market/k72Rn',
		'price'  => '69$',
	],
	'cocoon'  => [
		'title'  => esc_html( 'Cocoon - WooCommerce WordPress Theme' ),
		'bg-img' => get_template_directory_uri() . '/assets/images/themes/cocoon.jpg',
		'link'   => 'https://1.envato.market/qqMaL',
		'price'  => '59$',
	],
];
?>

<div id="themes" class="content-page">
	<h2 class="title"><?php esc_html_e( 'More Quality Themes', 'cariera' ); ?>
		<a href="https://1.envato.market/MOKEn" class="title-btn envato" target="_blank"><?php echo esc_html( 'Envato Portfolio' ); ?></a> 
	</h2>

	<div class="onboarding-products">
		<?php foreach ( $themes as $theme ) { ?>
			<div class="product-item">
				<a href="<?php echo esc_url( $theme['link'] ); ?>" target="_blank">
					<div class="theme-img" style="background-image: url('<?php echo esc_url( $theme['bg-img'] ); ?>');">
						<div class="price"><?php echo esc_html( $theme['price'] ); ?></div>
					</div>
					<div class="title"><?php echo esc_html( $theme['title'] ); ?></div>
				</a>
			</div>
		<?php } ?>
	</div>
</div>
