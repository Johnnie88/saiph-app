<?php

get_header();
do_action( 'cariera_single_listing_data' );

while ( have_posts() ) :
	the_post();

	do_action( 'cariera_single_company_start' );

	get_job_manager_template( 'content-single-company.php', [], 'wp-job-manager-companies' );

	do_action( 'cariera_single_company_end' );

endwhile;

get_footer();
