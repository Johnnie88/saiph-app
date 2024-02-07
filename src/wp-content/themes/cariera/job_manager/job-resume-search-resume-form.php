<?php
/**
 * Custom: Job Resume Tabs Search - Resume Form
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-resume-search-resume-form.php.
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

<form method="GET" action="<?php echo esc_url( get_permalink( get_option( 'resume_manager_resumes_page_id' ) ) ); ?>" class="resume-search-form">
	<div class="search-keywords">
		<label for="search_keywords_resumes"><?php esc_html_e( 'Keywords', 'cariera' ); ?></label>
		<input type="text" id="search_keywords_resumes" name="search_keywords" placeholder="<?php esc_attr_e( 'Keywords', 'cariera' ); ?>" autocomplete="off">
	</div>

	<div class="search-location">
		<label for="search_location_resumes"><?php esc_html_e( 'Location', 'cariera' ); ?></label>
		<input type="text" id="search_location_resumes" name="search_location" placeholder="<?php esc_attr_e( 'Location', 'cariera' ); ?>">
		<div class="geolocation"><i class="geolocate"></i></div>
	</div>

	<?php if ( get_option( 'resume_manager_enable_categories' ) ) { ?>
		<div class="search-categories">
			<label for="search_category_resumes"><?php esc_html_e( 'Category', 'cariera' ); ?></label>
			<?php
			cariera_job_manager_dropdown_category(
				[
					'taxonomy'        => 'resume_category',
					'hierarchical'    => 1,
					'name'            => 'search_category',
					'id'              => 'search_category_resumes',
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
