<?php

get_header(); ?>

<section class="page-header">
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center">
				<?php $count = wp_count_posts( 'job_listing' )->publish; ?>

				<h1 class="title">
					<?php echo apply_filters( 'cariera_job_listing_archive_title', wp_kses_post( sprintf( _n( 'We found %s Job Listing in our database', 'We found %s Job Listings in our database', 'cariera' ), '<span class="listing-count">' . $count . '</span>' ) ) ); ?>
				</h1>
			</div>
		</div>
	</div>
</section>

<?php
if ( cariera_get_option( 'cariera_job_search_map' ) ) {
	echo do_shortcode( '[cariera-map type="job_listing" class="jobs_page"]' );
}
?>

<main class="ptb80">
	<div class="container">
		<div class="col-md-12">
			<?php echo do_shortcode( '[jobs]' ); ?>
		</div>
	</div>
</main>

<?php
get_footer();
