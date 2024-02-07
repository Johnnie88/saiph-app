<?php
/**
 * Resume sidebar search template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/wpjm/resume-sidebar-search.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'wp-resume-manager-ajax-filters' );
?>

<form class="resume_filters">
	<div class="search_resumes">

		<div class="search_keywords resume-filter">
			<?php
			if ( ! empty( $_GET['search_keywords'] ) ) {
				$keywords = sanitize_text_field( wp_unslash( $_GET['search_keywords'] ) );
			} else {
				$keywords = '';
			}
			?>
			<label for="search_keywords"><?php esc_html_e( 'Keywords', 'cariera' ); ?></label>
			<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'All Resumes', 'cariera' ); ?>" value="<?php echo esc_attr( $keywords ); ?>" />
		</div>

		<div class="search_location resume-filter">
			<?php
			if ( ! empty( $_GET['search_location'] ) ) {
				$location = sanitize_text_field( wp_unslash( $_GET['search_location'] ) );
			} else {
				$location = '';
			}
			?>
			<label for="search_location"><?php esc_html_e( 'Location', 'cariera' ); ?></label>
			<input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Any Location', 'cariera' ); ?>" value="<?php echo esc_attr( $location ); ?>" />
			<div class="geolocation"><i class="geolocate"></i></div>
		</div>

		<?php do_action( 'cariera_wprm_job_filters_search_radius' ); ?>

		<?php
		if ( get_option( 'resume_manager_enable_categories' ) && ! is_tax( 'resumes_category' ) && get_terms( [ 'taxonomy' => 'resumes_category' ] ) ) {
			$show_category_multiselect = get_option( 'resume_manager_enable_default_category_multiselect', false );

			if ( ! empty( $_GET['search_category'] ) ) {
				$selected_category = sanitize_text_field( wp_unslash( $_GET['search_category'] ) );
			} else {
				$selected_category = '';
			}
			?>

			<div class="search_categories resume-filter">
				<label for="search_categories"><?php esc_html_e( 'Category', 'cariera' ); ?></label>
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
					job_manager_dropdown_categories(
						[
							'taxonomy'        => 'resume_category',
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
				<?php endif; ?>
			</div>
			<?php
		}

		do_action( 'cariera_wprm_sidebar_job_filters_search_jobs_end' );
		?>
	</div>

	<div class="showing_resumes"></div>
</form>
