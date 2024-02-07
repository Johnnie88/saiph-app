<?php
/**
 * Filter form to display above `[resumes]` shortcode.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/resume-filters.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Resume Manager
 * @category    Template
 * @version     1.13.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'wp-resume-manager-ajax-filters' );
do_action( 'resume_manager_resume_filters_before', $atts );
?>

<form class="resume_filters">

	<div class="search_resumes">
		<?php do_action( 'resume_manager_resume_filters_search_resumes_start', $atts ); ?>

		<div class="search_keywords resume-filter">
			<label for="search_keywords"><?php esc_attr_e( 'Keywords', 'cariera' ); ?></label>
			<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'All Resumes', 'cariera' ); ?>" value="<?php echo esc_attr( $keywords ); ?>" autocomplete="off" />
		</div>

		<div class="search_location resume-filter">
			<label for="search_location"><?php esc_attr_e( 'Location', 'cariera' ); ?></label>
			<input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Any Location', 'cariera' ); ?>" value="<?php echo esc_attr( $location ); ?>" />
			<div class="geolocation"><i class="geolocate"></i></div>
		</div>

		<?php do_action( 'cariera_wprm_job_filters_search_radius' ); ?>

		<?php if ( $categories ) : ?>
			<?php foreach ( $categories as $category ) : ?>
				<input type="hidden" name="search_categories[]" value="<?php echo sanitize_title( $category ); ?>" />
			<?php endforeach; ?>
		<?php elseif ( $show_categories && get_option( 'resume_manager_enable_categories' ) && ! is_tax( 'resume_category' ) && get_terms( 'resume_category' ) ) : ?>
			<div class="search_categories resume-filter">
				<label for="search_categories"><?php esc_attr_e( 'Category', 'cariera' ); ?></label>
				<?php if ( $show_category_multiselect ) : ?>
					<?php
					job_manager_dropdown_categories(
						[
							'taxonomy'     => 'resume_category',
							'hierarchical' => 1,
							'name'         => 'search_categories',
							'orderby'      => 'name',
							'selected'     => $selected_category,
							'hide_empty'   => false,
						]
					);
					?>
				<?php else : ?>
					<?php
					wp_dropdown_categories(
						[
							'taxonomy'        => 'resume_category',
							'hierarchical'    => 1,
							'show_option_all' => esc_html__( 'Any category', 'cariera' ),
							'name'            => 'search_categories',
							'orderby'         => 'name',
							'selected'        => $selected_category,
						]
					);
					?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'resume_manager_resume_filters_search_resumes_end', $atts ); ?>
	</div>
	<div class="showing_resumes"></div>
</form>

<?php do_action( 'resume_manager_resume_filters_after', $atts ); ?>
