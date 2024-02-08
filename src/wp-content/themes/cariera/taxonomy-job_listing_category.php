<?php

$taxonomy    = get_taxonomy( get_queried_object()->taxonomy );
$term_id     = get_queried_object()->term_id;
$layout      = cariera_get_option( 'cariera_job_taxonomy_layout' );
$list_layout = cariera_get_option( 'cariera_job_taxonomy_list_version' );
$grid_layout = cariera_get_option( 'cariera_job_taxonomy_grid_version' );

// Taxonomy bg info.
$tax       = $wp_query->get_queried_object();
$tax_id    = $tax->term_id;
$term_meta = get_option( "taxonomy_$tax_id" );
$bg_img    = isset( $term_meta['background_image'] ) ? $term_meta['background_image'] : '';

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

<?php if ( ! empty( $bg_img ) && cariera_get_option( 'cariera_job_category_bg' ) == 1 ) { ?>
	<section class="page-header page-header-bg job-header job-taxonomy-header" style="background: url('<?php echo esc_attr( $bg_img ); ?>'); ">
<?php } else { ?>
	<section class="page-header job-header job-taxonomy-header">
<?php } ?>

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
		<?php echo do_shortcode( '[jobs categories=' . $term_id . ' ' . $taxonomy_layout . ']' ); ?>
	</div>
</main>

<?php
get_footer();
