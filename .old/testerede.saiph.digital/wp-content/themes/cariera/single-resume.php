<?php

get_header();
do_action( 'cariera_single_listing_data' );

while ( have_posts() ) :
	the_post();

	do_action( 'cariera_single_resume_start' );

	get_job_manager_template( 'content-single-resume.php', [], 'wp-job-manager-resumes' );

	do_action( 'cariera_single_resume_end' );

endwhile;

get_footer();
