<?php
/**
 * Job sidebar search template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/wpjm/job-sidebar-search.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'wp-job-manager-ajax-filters' );
?>

<form class="job_filters">
	<div class="search_jobs">

		<div class="search_keywords">
			<?php
			if ( ! empty( $_GET['search_keywords'] ) ) {
				$keywords = sanitize_text_field( wp_unslash( $_GET['search_keywords'] ) );
			} else {
				$keywords = '';
			}
			?>
			<label for="search_keywords"><?php esc_html_e( 'Keywords', 'cariera' ); ?></label>
			<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'Keywords', 'cariera' ); ?>" value="<?php echo esc_attr( $keywords ); ?>" />
		</div>


		<div class="search_location">
			<?php
			if ( ! empty( $_GET['search_location'] ) ) {
				$location = sanitize_text_field( wp_unslash( $_GET['search_location'] ) );
			} else {
				$location = '';
			}
			?>
			<label for="search_location"><?php esc_html_e( 'Location', 'cariera' ); ?></label>
			<input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Location', 'cariera' ); ?>" value="<?php echo esc_attr( $location ); ?>" />
			<div class="geolocation"><i class="geolocate"></i></div>
		</div>

		<?php if ( apply_filters( 'job_manager_job_filters_show_remote_position', get_option( 'job_manager_enable_remote_position', true ) ) ) : ?>
			<div class="search_remote_position checkbox">
				<input type="checkbox" class="input-checkbox" name="remote_position" id="remote_position" placeholder="<?php esc_attr_e( 'Location', 'cariera' ); ?>" value="1" <?php checked( ! empty( $remote_position ) ); ?> />
				<label for="remote_position" id="remote_position_label"><?php esc_html_e( 'Remote positions only', 'cariera' ); ?></label>
			</div>
		<?php endif; ?>

		<?php do_action( 'cariera_wpjm_job_filters_search_radius' ); ?>

		<?php
		if ( ! is_tax( 'job_listing_category' ) && get_terms( [ 'taxonomy' => 'job_listing_category' ] ) ) {
			$show_category_multiselect = get_option( 'job_manager_enable_default_category_multiselect', false );

			if ( ! empty( $_GET['search_category'] ) ) {
				$selected_category = sanitize_text_field( wp_unslash( $_GET['search_category'] ) );
			} else {
				$selected_category = '';
			}
			?>

			<div class="search_categories">
				<label for="search_categories"><?php esc_html_e( 'Category', 'cariera' ); ?></label>
				<?php if ( $show_category_multiselect ) : ?>
					<?php
					job_manager_dropdown_categories(
						[
							'taxonomy'     => 'job_listing_category',
							'hierarchical' => 1,
							'name'         => 'search_categories',
							'orderby'      => 'name',
							'selected'     => $selected_category,
							'hide_empty'   => false,
							'show_count'   => 0,
							'class'        => 'cariera-select2-search',
						]
					);
					?>
				<?php else : ?>
					<?php
					job_manager_dropdown_categories(
						[
							'taxonomy'        => 'job_listing_category',
							'hierarchical'    => 1,
							'show_option_all' => esc_html__( 'Any category', 'cariera' ),
							'name'            => 'search_categories',
							'orderby'         => 'name',
							'selected'        => $selected_category,
							'multiple'        => false,
							'hide_empty'      => false,
							'show_count'      => 0,
							'class'           => 'cariera-select2-search',
						]
					);
					?>
				<?php endif; ?>
			</div>
		<?php } ?>

		<?php do_action( 'cariera_wpjm_sidebar_job_filters_search_jobs_end' ); ?>

		<?php if ( get_option( 'job_manager_enable_types' ) ) { ?>
			<div class="search-job-types">
				<?php if ( ! is_tax( 'job_listing_type' ) ) { ?>
					<label><?php esc_html_e( 'Job Type', 'cariera' ); ?></label>
					<?php
				}

				$selected_job_types = implode( ',', array_values( get_job_listing_types( 'id=>slug' ) ) );

				get_job_manager_template(
					'job-filter-job-types.php',
					[
						'job_types'          => '',
						'atts'               => '',
						'selected_job_types' => is_array( $selected_job_types ) ? $selected_job_types : array_filter( array_map( 'trim', explode( ',', $selected_job_types ) ) ),
					]
				);
			?>
			</div>
		<?php } ?>

	</div>

	<div class="showing_jobs"></div>
</form>
