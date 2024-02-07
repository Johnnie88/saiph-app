<?php

namespace Cariera_Core\Core\Job_Manager;

use Cariera_Core\Core\Job_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Search extends Job_Manager {

	/**
	 * Constructor
	 *
	 * @since 1.4.5
	 */
	public function __construct() {
		add_action( 'job_manager_job_filters_search_jobs_end', [ $this, 'advanced_search_start' ], 1 );
		add_action( 'job_manager_job_filters_search_jobs_end', [ $this, 'advanced_search_end' ], 10 );

		add_action( 'cariera_wpjm_job_filters_search_radius', [ $this, 'search_by_radius_fields' ], 2 );
		add_filter( 'job_manager_get_listings', [ $this, 'search_by_radius_query' ], 10, 2 );

		add_action( 'job_manager_job_filters_search_jobs_end', [ $this, 'search_by_salary_fields' ], 2 );
		add_action( 'cariera_wpjm_sidebar_job_filters_search_jobs_end', [ $this, 'search_by_salary_fields' ], 2 );
		add_filter( 'job_manager_get_listings', [ $this, 'search_by_salary_query' ], 10, 2 );

		add_action( 'job_manager_job_filters_search_jobs_end', [ $this, 'search_by_rate_fields' ], 2 );
		add_action( 'cariera_wpjm_sidebar_job_filters_search_jobs_end', [ $this, 'search_by_rate_fields' ], 2 );
		add_filter( 'job_manager_get_listings', [ $this, 'search_by_rate_query' ], 10, 2 );
	}

	/**
	 * Extra job search fields wrapper
	 *
	 * @since 1.3.6
	 */
	public function advanced_search_start() {
		if ( ! get_option( 'cariera_enable_filter_salary' ) && ! get_option( 'cariera_enable_filter_rate' ) ) {
			return;
		}

		echo '<div class="advanced-search-btn"><a href="#" id="advance-search">' . esc_html__( 'Advanced Search', 'cariera' ) . '</a></div>';
		echo '<div class="advanced-search-filters">';
	}

	public function advanced_search_end() {
		if ( ! get_option( 'cariera_enable_filter_salary' ) && ! get_option( 'cariera_enable_filter_rate' ) ) {
			return;
		}

		echo '</div>';
	}

	/**
	 * Custom search by salary field for the Job search
	 *
	 * @since 1.3.6
	 */
	public function search_by_salary_fields() {
		if ( get_option( 'cariera_enable_filter_salary' ) ) { ?>
			<div class="search_salary_min">
				<label for="search_salary_min"><?php esc_html_e( 'Minimum Salary', 'cariera' ); ?></label>
				<input type="text" class="job-manager-filter" name="search_salary_min" placeholder="<?php esc_attr_e( 'Search Salary Min', 'cariera' ); ?>">
			</div>

			<div class="search_salary_max">
				<label for="search_salary_max"><?php esc_html_e( 'Maximum Salary', 'cariera' ); ?></label>
				<input type="text" class="job-manager-filter" name="search_salary_max" placeholder="<?php esc_attr_e( 'Search Salary Max', 'cariera' ); ?>">
			</div>
			<?php
		}
	}

	/**
	 * Modifying the job search query.
	 *
	 * @since 1.3.6
	 */
	public function search_by_salary_query( $query_args, $args ) {
		if ( isset( $_POST['form_data'] ) ) {
			parse_str( $_POST['form_data'], $form_data );

			// If this is set, we are filtering by salary min.
			if ( ! empty( $form_data['search_salary_min'] ) ) {
				$salary_min = sanitize_text_field( $form_data['search_salary_min'] );

				$query_args['meta_query'][] = [
					'key'     => '_salary_min',
					'value'   => $salary_min,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				];

				// This will show the 'reset' link.
				add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
			}

			// If this is set, we are filtering by salary max.
			if ( ! empty( $form_data['search_salary_max'] ) ) {
				$salary_max = sanitize_text_field( $form_data['search_salary_max'] );

				$query_args['meta_query'][] = [
					'key'     => '_salary_max',
					'value'   => $salary_max,
					'compare' => '<=',
					'type'    => 'NUMERIC',
				];

				// This will show the 'reset' link.
				add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
			}
		}

		return $query_args;
	}

	/**
	 * Custom search by rate field for the Job search
	 *
	 * @since 1.3.6
	 */
	public function search_by_rate_fields() {
		if ( get_option( 'cariera_enable_filter_rate' ) ) {
			?>
			<div class="search_rate_min">
				<label for="search_rate_min"><?php esc_html_e( 'Minimum Rate', 'cariera' ); ?></label>
				<input type="text" class="job-manager-filter" name="search_rate_min" placeholder="<?php esc_attr_e( 'Search Rate Min', 'cariera' ); ?>">
			</div>

			<div class="search_rate_max">
				<label for="search_rate_max"><?php esc_html_e( 'Maximum Rate', 'cariera' ); ?></label>
				<input type="text" class="job-manager-filter" name="search_rate_max" placeholder="<?php esc_attr_e( 'Search Rate Max', 'cariera' ); ?>">
			</div>
			<?php
		}
	}

	/**
	 * Modifying the job search query.
	 *
	 * @since 1.3.6
	 */
	public function search_by_rate_query( $query_args, $args ) {
		if ( isset( $_POST['form_data'] ) ) {
			parse_str( $_POST['form_data'], $form_data );

			// If this is set, we are filtering by salary min.
			if ( ! empty( $form_data['search_rate_min'] ) ) {
				$rate_min = sanitize_text_field( $form_data['search_rate_min'] );

				$query_args['meta_query'][] = [
					'key'     => '_rate_min',
					'value'   => $rate_min,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				];

				// This will show the 'reset' link.
				add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
			}

			// If this is set, we are filtering by salary max.
			if ( ! empty( $form_data['search_rate_max'] ) ) {
				$rate_max = sanitize_text_field( $form_data['search_rate_max'] );

				$query_args['meta_query'][] = [
					'key'     => '_rate_max',
					'value'   => $rate_max,
					'compare' => '<=',
					'type'    => 'NUMERIC',
				];

				// This will show the 'reset' link.
				add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
			}
		}

		return $query_args;
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
	 * Modifying the job search query.
	 *
	 * @since 1.4.3
	 */
	public function search_by_radius_query( $query_args, $args ) {
		if ( isset( $_POST['form_data'] ) ) {
			parse_str( $_POST['form_data'], $form_data );

			// If this is set, we are filtering by radius.
			if ( isset( $form_data['search_location'] ) && isset( $form_data['search_radius'] ) && ! empty( $form_data['search_radius'] ) ) {

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
					add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
				}
			} else {
				add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
			}
		}

		return $query_args;
	}
}
