<?php

namespace Cariera_Core\Core\Resume_Manager;

use Cariera_Core\Core\Resume_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Search extends Resume_Manager {

	/**
	 * Constructor
	 *
	 * @since 1.4.5
	 */
	public function __construct() {
		add_action( 'cariera_wprm_job_filters_search_radius', [ $this, 'search_by_radius_fields' ], 2 );
		add_filter( 'resume_manager_get_resumes', [ $this, 'search_by_radius_query' ], 10, 2 );

		add_action( 'resume_manager_resume_filters_search_resumes_end', [ $this, 'search_skills_field' ] );
		add_action( 'cariera_wprm_sidebar_job_filters_search_jobs_end', [ $this, 'search_skills_field' ] );
		add_filter( 'resume_manager_get_resumes', [ $this, 'search_skills_query' ], 10, 2 );

		add_action( 'resume_manager_resume_filters_search_resumes_end', [ $this, 'search_rate_field' ] );
		add_action( 'cariera_wprm_sidebar_job_filters_search_jobs_end', [ $this, 'search_rate_field' ] );
		add_filter( 'resume_manager_get_resumes', [ $this, 'search_rate_query' ], 10, 2 );
	}

	/**
	 * Custom search by location radius for the Job search
	 *
	 * @since 1.4.3
	 */
	public function search_by_radius_fields() {
		$search_radius = cariera_get_option( 'cariera_search_radius' );
		if ( $search_radius ) {
			?>

			<div class="search_radius" >
				<div class="range-slider">
					<input name="search_radius" id="search_radius" class="distance-radius" type="range" min="1" max="<?php echo cariera_get_option( 'cariera_max_radius_search_value' ); ?>" step="1" value="<?php cariera_get_option( 'cariera_radius_unit' ); ?>" data-title="<?php echo esc_html__( 'Radius around selected location.', 'cariera' ); ?>">
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Custom search by location radius for the Resume search
	 *
	 * @since 1.4.3
	 */
	public function search_by_radius_query( $query_args, $args ) {
		if ( isset( $_POST['form_data'] ) ) {
			parse_str( $_POST['form_data'], $form_data );

			// If this is set, we are filtering by radius.
			if ( isset( $form_data['search_radius'] ) && ! empty( $form_data['search_radius'] ) ) {

				if ( ! empty( $form_data['search_radius'] ) ) {
					$address     = $form_data['search_location'];
					$radius      = $form_data['search_radius'];
					$radius_type = cariera_get_option( 'cariera_radius_unit' );

					if ( ! empty( $address ) ) {
						$latlng      = cariera_geocode( $address );
						$nearbyposts = cariera_get_nearby_listings( $latlng[0], $latlng[1], $radius, $radius_type );
						cariera_array_sort_by_column( $nearbyposts, 'distance' );

						$ids = array_unique( array_column( $nearbyposts, 'post_id' ) );
						if ( ! empty( $ids ) ) {
							$query_args['post__in'] = $ids;
							unset( $query_args['meta_query'][0] );
						}
					}

					// This will show the 'reset' link.
					add_filter( 'resume_manager_get_resumes_custom_filter', '__return_true' );
				}
			} else {
				add_filter( 'resume_manager_get_resumes_custom_filter', '__return_true' );
			}
		}

		return $query_args;
	}

	/**
	 * Custom search by skills field for the Resume search
	 *
	 * @since 1.3.6
	 */
	public function search_skills_field() {

		if ( get_option( 'resume_manager_enable_skills' ) ) {

			if ( ! empty( $_GET['search_skills'] ) ) {
				$selected_skills = sanitize_text_field( $_GET['search_skills'] );
			} else {
				$selected_skills = '';
			}

			if ( ! is_tax( 'resume_skill' ) && get_terms( 'resume_skill' ) ) {
				?>
				<div class="search_skills resume-filter">
					<label for="search_skills"><?php esc_html_e( 'Filter by Skills', 'cariera' ); ?></label>
					<?php
					job_manager_dropdown_categories(
						[
							'taxonomy'     => 'resume_skill',
							'hierarchical' => 1,
							'name'         => 'search_skills',
							'orderby'      => 'name',
							'selected'     => $selected_skills,
							'hide_empty'   => false,
							'class'        => 'cariera-select2',
							'id'           => 'search_skills',
							'placeholder'  => esc_html__( 'Choose a skill', 'cariera' ),
						]
					);
					?>
				</div>
				<?php
			}
		}
	}

	/**
	 * Modifying the resume search query.
	 *
	 * @since 1.3.6
	 */
	public function search_skills_query( $query_args, $args ) {
		if ( isset( $_POST['form_data'] ) ) {
			parse_str( $_POST['form_data'], $form_data );

			// If this is set, we are filtering by salary.
			if ( isset( $form_data['search_skills'] ) ) {
				if ( ! empty( $form_data['search_skills'] ) ) {

					$field    = is_numeric( $form_data['search_skills'][0] ) ? 'term_id' : 'slug';
					$operator = 'all' === count( $form_data['search_skills'] ) > 1 ? 'AND' : 'IN';

					$query_args['tax_query'][] = [
						'taxonomy'         => 'resume_skill',
						'field'            => $field,
						'terms'            => array_values( $form_data['search_skills'] ),
						'include_children' => $operator !== 'AND',
						'operator'         => $operator,
					];

					// This will show the 'reset' link.
					add_filter( 'resume_manager_get_resumes_custom_filter', '__return_true' );
				}
			}
		}

		return $query_args;
	}

	/**
	 * Custom search by rate field for the Resume search
	 *
	 * @since 1.3.6
	 */
	public function search_rate_field() {
		?>
		<div class="search_by_rate resume-filter">
			<label for="search_by_rate"><?php esc_html_e( 'Minimum Rate', 'cariera' ); ?></label>
			<input type="text" name="search_by_rate" id="search_by_rate"  placeholder="<?php esc_attr_e( 'Search by minimum rate', 'cariera' ); ?>">
		</div>
		<?php
	}

	/**
	 * Modifying the resume search query.
	 *
	 * @since 1.3.6
	 */
	public function search_rate_query( $query_args, $args ) {
		if ( isset( $_POST['form_data'] ) ) {
			parse_str( $_POST['form_data'], $form_data );

			if ( ! empty( $form_data['search_by_rate'] ) ) {
				$rate = sanitize_text_field( $form_data['search_by_rate'] );

				$query_args['meta_query'][] = [
					'key'     => '_rate',
					'value'   => $rate,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				];

				// This will show the 'reset' link.
				add_filter( 'resume_manager_get_resumes_custom_filter', '__return_true' );
			}
		}

		return $query_args;
	}
}
