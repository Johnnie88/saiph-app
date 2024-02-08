<?php
/**
 * Onboarding: Sidebar
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/sidebar.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<aside class="onboarding-sidebar">
	<div class="widget widget-documentation">
		<h3 class="title"><?php esc_html_e( 'Documentation', 'cariera' ); ?></h3>
		<p><?php esc_html_e( 'Regularly updated knowledge base that will help you get started with Cariera.', 'cariera' ); ?></p>
		<a href="https://docs.cariera.co/" class="btn-main" target="_blank"><?php esc_html_e( 'Read Documentation', 'cariera' ); ?></a>
	</div>

	<div class="widget widget-notice">
		<p><?php esc_html_e( 'If you like Cariera please support us by giving the theme a positive rating.', 'cariera' ); ?></p>
		<a href="https://themeforest.net/downloads" class="btn-link" target="_blank"><?php esc_html_e( 'Rate Cariera', 'cariera' ); ?></a>
	</div>

	<div class="widget widget-notice">
		<p><a target="_blank" href="https://themeforest.net/licenses/standard"><?php esc_html_e( 'One standard license ', 'cariera' ); ?></a><?php printf( esc_html__( 'is valid only for %s. Running multiple websites on a single license is a copyright violation.', 'cariera' ), '<strong>1 website</strong>' ); ?></p>
		<a href="https://1.envato.market/WL5MX" class="btn-link" target="_blank"><?php esc_html_e( 'Buy new license', 'cariera' ); ?></a>
	</div>

	<div class="widget">
		<h3 class="title"><?php esc_html_e( 'Recommended Hosting', 'cariera' ); ?></h3>
		<a href="https://www.cloudways.com/en/?id=759820" target="_blank"><img src="//www.cloudways.com/affiliate/accounts/default1/banners/e6f8926f.jpg" alt="The Ultimate Managed Hosting Platform" title="The Ultimate Managed Hosting Platform" width="336" height="280" /></a><img style="border:0" src="https://www.cloudways.com/affiliate/scripts/imp.php?id=759820&amp;a_bid=e6f8926f" width="1" height="1" />
	</div>
</aside>
