<?php

get_header(); ?>

<!-- ===== Start of Page Header ===== -->
<section class="page-header">
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center">
				<?php $count = wp_count_posts( 'resume' )->publish; ?>

				<h1 class="title">
					<?php echo apply_filters( 'cariera_resumes_archive_title', wp_kses_post( sprintf( _n( 'We found %s Resume in our database', 'We found %s Resumes in our database', 'cariera' ), '<span class="listing-count">' . $count . '</span>' ) ) ); ?>
				</h1>
			</div>
		</div>
	</div>
</section>
<!-- ===== End of Page Header ===== -->

<?php
if ( cariera_get_option( 'cariera_resume_search_map' ) ) {
	echo do_shortcode( '[cariera-map type="resume" class="resume_page"]' );
}
?>

<main class="ptb80">
	<div class="container">
		<div class="col-md-12">
			<?php echo do_shortcode( '[resumes]' ); ?>
		</div>
	</div>
</main>

<?php
get_footer();
