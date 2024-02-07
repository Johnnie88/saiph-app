<?php
/**
 * Custom: Company - Company's Active Job Listings
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/content-company-job-listings.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.5
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_style( 'cariera-job-listings' );

global $company_preview;

if ( $company_preview ) {
	return;
}

$show_active = apply_filters( 'cariera_single_company_job_listings_per_page', get_option( 'cariera_companies_per_page' ) );

$args = [
	'posts_per_page' => $show_active,
	'post_status'    => 'publish',
];

$companies                 = cariera_get_the_company_job_listing( $post->ID, $args );
$active_job_listings       = cariera_get_the_company_job_listing_active_count( $post->ID );
$jobs_page_url             = job_manager_get_permalink( 'jobs' );
$companies_search_page_url = ! empty( $jobs_page_url ) ? add_query_arg( 'search_keywords', "company_id_{$post->ID}", $jobs_page_url ) : false;

if ( $companies->have_posts() ) {
	do_action( 'cariera_company_job_listings_before' ); ?>

	<div id="company-job-listings" class="company-job-listings">
		<h5><?php esc_html_e( 'Job Positions', 'cariera' ); ?>
		<?php if ( ! empty( $companies_search_page_url ) ) : ?>
			(<a href="<?php echo esc_url( $companies_search_page_url ); ?>" target="_blank"><?php echo esc_html( $active_job_listings ); ?></a>)
		<?php endif; ?>
		</h5>

		<ul class="job_listings job-listings-main job_list row">
			<?php
			while ( $companies->have_posts() ) :
				$companies->the_post();

				get_job_manager_template_part( 'job-templates/content', 'job_listing_list1' );
			endwhile;
			?>
		</ul>
	</div>

	<?php
	do_action( 'cariera_company_job_listings_after' );
}

wp_reset_postdata();
