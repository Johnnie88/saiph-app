<?php
/**
 * Filters in `[jobs]` shortcode.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-filters.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.38.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_script( 'wp-job-manager-ajax-filters' );

do_action( 'job_manager_job_filters_before', $atts );
?>

<form class="job_filters">
	<?php do_action( 'job_manager_job_filters_start', $atts ); ?>

	<div class="search_jobs">
		<?php do_action( 'job_manager_job_filters_search_jobs_start', $atts ); ?>

		<div class="search_keywords">
			<label for="search_keywords"><?php esc_html_e( 'Keywords', 'cariera' ); ?></label>
			<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'Keywords', 'cariera' ); ?>" value="<?php echo esc_attr( $keywords ); ?>" />
		</div>

		<div class="search_location">
			<label for="search_location"><?php esc_html_e( 'Location', 'cariera' ); ?></label>
			<input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Location', 'cariera' ); ?>" value="<?php echo esc_attr( $location ); ?>" />
			<div class="geolocation"><i class="geolocate"></i></div>
		</div>

		<?php if ( apply_filters( 'job_manager_job_filters_show_remote_position', get_option( 'job_manager_enable_remote_position', true ), $atts ) ) : ?>
			<div class="search_remote_position checkbox">
				<input type="checkbox" class="input-checkbox" name="remote_position" id="remote_position" placeholder="<?php esc_attr_e( 'Location', 'cariera' ); ?>" value="1" <?php checked( ! empty( $remote_position ) ); ?> />
				<label for="remote_position" id="remote_position_label"><?php esc_html_e( 'Remote positions only', 'cariera' ); ?></label>
			</div>
		<?php endif; ?>

		<?php do_action( 'cariera_wpjm_job_filters_search_radius' ); ?>

		<!-- <div style="clear: both"></div> -->

		<?php if ( $categories ) : ?>
			<?php foreach ( $categories as $category ) : ?>
				<input type="hidden" name="search_categories[]" value="<?php echo esc_attr( sanitize_title( $category ) ); ?>" />
			<?php endforeach; ?>
		<?php elseif ( $show_categories && ! is_tax( 'job_listing_category' ) && get_terms( [ 'taxonomy' => 'job_listing_category' ] ) ) : ?>
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
		<?php endif; ?>

		<?php
		/**
		 * Action for the custom search fields
		 */
		do_action( 'job_manager_job_filters_search_jobs_end', $atts );
		?>


		<?php
		/**
		 * Show the submit button on the job filters form.
		 *
		 * @since 1.33.0
		 *
		 * @param bool $show_submit_button Whether to show the button. Defaults to true.
		 * @return bool
		 */
		if ( apply_filters( 'job_manager_job_filters_show_submit_button', true ) ) :
			?>
			<div class="search_submit">
				<input type="submit" class="btn btn-main" value="<?php esc_attr_e( 'Search Jobs', 'cariera' ); ?>">
			</div>
		<?php endif; ?>
	</div>

	<?php do_action( 'job_manager_job_filters_end', $atts ); ?>
</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>

<noscript><?php esc_html_e( 'Your browser does not support JavaScript, or it is disabled. JavaScript must be enabled in order to view listings.', 'cariera' ); ?></noscript>
