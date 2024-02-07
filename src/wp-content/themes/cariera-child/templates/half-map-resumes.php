<?php
/**
 *
 * @package Cariera
 *
 * @since    1.2.8
 * @version  1.7.0
 *
 * ========================
 * Template Name: Half Map - Resumes
 * ========================
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-half-map-listings' );

get_header();

$half_map_side = cariera_get_option( 'cariera_resume_half_map_layout' );
$resume_layout = cariera_get_option( 'cariera_half_map_single_resume_layout' );

if ( 'left-side' === $half_map_side ) {
	$map_side    = 'map-holder-right';
	$resume_side = 'job-holder-left';
} else {
	$map_side    = 'map-holder-left';
	$resume_side = 'job-holder-right';
} ?>

<main class="half-map-wrapper resumes-half-map">
	<?php if ( \Cariera\wp_resume_manager_is_activated() ) { ?>
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
			<?php echo do_shortcode( '[cariera-map height="100%" type="resume"]' ); ?>
		</div>

		<!-- Job Holder -->
		<div class="resume-holder <?php echo esc_attr( $resume_side ); ?> cariera-scroll">
			<h3 class="title"><?php echo esc_html( cariera_get_option( 'cariera_resume_half_map_text' ) ); ?></h3>

			<?php
			if ( $resume_layout === 'list1' ) {
				echo do_shortcode( '[resumes]' );
			} elseif ( $resume_layout === 'list2' ) {
				echo do_shortcode( '[resumes resumes_list_version="2"]' );
			} elseif ( $resume_layout === 'grid1' ) {
				echo do_shortcode( '[resumes resumes_layout="grid" resumes_grid_version="1"]' );
			} elseif ( $resume_layout === 'grid2' ) {
				echo do_shortcode( '[resumes resumes_layout="grid" resumes_grid_version="2"]' );
			} else {
				echo do_shortcode( '[resumes]' );
			}
			?>
		</div> 
	<?php } else { ?>
		<div class="container">
			<div class="col-md-12">
				<div class="job-manager-message error mt80">
					<span><?php esc_html_e( 'Please activate the "Resume Manager" plugin in order to make this template work.', 'cariera' ); ?></span>
				</div>
			</div>
		</div>
	<?php } ?>
</main>

<?php
get_footer();
