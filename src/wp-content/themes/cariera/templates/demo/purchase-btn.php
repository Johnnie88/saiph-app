<?php
/**
 * Purchase Button
 *
 * This template can be overridden by copying it to cariera-child/templates/demo/purchase-btn.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$url   = apply_filters( 'cariera_envato_purchase_url', 'https://1.envato.market/WL5MX' );
$price = apply_filters( 'cariera_envato_purchase_price', '79$' );
?>

<a href="<?php echo esc_url( $url ); ?>" class="envato-btn-purchase" target="_blank">
	<span><?php echo esc_html( $price ); ?></span>
</a>
