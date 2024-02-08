<?php
/**
 * Custom: Company Listing - List Version 1
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-templates/content-company_list1.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.3.0
 * @version     1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

$company_class = 'company-list single_company_1';
$logo          = get_the_company_logo();
$featured      = get_post_meta( get_the_ID(), '_featured', true ) == 1 ? 'featured' : '';

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
} ?>

<li <?php cariera_company_class( esc_attr( $company_class ) ); ?> data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo esc_attr( get_the_ID() ); ?>" data-featured="<?php echo esc_attr( $featured ); ?>">
	<a href="<?php cariera_the_company_permalink(); ?>">
		<div class="company-inner">

			<div class="company-logo-wrapper">
				<div class="company-logo">
					<?php cariera_the_company_logo(); ?>
				</div>
			</div>

			<!-- Company Details -->
			<div class="company-details">
				<div class="company-title">
					<h5><?php the_title(); ?></h5>
				</div>

				<div class="company-meta">
					<?php
					// Action to add support for WPJM Field Editor.
					ob_start();
						do_action( 'cariera_company_listing_meta_start' );
						$company_listing_meta_start = ob_get_contents();
					ob_end_clean();

					if ( ! empty( $company_listing_meta_start ) ) {
						do_action( 'cariera_company_listing_meta_start' );
					}
					?>

					<?php if ( ! empty( cariera_get_the_company_location() ) ) { ?>
						<div class="company-location">
							<i class="icon-location-pin"></i>
							<span><?php echo cariera_get_the_company_location(); ?></span>
						</div>
					<?php } ?>

					<?php
					// Action to add support for WPJM Field Editor.
					ob_start();
						do_action( 'cariera_company_listing_meta_end' );
						$company_listing_meta_end = ob_get_contents();
					ob_end_clean();

					if ( ! empty( $company_listing_meta_end ) ) {
						do_action( 'cariera_company_listing_meta_end' );
					}
					?>
				</div>

				<div class="company-jobs">
					<span>
						<?php echo apply_filters( 'cariera_company_open_positions_info', esc_html( sprintf( _n( '%s Job', '%s Jobs', cariera_get_the_company_job_listing_active_count( $post->ID ), 'cariera' ), cariera_get_the_company_job_listing_active_count( $post->ID ) ) ) ); ?>
					</span>
				</div>
			</div>   
		</div>
	</a>
</li>
