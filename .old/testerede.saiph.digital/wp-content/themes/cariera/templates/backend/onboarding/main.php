<?php
/**
 * Onboarding: Main Page
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/main.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'cariera-onboarding' );
wp_enqueue_style( 'cariera-onboarding' );

// Notify.
wp_enqueue_script( 'jquery-confirm' );
wp_enqueue_style( 'jquery-confirm' ); ?>

<div class="cariera-onboarding">
	<span id="cariera-home-url" style="display:none;"><?php echo esc_url( home_url() ); ?></span>

	<!-- Navigation -->
	<?php get_template_part( 'templates/backend/onboarding/header', null, [ 'status' => $args['status'] ] ); ?>
	
	<!-- Content -->
	<section class="onboarding-content">

		<!-- Welcome -->
		<div id="welcome" class="content-page active">
			<?php get_template_part( 'templates/backend/onboarding/theme-info' ); ?>

			<?php if ( ! $args['status'] ) { ?>
				<h2><?php esc_html_e( 'Get Started!', 'cariera' ); ?></h2>
				<p><?php echo wp_kses_post( __( 'Activate the theme using your <strong>Envato Purchase Code</strong>. You can use 1 license for <strong>1 website</strong>, if you want to use your license on a different domain then make sure to deregister your license first and activate it on your new domain.', 'cariera' ) ); ?></p>
			<?php } ?>

			<div class="license-container">
				<?php do_action( 'cariera_onboarding_license' ); ?>
				<?php do_action( 'cariera_onboarding_license_sidebar' ); ?>
			</div>
		</div>

		<!-- Required Plugins -->
		<div id="plugins" class="content-page">
			<h2 class="title"><?php esc_html_e( 'Required Plugins', 'cariera' ); ?></h2>

			<?php do_action( 'cariera_onboarding_plugins' ); ?>
		</div>

		<!-- Import Demo Content -->
		<div id="import" class="content-page">
			<h2 class="title"><?php esc_html_e( 'Import Demo Content', 'cariera' ); ?></h2>

			<?php do_action( 'cariera_onboarding_import' ); ?>
		</div>

		<!-- Compatible Plugins -->
		<?php get_template_part( 'templates/backend/onboarding/addons' ); ?>
		
		<!-- Gnodesign Themes -->
		<?php get_template_part( 'templates/backend/onboarding/gnodesign-themes' ); ?>
	</section>

	<!-- Sidebar -->
	<?php get_template_part( 'templates/backend/onboarding/sidebar' ); ?>
</div>

<?php get_template_part( 'templates/backend/admin/support-link' ); ?>
