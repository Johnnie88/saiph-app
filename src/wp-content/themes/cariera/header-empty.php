<!DOCTYPE html>
<html <?php get_language_attributes(); ?> >

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">

	<!-- Mobile viewport optimized -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">

	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<?php do_action( 'cariera_body_start' ); ?>

	<!-- Start Website wrapper -->
	<?php $layout = cariera_get_option( 'cariera_body_style' ); ?>
	<div class="wrapper <?php echo esc_attr( $layout ); ?>">

		<?php
		get_template_part( 'templates/extra/preloader' );
