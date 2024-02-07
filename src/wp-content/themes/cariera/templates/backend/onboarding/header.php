<?php
/**
 * Onboarding: Header Navigation
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/header.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $args['status'] ) {
	$req = '';
} else {
	$req = '<span class="badge">' . esc_html__( 'activation required', 'cariera' ) . '</span>';
}

$version = wp_get_theme( get_template() )->get( 'Version' );
?>

<header class="onboarding-header">
	<div class="logo">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/gnodesign-logo.svg' ); ?>" class="gnodesign-logo" />
		<span class="theme-version"><?php echo sprintf( esc_html__( 'Version: %s', 'cariera' ), $version ); ?></span>
	</div>

	<div class="navigation">
		<a href="#" data-tab="welcome" class="menu-item active">
			<div class="title"><?php esc_html_e( 'Welcome', 'cariera' ); ?></div>
			<div class="description"><?php esc_html_e( 'Get started with Cariera!', 'cariera' ); ?></div>
		</a>

		<a href="#" data-tab="plugins" class="menu-item">
			<div class="title">
			<?php
			esc_html_e( 'Plugins', 'cariera' );
			echo wp_kses_post( $req );
			?>
			</div>
			<div class="description"><?php esc_html_e( 'Install all required plugins', 'cariera' ); ?></div>
		</a>

		<a href="#" data-tab="import" class="menu-item">
			<div class="title">
			<?php
			esc_html_e( 'Import', 'cariera' );
			echo wp_kses_post( $req );
			?>
			</div>
			<div class="description"><?php esc_html_e( 'Full demo import', 'cariera' ); ?></div>
		</a>

		<a href="#" data-tab="addons" class="menu-item">
			<div class="title"><?php esc_html_e( 'Add-ons', 'cariera' ); ?></div>
			<div class="description"><?php esc_html_e( 'Compatible third party plugins', 'cariera' ); ?></div>
		</a>

		<a href="#" data-tab="themes" class="menu-item">
			<div class="title"><?php esc_html_e( 'Themes', 'cariera' ); ?></div>
			<div class="description"><?php esc_html_e( 'More themes by Gnodesign', 'cariera' ); ?></div>
		</a>
	</div>
</header>
