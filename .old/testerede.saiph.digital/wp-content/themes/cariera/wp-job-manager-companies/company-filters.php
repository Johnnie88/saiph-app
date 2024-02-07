<?php
/**
 * Custom: Company - Company Filters
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-filters.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.3.0
 * @version     1.6.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_script( 'company-ajax-filters' );
do_action( 'cariera_company_filters_before', $atts ); ?>

<form class="company_filters">
	<div class="search_companies">
		<?php do_action( 'cariera_company_search_filters_start', $atts ); ?>

		<div class="search_keywords">
			<label for="search_keywords"><?php echo esc_html__( 'Keywords', 'cariera' ); ?></label>
			<input type="text" id="search_keywords" class="search-field" placeholder="<?php echo esc_attr__( 'Keywords', 'cariera' ); ?>" value="<?php echo esc_attr( $keywords ); ?>" name="search_keywords" autocomplete="off" />
		</div>

		<div class="search_location">
			<label for="search_location"><?php echo esc_html__( 'Location', 'cariera' ); ?></label>
			<input type="text" id="search_location" class="location-search-field" placeholder="<?php echo esc_attr__( 'Locations', 'cariera' ); ?>" value="<?php echo esc_attr( $location ); ?>" name="search_location" />
			<div class="geolocation"><i class="geolocate"></i></div>
		</div>

		<?php if ( get_option( 'cariera_company_category' ) ) { ?>
			<div class="search_categories">
				<label for="search_categories"><?php echo esc_html__( 'Categories', 'cariera' ); ?></label>

				<?php
				job_manager_dropdown_categories(
					[
						'taxonomy'        => 'company_category',
						'hierarchical'    => 1,
						'show_option_all' => esc_html__( 'Any category', 'cariera' ),
						'name'            => 'search_categories',
						'class'           => 'cariera-select2-search',
						'orderby'         => 'name',
						'selected'        => $selected_category,
						'hide_empty'      => false,
						'multiple'        => false,
					]
				);
				?>
			</div>
		<?php } ?>

		<?php do_action( 'cariera_company_search_filters_end', $atts ); ?>
	</div>

	<div class="showing_companies"></div>
</form>

<?php do_action( 'cariera_company_filters_after', $atts ); ?>
