<?php
/**
 * Preloader template
 *
 * This template can be overridden by copying it to cariera-child/templates/extra/preloader.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.1.0
 * @version     1.6.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! cariera_get_option( 'cariera_preloader' ) ) {
	return;
}

$preloader_ver = cariera_get_option( 'cariera_preloader_version' );

// PRELOADER VERSION 1.
if ( 'preloader1' === $preloader_ver ) { ?>
	<div id="preloader">
		<div class="inner">
			<div class="loading_effect">
				<div class="object" id="object_one"></div>
			</div>
		</div>
	</div>
	<?php
}

// PRELOADER VERSION 2.
if ( 'preloader2' === $preloader_ver ) {
	?>
	<div id="preloader">
		<div class="inner">
			<div class="loading_effect2">
				<div class="object" id="object_one"></div>
				<div class="object" id="object_two"></div>
				<div class="object" id="object_three"></div>
			</div>
		</div>
	</div>
	<?php
}

// PRELOADER VERSION 3.
if ( 'preloader3' === $preloader_ver ) {
	?>
	<div id="preloader">
		<div class="inner">
			<div class="loading_effect3">
				<div class="object"></div>
				<p><?php esc_html_e( 'loading', 'cariera' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

// PRELOADER VERSION 4.
if ( 'preloader4' === $preloader_ver ) {
	if ( cariera_get_option( 'logo' ) ) {
		$logo = apply_filters( 'cariera_preloader_logo', cariera_get_option( 'logo' ) );
	} else {
		$logo = apply_filters( 'cariera_preloader_logo', get_template_directory_uri() . '/assets/images/logo.svg' );
	}
	?>

	<div id="preloader" class="preloader4">
		<div class="inner">
			<div class="loading-container">
				<img src="<?php echo esc_url( $logo ); ?>" alt="<?php esc_attr_e( 'Site logo', 'cariera' ); ?>">
				<div id="object_one" class="object"></div>
			</div>
		</div>
	</div>
	<?php
}
