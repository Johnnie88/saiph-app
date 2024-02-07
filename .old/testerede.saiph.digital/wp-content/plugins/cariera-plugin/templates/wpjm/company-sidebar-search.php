<?php
/**
 * Company sidebar search template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/wpjm/company-sidebar-search.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'company-ajax-filters' );
?>

<form class="company_filters">
	<div class="search_companies">

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

		<?php if ( get_option( 'cariera_company_category' ) ) { ?>
			<div class="search_categories">
				<label for="search_categories"><?php echo esc_html__( 'Categories', 'cariera' ); ?></label>

				<?php
				if ( ! empty( $_GET['search_category'] ) ) {
					$selected_category = sanitize_text_field( wp_unslash( $_GET['search_category'] ) );
				} else {
					$selected_category = '';
				}

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
	</div>

	<div class="showing_companies"></div>
</form>
