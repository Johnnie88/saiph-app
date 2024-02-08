<?php
/**
 * Custom: Job Resume Tabs Search - Job Form
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-resume-search-job-form.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.6.2
 * @version     1.6.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form method="GET" action="<?php echo esc_url( get_permalink( get_option( 'job_manager_jobs_page_id' ) ) ); ?>" class="job-search-form">
	<div class="search-keywords">
		<label for="search_keywords_jobs"><?php esc_html_e( 'Keywords', 'cariera' ); ?></label>
		<input type="text" id="search_keywords_jobs" name="search_keywords" placeholder="<?php esc_attr_e( 'Keywords', 'cariera' ); ?>" autocomplete="off">
		<div class="search-results"><div class="search-loader"><span></span></div><div class="job-listings cariera-scroll"></div></div>
	</div>

	<div class="search-location">
		<label for="search_location_jobs"><?php esc_html_e( 'Location', 'cariera' ); ?></label>
		<input type="text" id="search_location_jobs" name="search_location" placeholder="<?php esc_attr_e( 'Location', 'cariera' ); ?>">
		<div class="geolocation"><i class="geolocate"></i></div>
	</div>

	<?php if ( get_option( 'job_manager_enable_categories' ) ) { ?>
		<div class="search-categories">
			<label for="search_category_jobs"><?php esc_html_e( 'Category', 'cariera' ); ?></label>
			<?php
			cariera_job_manager_dropdown_category(
				[
					'taxonomy'        => 'job_listing_category',
					'hierarchical'    => 1,
					'name'            => 'search_category',
					'id'              => 'search_category_jobs',
					'orderby'         => 'name',
					'selected'        => '',
					'multiple'        => false,
					'show_option_all' => true,
				]
			);
			?>
		</div>
	<?php } ?>

	<div class="search-submit">
		<label><?php esc_html_e( 'Button', 'cariera' ); ?></label>
		<input type="submit" class="btn btn-main btn-effect" value="<?php esc_attr_e( 'Search', 'cariera' ); ?>">
	</div>
</form>
