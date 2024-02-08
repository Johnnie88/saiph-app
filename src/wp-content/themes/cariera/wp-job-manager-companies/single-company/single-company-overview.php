<?php
/**
 * Custom: Single Company - Company Overview
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/single-company/single-company-overview.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.6
 * @version     1.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

$active_job_listings = cariera_get_the_company_job_listing_active_count( $post->ID );
?>

<h5><?php esc_html_e( 'Company Overview', 'cariera' ); ?></h5>
<aside class="widget widget-company-overview">

	<?php do_action( 'cariera_single_company_meta_start' ); ?>

	<div class="single-company-overview-detail single-company-overview-jobs-posted">
		<div class="icon">
			<i class="icon-check"></i>
		</div>

		<div class="content">
			<h6><?php esc_html_e( 'Posted Jobs', 'cariera' ); ?></h6>
			<span>
				<?php echo apply_filters( 'cariera_company_open_positions_info', esc_html( sprintf( _n( '%s Job', '%s Jobs', $active_job_listings, 'cariera' ), $active_job_listings ) ) ); ?>
			</span>
		</div>
	</div>

	<?php if ( ! empty( cariera_get_the_company_location() ) ) { ?>
		<div class="single-company-overview-detail single-company-overview-location">
			<div class="icon">
				<i class="icon-location-pin"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Location', 'cariera' ); ?></h6>
				<span><?php cariera_the_company_location_output(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( ! empty( cariera_get_the_company_category() ) && get_option( 'cariera_company_category' ) ) { ?>
		<div class="single-company-overview-detail single-company-overview-category">
			<div class="icon">
				<i class="icon-star"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Category', 'cariera' ); ?></h6>
				<span><?php cariera_the_company_category_output(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( ! empty( cariera_get_company_since() ) ) { ?>
		<div class="single-company-overview-detail single-company-overview-since">
			<div class="icon">
				<i class="icon-clock"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Since', 'cariera' ); ?></h6>
				<span><?php echo cariera_get_company_since(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( ! empty( cariera_get_the_company_team_size() ) && get_option( 'cariera_company_team_size' ) ) { ?>
		<div class="single-company-overview-detail single-company-overview-team-size">
			<div class="icon">
				<i class="icon-people"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Team Size', 'cariera' ); ?></h6>
				<span><?php cariera_the_company_team_size_output(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php do_action( 'cariera_single_company_meta_end' ); ?>
</aside>
