<?php
/**
 * Elementor Element: Job Search Box
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/job-searchbox.php.
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

<form class="job-search-form-box row ' . $settings['custom_class'] . '" method="get" action="' . esc_url( get_permalink( get_option( 'job_manager_jobs_page_id' ) ) ) . '">
	<div class="col-md-12 form-title">
		<h4 class="title"><?php echo esc_html( $settings['title'] ); ?></h4>
	</div>
	
	<div class="col-md-12 search-keywords">
		<label for="search-keywords"><?php esc_html_e( 'Keywords', 'cariera' ); ?></label>
		<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'Keywords', 'cariera' ); ?>" value="" autocomplete="off">
	</div>

	<?php if ( ! empty( $settings['location'] ) ) { ?>
		<div class="col-md-12 search-location mt15">
			<label for="search-location"><?php esc_html_e( 'Location', 'cariera' ); ?></label>
			<input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Location', 'cariera' ); ?>" value="">
			<div class="geolocation"><i class="geolocate"></i></div>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'Astoundify_Job_Manager_Regions' ) && ! empty( $settings['region'] ) ) { ?>
		<div class="col-md-12 search-region mt15">
			<label for="search_region"><?php esc_html_e( 'Region', 'cariera' ); ?></label>
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
	
	<?php if ( ! empty( $settings['categories'] ) ) { ?>
		<div class="col-md-12 search-categories mt15">
			<label for="search_categories"><?php esc_html_e( 'Categories', 'cariera' ); ?></label>

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
	<?php } ?>

	<div class="col-md-12 search-submit mt15 mb30">
		<button type="submit" class="btn btn-main btn-effect"><i class="fas fa-search"></i><?php esc_html_e( 'search', 'cariera' ); ?></button>
	</div>
</form>
