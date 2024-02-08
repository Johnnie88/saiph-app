<?php
/**
 * Cariera Dashboard - Cards template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/dashboard/cards.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     1.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();

if ( in_array( 'administrator', (array) $current_user->roles, true ) ) {
	$listing_name = esc_html__( 'Listings', 'cariera' );

	// Jobs.
	$active_jobs  = cariera_count_user_posts_by_status( $current_user->ID, 'job_listing', 'publish' );
	$pending_jobs = cariera_count_user_posts_by_status( $current_user->ID, 'job_listing', 'pending' );
	$expired_jobs = cariera_count_user_posts_by_status( $current_user->ID, 'job_listing', 'expired' );
	// Resumes.
	$active_resumes  = cariera_count_user_posts_by_status( $current_user->ID, 'resume', 'publish' );
	$pending_resumes = cariera_count_user_posts_by_status( $current_user->ID, 'resume', 'pending' );
	$expired_resumes = cariera_count_user_posts_by_status( $current_user->ID, 'resume', 'expired' );
	// All listings together.
	$active_listings  = $active_jobs + $active_resumes;
	$pending_listings = $pending_jobs + $pending_resumes;
	$expired_listings = $expired_jobs + $expired_resumes;
} elseif ( in_array( 'employer', (array) $current_user->roles, true ) ) {
	$listing_name = esc_html__( 'Listings', 'cariera' );

	$active_listings  = cariera_count_user_posts_by_status( $current_user->ID, 'job_listing', 'publish' );
	$pending_listings = cariera_count_user_posts_by_status( $current_user->ID, 'job_listing', 'pending' );
	$expired_listings = cariera_count_user_posts_by_status( $current_user->ID, 'job_listing', 'expired' );
} elseif ( in_array( 'candidate', (array) $current_user->roles, true ) ) {
	$listing_name = esc_html__( 'Resumes', 'cariera' );

	$active_listings  = cariera_count_user_posts_by_status( $current_user->ID, 'resume', 'publish' );
	$pending_listings = cariera_count_user_posts_by_status( $current_user->ID, 'resume', 'pending' );
	$expired_listings = cariera_count_user_posts_by_status( $current_user->ID, 'resume', 'expired' );
} else {
	return;
} ?>

<div class="row">
	<!-- Stat Item -->
	<div class="col-lg-3 col-md-6 dashboard-widget published-listings">
		<div class="card-statistics style-1">
			<div class="statistics-content">
				<h4><?php echo esc_html( $active_listings ); ?></h4>
				<span><?php printf( esc_html__( 'Published %s', 'cariera' ), $listing_name ); ?></span>
			</div>
			<div class="statistics-icon">
				<i class="icon-check"></i>
			</div>
		</div>
	</div>

	<!-- Stat Item -->
	<div class="col-lg-3 col-md-6 dashboard-widget pending-listings">
		<div class="card-statistics style-2">
			<div class="statistics-content">
				<h4><?php echo esc_html( $pending_listings ); ?></h4>
				<span><?php printf( esc_html__( 'Pending %s', 'cariera' ), $listing_name ); ?></span>
			</div>
			<div class="statistics-icon">
				<i class="icon-pencil"></i>
			</div>
		</div>
	</div>

	<!-- Stat Item -->
	<div class="col-lg-3 col-md-6 dashboard-widget expired-listings">
		<div class="card-statistics style-3">
			<div class="statistics-content">
				<h4><?php echo esc_html( $expired_listings ); ?></h4>
				<span><?php printf( esc_html__( 'Expired %s', 'cariera' ), $listing_name ); ?></span>
			</div>
			<div class="statistics-icon">
				<i class="icon-clock"></i>
			</div>
		</div>
	</div>

	<!-- Stat Item -->
	<div class="col-lg-3 col-md-6 dashboard-widget monthly-views-stats">
		<div class="card-statistics style-4">
			<div class="statistics-content">
				<h4><?php echo esc_html( '0' ); ?></h4>
				<span><?php esc_html_e( 'Monthly Views', 'cariera' ); ?></span>
			</div>
			<div class="statistics-icon">
				<i class="icon-eye"></i>
			</div>
		</div>
	</div>
</div>
