<?php
/**
 *
 * @package Cariera
 *
 * @since   1.2.8
 * @version 1.7.0
 *
 * ========================
 * Template Name: Half Map - Jobs
 * ========================
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-half-map-listings' );

get_header();

$half_map_side = cariera_get_option( 'cariera_job_half_map_layout' );
$job_layout    = cariera_get_option( 'cariera_half_map_single_job_layout' );

if ( 'left-side' === $half_map_side ) {
	$map_side = 'map-holder-right';
	$job_side = 'job-holder-left';
} else {
	$map_side = 'map-holder-left';
	$job_side = 'job-holder-right';
} ?>

<main class="half-map-wrapper jobs-half-map">
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
		<?php echo do_shortcode( '[cariera-map height="100%"]' ); ?>
	</div>

	<!-- Job Holder -->
	<div class="job-holder <?php echo esc_attr( $job_side ); ?> cariera-scroll">
		<h3 class="title"><?php echo esc_html( cariera_get_option( 'cariera_job_half_map_text' ) ); ?></h3>

		<?php
		if ( $job_layout == '1' ) {
			echo do_shortcode( '[jobs show_pagination="true"]' );
		} elseif ( $job_layout == '2' ) {
			echo do_shortcode( '[jobs jobs_list_version="2"]' );
		} elseif ( $job_layout == '3' ) {
			echo do_shortcode( '[jobs jobs_list_version="3"]' );
		} elseif ( $job_layout == '4' ) {
			echo do_shortcode( '[jobs jobs_list_version="4"]' );
		} else {
			echo do_shortcode( '[jobs jobs_list_version="5"]' );
		}
		?>
	</div>
</main>

<?php
get_footer();
