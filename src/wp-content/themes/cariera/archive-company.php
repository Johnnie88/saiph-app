<?php

get_header();

$count = wp_count_posts( 'company' )->publish;
?>

<section class="page-header">
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center">
				<h1 class="title">
					<?php echo apply_filters( 'cariera_company_archive_title', wp_kses_post( sprintf( _n( 'We found %s Company in our database', 'We found %s Companies in our database', 'cariera' ), '<span class="listing-count">' . $count . '</span>' ) ) ); ?>
				</h1>
			</div>
		</div>
	</div>
</section>

<?php
if ( cariera_get_option( 'cariera_company_search_map' ) ) {
	echo do_shortcode( '[cariera-map type="company" class="companies_page"]' );
}
?>

<main class="ptb80">
	<div class="container">
		<div class="col-md-12">
			<?php
			do_action( 'cariera_before_company_loop' );

			echo do_shortcode( '[companies]' );

			do_action( 'cariera_after_company_loop' );
			?>
		</div>
	</div>
</main>

<?php
get_footer();
