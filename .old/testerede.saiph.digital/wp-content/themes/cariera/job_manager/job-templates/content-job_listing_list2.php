<?php
/**
 * Custom: Job Listing - List Version 2
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-template/content-job_listing_list2.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.2.5
 * @version     1.6.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

$job_class = 'job-list single_job_listing_2';

$job_id   = get_the_ID();
$company  = '';
$logo     = get_the_company_logo();
$featured = get_post_meta( $job_id, '_featured', true ) == 1 ? 'featured' : '';

// If Cariera Company manager exists and company integration check.
if ( \Cariera\cariera_core_is_activated() && get_option( 'cariera_company_manager_integration', false ) && function_exists( 'cariera_get_the_company' ) ) {
	$company = get_post( cariera_get_the_company() );
}

// Logo if there is an active company.
if ( ! empty( $company ) && has_post_thumbnail( $company ) ) {
	$logo = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
}

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
} ?>

<li <?php job_listing_class( esc_attr( $job_class ) ); ?> data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo esc_attr( get_the_ID() ); ?>" data-featured="<?php echo esc_attr( $featured ); ?>">
	<a href="<?php the_job_permalink(); ?>">
		<div class="job-content-wrapper">

			<!-- Job Company -->
			<div class="job-content-company">
				<div class="job-company">
					<?php
					// Company Logo.
					if ( ! empty( $company ) && has_post_thumbnail( $company ) ) {
						echo '<img class="company_logo" src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_the_company_name( $company ) ) . '" />';
					} else {
						cariera_the_company_logo();
					}
					?>
				</div>
			</div>

			<!-- Job Title & Info -->
			<div class="job-content-main">
				<div class="job-title">
					<h5 class="title">
						<?php the_title(); ?>
						<?php do_action( 'cariera_job_listing_status' ); ?>    
					</h5>
				</div>

				<div class="job-info">
					<?php do_action( 'job_listing_info_start' ); ?>

					<?php if ( cariera_get_the_company() ) { ?>
						<span class="company">
							<?php the_company_name( '<i class="far fa-building"></i>' ); ?>
						</span>
					<?php } ?>

					<span class="location">
						<i class="icon-location-pin"></i>
						<?php the_job_location( false ); ?>
					</span>

					<?php
					$rate_min = get_post_meta( $post->ID, '_rate_min', true );

					if ( $rate_min ) {
						$rate_max = get_post_meta( $post->ID, '_rate_max', true );
						?>

						<span class="rate">
							<i class="far fa-money-bill-alt"></i> 
							<?php cariera_job_rate(); ?>
						</span>
					<?php } ?>


					<?php
					$salary_min = get_post_meta( $post->ID, '_salary_min', true );
					if ( $salary_min ) {
						$salary_max = get_post_meta( $post->ID, '_salary_max', true );
						?>
						<span class="salary">
							<i class="far fa-money-bill-alt"></i>
							<?php cariera_job_salary(); ?>
						</span>
					<?php } ?>

					<?php do_action( 'job_listing_info_end' ); ?>
				</div>
			</div>

			<!-- Job Category -->
			<div class="job-content-meta">
				<ul class="meta">
					<?php do_action( 'job_listing_meta_start' ); ?>

					<?php
					if ( get_option( 'job_manager_enable_types' ) ) {
						$types = wpjm_get_the_job_types();
						if ( ! empty( $types ) ) {
							echo '<li class="job-type-wrapper">';
							?>

							<span class="job-type term-<?php echo esc_attr( $types[0]->term_id ); ?> <?php echo esc_attr( sanitize_title( $types[0]->slug ) ); ?>"><?php echo esc_html( $types[0]->name ); ?></span>

							<?php
							if ( cariera_newly_posted() ) {
								echo '<span class="job-item-badge new-job">' . esc_html__( 'New', 'cariera' ) . '</span>';
							}
							echo '</li>';
						}
					}
					?>

					<li class="date"><?php the_job_publish_date(); ?></li>

					<?php do_action( 'job_listing_meta_end' ); ?>
				</ul>
			</div>
		</div>
	</a>
</li>
