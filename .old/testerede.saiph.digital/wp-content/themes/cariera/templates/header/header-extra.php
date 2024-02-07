<?php
/**
 * Header Extra template
 *
 * This template can be overridden by copying it to cariera-child/templates/header/header-extra.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.0
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$login_registration = get_option( 'cariera_login_register_layout' ); ?>

<div class="extra-menu">
	<?php
	$current_user = wp_get_current_user();

	// HEADER CART.
	if ( cariera_get_option( 'header_cart' ) ) {
		if ( \Cariera\wc_is_activated() ) {
			$cart_count = WC()->cart->get_cart_contents_count();
			$cart_class = $cart_count < 1 ? 'counter-hidden' : '';
			?>

			<div class="extra-menu-item extra-shop mini-cart woocommerce">
				<a href="#shopping-cart-modal" class="cart-contents popup-with-zoom-anim" aria-label="<?php esc_attr_e( 'Shopping cart modal trigger', 'cariera' ); ?>">
					<i class="icon-bag"></i>
					<span class="notification-count cart-count <?php echo esc_html( $cart_class ); ?>"><?php echo number_format_i18n( $cart_count ); ?></span>
				</a>
			</div>
			<?php
		}
	}

	// HEADER QUICK SEARCH.
	if ( cariera_get_option( 'header_quick_search' ) ) {
		?>
		<div class="extra-menu-item extra-search">
			<a href="#quick-search-modal" class="header-search-btn popup-with-zoom-anim" aria-label="<?php esc_attr_e( 'Quick search trigger', 'cariera' ); ?>">
				<i class="icon-magnifier" aria-hidden="true"></i>
			</a>
		</div>
		<?php
	}

	// HEADER LOGIN & ACCOUNT.
	if ( cariera_get_option( 'header_account' ) ) {
		if ( ! is_user_logged_in() ) {
			?>
			<div class="extra-menu-item extra-user">

				<?php if ( $login_registration == 'popup' ) { ?>
					<a href="#login-register-popup" class="popup-with-zoom-anim" aria-label="<?php esc_attr_e( 'User login & register trigger.', 'cariera' ); ?>">
					<?php
				} else {
					$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
					$login_registration_page_url = get_permalink( $login_registration_page );
					?>

					<a href="<?php echo esc_url( $login_registration_page_url ); ?>" aria-label="<?php esc_attr_e( 'User login & register trigger.', 'cariera' ); ?>">
				<?php } ?>
						<div class="box-icon-user"><i class="icon-user"></i>&nbsp;&nbsp;Acessar / Cadastrar</div>
				</a>
			</div>
			<?php
		} else {
			get_template_part( 'templates/header/messages' );
			get_template_part( 'templates/header/notifications' );
			get_template_part( 'templates/header/user-menu' );
		}
	}

	get_template_part( 'templates/header/header-cta' );
	?>
</div>
