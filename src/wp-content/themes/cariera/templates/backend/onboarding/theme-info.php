<?php
/**
 * Onboarding: Theme Info
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/theme-info.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme        = wp_get_theme();
$version      = $theme->get( 'Version' );
$child        = false;
$child_status = esc_html__( 'Not Active', 'cariera' );

// If Child theme is active.
if ( $theme->parent() ) {
	$child        = true;
	$child_status = esc_html__( 'Active', 'cariera' );

	$theme   = $theme->parent();
	$version = $theme->get( 'Version' );
}
?>

<div class="theme-info">
	<div class="theme-preview">
		<img height="160" src="<?php echo esc_url( $theme->get_screenshot() ); ?>">
	</div>

	<div class="details">
		<h2 class="title"><?php esc_html_e( 'Welcome to Cariera!', 'cariera' ); ?></h2>

		<ul>
			<li class="child-theme"><strong><?php esc_html_e( 'Child theme:', 'cariera' ); ?></strong><span class="<?php echo esc_attr( $child == true ? 'green' : 'red' ); ?>"><?php echo esc_html( $child_status ); ?></span></li>
			<li class="version"><strong><?php esc_html_e( 'Theme Version:', 'cariera' ); ?></strong><span><?php echo esc_html( $version ); ?></span></li>
			<li class="changelog"><a href="https://youtu.be/6Q5JDTOuRkY" target="_blank"><?php esc_html_e( 'Installation Video', 'cariera' ); ?></a> | <a href="https://1.envato.market/Dj5Yq" target="_blank"><?php esc_html_e( 'Full Changelog', 'cariera' ); ?></a> | <a href="https://www.facebook.com/groups/858997041561417" target="_blank"><?php esc_html_e( 'FB Group', 'cariera' ); ?></a></li>
		</ul>
	</div>
</div>
