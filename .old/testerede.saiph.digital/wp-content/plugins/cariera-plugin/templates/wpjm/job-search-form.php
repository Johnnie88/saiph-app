<?php
/**
 * Job search form template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/wpjm/job-search-from.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form method="GET" action="<?php echo esc_url( get_permalink( get_option( 'job_manager_jobs_page_id' ) ) ); ?>" class="job-search-form <?php echo esc_attr( $search_style ); ?>">
	<div class="search-keywords"><input type="text" id="search_keywords" name="search_keywords" placeholder="<?php esc_html_e( 'Keywords', 'cariera' ); ?>" autocomplete="off">
		<div class="search-results">
			<div class="search-loader"><span></span></div>
			<div class="job-listings cariera-scroll"></div>
		</div>
	</div>
	
	<?php if ( 'yes' === $location ) { ?>
		<div class="search-location">
			<input type="text" id="search_location" name="search_location" placeholder="<?php esc_html_e( 'Location', 'cariera' ); ?>">
			<div class="geolocation"><i class="geolocate"></i></div>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'Astoundify_Job_Manager_Regions' ) && 'yes' === $region ) { ?>
		<div class="search-region">
			<?php
			wp_dropdown_categories(
				apply_filters(
					'job_manager_regions_dropdown_args',
					[
						'show_option_all' => esc_html__( 'All Regions', 'cariera' ),
						'hierarchical'    => true,
						'orderby'         => 'name',
						'taxonomy'        => 'job_listing_region',
						'name'            => 'search_region',
						'class'           => 'search_region',
						'hide_empty'      => 0,
						'selected'        => isset( $atts['selected_region'] ) ? $atts['selected_region'] : '',
					]
				)
			);
			?>
		</div>
	<?php } ?>

	<?php if ( 'yes' === $categories ) { ?>
		<div class="search-categories">                    
			<?php
			cariera_job_manager_dropdown_category(
				[
					'taxonomy'        => 'job_listing_category',
					'hierarchical'    => 1,
					'name'            => 'search_category',
					'id'              => 'search_category',
					'orderby'         => 'name',
					'selected'        => '',
					'multiple'        => false,
					'show_option_all' => true,
				]
			);
			?>
		</div>
	<?php }	?>
	
	<div class="search-submit">
		<input type="submit" class="btn btn-main btn-effect" value="<?php esc_html_e( 'Search', 'cariera' ); ?>">
	</div>
</form>
