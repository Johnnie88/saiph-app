<?php
/**
 * Custom: Single Job Page - Related Jobs
 *
 * This template can be overridden by copying it to yourtheme/job_manager/single-job/page-header.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.3
 * @version     1.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-job-listings' );

global $post, $job_preview;

if ( $job_preview ) {
	return;
}

$category = get_the_terms( $post->ID, 'job_listing_category' );

if ( ! $category || is_wp_error( $category ) || ! is_array( $category ) ) {
	return;
}

$category = wp_list_pluck( $category, 'term_id' );

$related_args = [
	'post_type'      => 'job_listing',
	'orderby'        => 'rand',
	'posts_per_page' => 6,
	'post_status'    => 'publish',
	'post__not_in'   => [ $post->ID ],
	'tax_query'      => [
		[
			'taxonomy' => 'job_listing_category',
			'field'    => 'id',
			'terms'    => $category,
		],
	],
];

if ( 1 === absint( get_option( 'job_manager_hide_filled_positions' ) ) ) {
	$related_args['meta_query'][] = [
		'key'     => '_filled',
		'value'   => '1',
		'compare' => '!=',
	];
}

$related_jobs = new WP_Query( apply_filters( 'cariera_related_job_args', $related_args ) );

if ( ! $related_jobs->have_posts() ) {
	return;
}
?>

<section class="related-jobs">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h4 class="title nomargin pb30"><?php esc_html_e( 'Related Jobs', 'cariera' ); ?></h4>

				<!-- Start of Slider -->
				<ul class="job_listings related-jobs-slider">                    
					<?php
					while ( $related_jobs->have_posts() ) :
						$related_jobs->the_post();
						get_job_manager_template_part( 'job-templates/content', 'job_listing_grid4' );
					endwhile;
					?>
				</ul>
			</div>
		</div>
	</div>
</section>

<?php
wp_reset_postdata();
