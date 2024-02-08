<?php
/**
 * Header Version 1 template
 *
 * This template can be overridden by copying it to cariera-child/templates/header/header1.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.0.0
 * @version     1.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'templates/header/top-header' );

$header_top         = get_post_meta( get_the_ID(), 'cariera_header1_fixed_top', 'true' );
$header_transparent = get_post_meta( get_the_ID(), 'cariera_header1_transparent', 'true' );
$header_white       = get_post_meta( get_the_ID(), 'cariera_header1_white', 'true' );
$login_registration = get_option( 'cariera_login_register_layout' );

$header_classes = [ 'cariera-main-header', 'main-header', 'header1' ];

if ( $header_top == 1 ) {
	$header_classes[] = 'header-fixed-top';
}

if ( $header_transparent == 1 ) {
	$header_classes[] = 'header-transparent';
}

if ( $header_white == 1 ) {
	$header_classes[] = 'header-white';
}

if ( cariera_get_option( 'cariera_sticky_header', 'true' ) ) {
	$header_classes[] = 'sticky-header';
}

if ( cariera_get_option( 'cariera_fullwidth_header', 'true' ) ) {
	$header_width = 'container-fluid';
} else {
	$header_width = 'container';
} ?>

<header class="<?php echo esc_attr( join( ' ', $header_classes ) ); ?>">
	<div class="header-container <?php echo esc_attr( $header_width ); ?>">

		<!-- ====== Start of Logo ====== -->
		<div class="logo">
			<?php if ( cariera_get_option( 'logo' ) ) { ?>
				<a class="navbar-brand logo-wrapper nomargin" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr( bloginfo( 'name' ) ); ?>" rel="home">
					<!-- Logo -->
					<img src="<?php echo esc_url( cariera_get_option( 'logo' ) ); ?>" class="logo" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" />

					<?php if ( cariera_get_option( 'logo-white' ) ) { ?>
						<!-- White Logo -->
						<img src="<?php echo esc_url( cariera_get_option( 'logo-white' ) ); ?>" class="logo-white" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" />
					<?php } ?>
				</a>
			<?php } elseif ( cariera_get_option( 'logo_text' ) ) { ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="logo-text">
					<?php echo esc_html( cariera_get_option( 'logo_text' ) ); ?>
				</a>
			<?php } else { ?>
				<a class="navbar-brand logo-wrapper" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_html( bloginfo( 'name' ) ); ?>" rel="home">
					<!-- INSERT YOUR LOGO HERE -->
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.svg' ); ?>" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" width="150" class="logo">

					<!-- INSERT YOUR WHITE LOGO HERE -->
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-white.svg' ); ?>" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" width="150" class="logo-white">
				</a>
			<?php } ?>
		</div>
		<!-- ====== End of Logo ====== -->

		<!-- ====== Start of Mobile Navigation ====== -->
		<div class="mmenu-trigger <?php if ( wp_nav_menu( [ 'theme_location' => 'primary', 'echo' => false ] ) == false ) { ?> hidden-burger <?php } ?>">
			<button id="mobile-nav-toggler" class="hamburger hamburger--collapse" type="button" aria-label="<?php esc_attr_e( 'Mobile navigation toggler', 'cariera' ); ?>">
				<span class="hamburger-box">
					<span class="hamburger-inner"></span>
				</span>
			</button>
		</div>
		<!-- ====== Endo of Mobile Navigation ====== -->

		<!-- ====== Start of Main Menu ====== -->
		<nav class="main-nav-wrapper">
			<?php
			wp_nav_menu(
				[
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'main-menu main-nav',
					'walker'         => new Cariera_Mega_Menu_Walker(),
					'fallback_cb'    => '\Cariera\menu_fallback',
				]
			);
			?>
		</nav>
		<!-- ====== End of Main Menu ====== -->

		<?php get_template_part( 'templates/header/header-extra' ); ?>
	</div>
</header>
