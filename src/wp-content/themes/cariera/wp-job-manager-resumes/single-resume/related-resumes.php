<?php
/**
 * Custom: Single Resume - Related Resumes
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/single-resume/related-resumes.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.6.0
 * @version     1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-resume-listings' );

global $post, $resume_preview;

if ( $resume_preview ) {
	return;
}

$category = get_the_terms( $post->ID, 'resume_category' );

if ( ! $category || is_wp_error( $category ) || ! is_array( $category ) ) {
	return;
}

$category = wp_list_pluck( $category, 'term_id' );

$related_args = [
	'post_type'      => 'resume',
	'orderby'        => 'rand',
	'posts_per_page' => 6,
	'post_status'    => 'publish',
	'post__not_in'   => [ $post->ID ],
	'tax_query'      => [
		[
			'taxonomy' => 'resume_category',
			'field'    => 'id',
			'terms'    => $category,
		],
	],
];

$related_resumes = new WP_Query( apply_filters( 'cariera_related_resume_args', $related_args ) );

if ( ! $related_resumes->have_posts() ) {
	return;
}
?>

<section class="related-resumes">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h4 class="title nomargin pb30"><?php esc_html_e( 'Related Resumes', 'cariera' ); ?></h4>

				<!-- Start of Slider -->
				<ul class="resumes related-resumes-slider">                    
					<?php
					while ( $related_resumes->have_posts() ) :
						$related_resumes->the_post();
						get_job_manager_template_part( 'resume-templates/content', 'resume_grid3', 'wp-job-manager-resumes' );
					endwhile;
					?>
				</ul>
			</div>
		</div>
	</div>
</section>

<?php
wp_reset_postdata();
