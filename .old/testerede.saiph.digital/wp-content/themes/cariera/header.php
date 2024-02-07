<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">

	<!-- Mobile viewport optimized -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php
	wp_body_open();
	do_action( 'cariera_body_start' );
	$layout = cariera_get_option( 'cariera_body_style' );
	?>

	<!-- Start Website wrapper -->
	<div class="wrapper <?php echo esc_attr( $layout ); ?>">
		<?php
		get_template_part( 'templates/extra/preloader' );

		// Add Elementor Pro support for Custom Header.
		if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
			if ( get_post_meta( get_the_ID(), 'cariera_show_header', 'true' ) !== 'hide' ) {
				$header = cariera_get_option( 'cariera_header_style' );
				get_template_part( 'templates/header/' . $header );
			}
		}
