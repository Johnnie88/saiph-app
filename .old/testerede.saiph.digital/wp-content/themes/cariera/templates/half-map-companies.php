<?php
/**
 *
 * @package Cariera
 *
 * @since    1.3.1
 * @version  1.7.0
 *
 * ========================
 * Template Name: Half Map - Companies
 * ========================
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-half-map-listings' );

get_header();

$half_map_side = cariera_get_option( 'cariera_company_half_map_layout' );

if ( 'left-side' === $half_map_side ) {
	$map_side     = 'map-holder-right';
	$company_side = 'company-holder-left';
} else {
	$map_side     = 'map-holder-left';
	$company_side = 'company-holder-right';
} ?>

<main class="half-map-wrapper companies-half-map">
	<?php if ( \Cariera\company_manager_is_activated() ) { ?>
		<!-- Navigation shown on devices -->
		<div class="responsive-nav">
			<ul class="nav nav-tabs">
				<li class="show-results active">
					<a href="#" class="list-view"><i class="fas fa-list"></i><?php esc_html_e( 'List view', 'cariera' ); ?></a>
				</li>
				<li class="show-map">
					<a href="#" class="map-view"><i class="fas fa-map"></i><?php esc_html_e( 'Map view', 'cariera' ); ?></a>
				</li>
			</ul>
		</div>

		<!-- Map Holder -->
		<div class="map-holder <?php echo esc_attr( $map_side ); ?>">
			<?php echo do_shortcode( '[cariera-map height="100%" type="company"]' ); ?>
		</div>

		<!-- Job Holder -->
		<div class="company-holder <?php echo esc_attr( $company_side ); ?> cariera-scroll">
			<h3 class="title"><?php echo esc_html( cariera_get_option( 'cariera_company_half_map_text' ) ); ?></h3>
			<?php echo do_shortcode( '[companies]' ); ?>
		</div>  
	<?php } else { ?>
		<div class="container">
			<div class="col-md-12">
				<div class="job-manager-message error mt80">
					<span><?php esc_html_e( 'Please activate the "Cariera Core" plugin in order to make this template work.', 'cariera' ); ?></span>
				</div>
			</div>        
		</div>
	<?php } ?>
</main>

<?php
get_footer();
