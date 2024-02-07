<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woocommerce {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since   1.5.2
	 * @version 1.7.1
	 */
	public function __construct() {
		if ( ! \Cariera\wc_is_activated() ) {
			return;
		}

		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'add_to_cart_ajax_fragment' ] );
		add_action( 'woocommerce_single_product_summary', [ $this, 'share_product' ], 70 );
		add_action( 'wp_footer', [ $this, 'shopping_cart_modal' ] );
		add_action( 'wp', [ $this, 'remove_wc_actions' ] );
	}

	/**
	 * AJAXIFY the shopping page add to cart button.
	 *
	 * @since   1.0.0
	 * @version 1.5.2
	 */
	public function add_to_cart_ajax_fragment( $fragments ) {
		if ( WC()->cart->get_cart_contents_count() < 1 ) {
			$fragments['span.notification-count.cart-count'] = '<span class="notification-count cart-count counter-hidden"></span>';
		} else {
			$fragments['span.notification-count.cart-count'] = sprintf(
				'<span class="notification-count cart-count">%s</span>',
				number_format_i18n( WC()->cart->get_cart_contents_count() )
			);
		}

		return $fragments;
	}

	/**
	 * Add sharing functionality via function
	 *
	 * @since   1.3.4
	 * @version 1.5.5
	 */
	public function share_product() {
		if ( ! cariera_get_option( 'cariera_product_share' ) || ! function_exists( 'cariera_share_media' ) ) {
			return;
		}

		echo cariera_share_media();
	}

	/**
	 * Shopping Cart modal
	 *
	 * @since   1.4.0
	 * @version 1.5.2
	 */
	public function shopping_cart_modal() {
		if ( ! \Cariera\wc_is_activated() ) {
			return;
		}

		if ( false === cariera_get_option( 'header_cart' ) ) {
			return;
		}

		if ( apply_filters( 'woocommerce_widget_cart_is_hidden', is_cart() || is_checkout() ) ) {
			return;
		}
		?>

		<div id="shopping-cart-modal" class="small-dialog zoom-anim-dialog mfp-hide">
			<div class="small-dialog-headline">
				<h3 class="title"><?php esc_html_e( 'Cart', 'cariera' ); ?></h3>
			</div>

			<div class="small-dialog-content">
				<?php the_widget( 'WC_Widget_Cart' ); ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Remove WC actions
	 *
	 * @since   1.5.2
	 */
	public function remove_wc_actions() {
		// Remove WC breadcrumbs.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

		// Removing Related Products from Customizer.
		if ( false === cariera_get_option( 'cariera_related_products' ) ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		}

		// Remove sidebar from single product page.
		if ( is_product() ) {
			remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
		}

		// Hide WooCommerce Sidebar when layout is set to fullwidth.
		if ( 'fullwidth' === cariera_get_option( 'cariera_shop_layout' ) ) {
			remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
		}
	}
}
