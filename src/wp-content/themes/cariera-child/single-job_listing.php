<?php

get_header();
do_action( 'cariera_single_listing_data' );

while ( have_posts() ) :
	the_post();

	do_action( 'cariera_single_job_listing_start' );

	get_job_manager_template( 'content-single-job_listing.php' );

	do_action( 'cariera_single_job_listing_end' );

endwhile;

get_footer();
