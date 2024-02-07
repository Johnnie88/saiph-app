<?php
/**
 * Cariera Dashboard - Views Chart template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/dashboard/views-charts.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     1.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! cariera_get_option( 'cariera_dashboard_views_statistics' ) ) {
	return;
} ?>

<div class="col-lg-6 col-md-12 dashboard-content-views">
	<div class="dashboard-card-box">
		<div class="dashboard-card-title">
			<h4 class="title"><?php esc_html_e( 'Monthly Views', 'cariera' ); ?></h4>        
		</div>

		<div class="dashboard-card-box-inner">
			<div class="canvas-loader"><span></span></div>
			<canvas id="views-chart"></canvas>
		</div>                        
	</div>
</div>
