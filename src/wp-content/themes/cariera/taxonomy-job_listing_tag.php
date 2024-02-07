<?php

$taxonomy    = get_taxonomy( get_queried_object()->taxonomy );
$layout      = cariera_get_option( 'cariera_job_taxonomy_layout' );
$list_layout = cariera_get_option( 'cariera_job_taxonomy_list_version' );
$grid_layout = cariera_get_option( 'cariera_job_taxonomy_grid_version' );

// Add layout options if settings exist.
if ( ! empty( $layout ) ) {
	if ( 'list' === $layout ) {
		$taxonomy_layout = 'jobs_layout="list" jobs_list_version="' . $list_layout . '"';
	} else {
		$taxonomy_layout = 'jobs_layout="grid" jobs_grid_version="' . $grid_layout . '"  ';
	}
} else {
	$taxonomy_layout = '';
}

get_header(); ?>

<section class="page-header job-header job-taxonomy-header">
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center">
				<h1 class="title">
					<?php
					echo $taxonomy ? esc_attr( $taxonomy->labels->singular_name ) . ': ' : '';
					single_term_title();
					?>
				</h1>
			</div>
		</div>
	</div>
</section>

<main class="ptb80">
	<div class="container">
		<?php echo do_shortcode( '[jobs_by_tag tag="' . get_query_var( 'term' ) . '" ' . $taxonomy_layout . ']' ); ?>
	</div>
</main>

<?php
get_footer();
