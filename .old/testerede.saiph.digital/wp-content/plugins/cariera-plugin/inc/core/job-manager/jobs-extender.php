<?php

namespace Cariera_Core\Core\Job_Manager;

use Cariera_Core\Core\Job_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Jobs_Extender extends Job_Manager {

	/**
	 * Construct
	 *
	 * @since   1.2.5
	 * @version 1.7.2
	 */
	public function __construct() {
		add_shortcode( 'jobs', [ $this, 'job_listings_output' ] );
		add_filter( 'job_manager_get_listings_result', [ $this, 'get_listings_result' ], 10, 2 );
	}

	/**
	 * String to bool function
	 *
	 * @since  1.2.5
	 */
	public function string_to_bool( $value ) {
		return ( is_bool( $value ) && $value ) || in_array( $value, [ '1', 'true', 'yes' ], true ) ? true : false;
	}

	/**
	 * [jobs] shortcode
	 *
	 * @since   1.2.5
	 * @version 1.7.0
	 */
	public function job_listings_output( $atts ) {
		wp_enqueue_style( 'cariera-job-listings' );

		ob_start();

		if ( ! job_manager_user_can_browse_job_listings() ) {
			get_job_manager_template_part( 'access-denied', 'browse-job_listings' );
			return ob_get_clean();
		}

		$atts = shortcode_atts(
			apply_filters(
				'job_manager_output_jobs_defaults',
				[
					// Custom Listing Layout.
					'jobs_layout'               => 'list',
					'jobs_list_version'         => '1',
					'jobs_grid_version'         => '1',
					'sidebar_search'            => '0',

					// Default WPJM.
					'per_page'                  => get_option( 'job_manager_per_page' ),
					'orderby'                   => 'featured',
					'order'                     => 'DESC',

					// Filters + cats.
					'show_filters'              => true,
					'show_categories'           => true,
					'show_category_multiselect' => get_option( 'job_manager_enable_default_category_multiselect', false ),
					'show_pagination'           => false,
					'show_more'                 => true,

					// Limit what jobs are shown based on category, post status, and type.
					'categories'                => '',
					'job_types'                 => '',
					'post_status'               => '',
					'featured'                  => null, // True to show only featured, false to hide featured, leave null to show both.
					'filled'                    => null, // True to show only filled, false to hide filled, leave null to show both/use the settings.
					'remote_position'           => null, // True to show only remote, false to hide remote, leave null to show both.

					// Default values for filters.
					'location'                  => '',
					'keywords'                  => '',
					'selected_category'         => '',
					'selected_job_types'        => implode( ',', array_values( get_job_listing_types( 'id=>slug' ) ) ),
				]
			),
			$atts
		);

		if ( ! get_option( 'job_manager_enable_categories' ) ) {
			$atts['show_categories'] = false;
		}

		// Job Layout.
		if ( $atts['jobs_layout'] === 'list' ) {
			$jobs_layout         = '_list';
			$jobs_layout_wrapper = 'job_list';
			$jobs_version        = $atts['jobs_list_version'];
		} else {
			$jobs_layout         = '_' . $atts['jobs_layout'];
			$jobs_layout_wrapper = 'job_grid';
			$jobs_version        = $atts['jobs_grid_version'];
		}

		// Custom sidebar search attr. This is used as a check when a sidebar search is used instead of main "show_filters".
		if ( ! is_null( $atts['sidebar_search'] ) ) {
			$atts['sidebar_search'] = ( is_bool( $atts['sidebar_search'] ) && $atts['sidebar_search'] ) || in_array( $atts['sidebar_search'], [ 1, '1', 'true', 'yes' ], true );
		}

		// String and bool handling.
		$atts['show_filters']              = $this->string_to_bool( $atts['show_filters'] );
		$atts['show_categories']           = $this->string_to_bool( $atts['show_categories'] );
		$atts['show_category_multiselect'] = $this->string_to_bool( $atts['show_category_multiselect'] );
		$atts['show_more']                 = $this->string_to_bool( $atts['show_more'] );
		$atts['show_pagination']           = $this->string_to_bool( $atts['show_pagination'] );

		if ( ! is_null( $atts['featured'] ) ) {
			$atts['featured'] = ( is_bool( $atts['featured'] ) && $atts['featured'] ) || in_array( $atts['featured'], [ 1, '1', 'true', 'yes' ], true );
		}

		if ( ! is_null( $atts['filled'] ) ) {
			$atts['filled'] = ( is_bool( $atts['filled'] ) && $atts['filled'] ) || in_array( $atts['filled'], [ 1, '1', 'true', 'yes' ], true );
		}

		if ( ! is_null( $atts['remote_position'] ) ) {
			$atts['remote_position'] = ( is_bool( $atts['remote_position'] ) && $atts['remote_position'] ) || in_array( $atts['remote_position'], [ 1, '1', 'true', 'yes' ], true );
		}

		// By default, use client-side state to populate form fields.
		$disable_client_state = false;

		// Get keywords, location, category and type from querystring if set.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Input is used safely.
		if ( ! empty( $_GET['search_keywords'] ) ) {
			$atts['keywords']     = sanitize_text_field( wp_unslash( $_GET['search_keywords'] ) );
			$disable_client_state = true;
		}
		if ( ! empty( $_GET['search_location'] ) ) {
			$atts['location']     = sanitize_text_field( wp_unslash( $_GET['search_location'] ) );
			$disable_client_state = true;
		}
		if ( ! empty( $_GET['search_category'] ) ) {
			$atts['selected_category'] = sanitize_text_field( wp_unslash( $_GET['search_category'] ) );
			$disable_client_state      = true;
		}
		if ( ! empty( $_GET['search_job_type'] ) ) {
			$atts['selected_job_types'] = sanitize_text_field( wp_unslash( $_GET['search_job_type'] ) );
			$disable_client_state       = true;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Array handling.
		$atts['categories']         = is_array( $atts['categories'] ) ? $atts['categories'] : array_filter( array_map( 'trim', explode( ',', $atts['categories'] ) ) );
		$atts['selected_category']  = is_array( $atts['selected_category'] ) ? $atts['selected_category'] : array_filter( array_map( 'trim', explode( ',', $atts['selected_category'] ) ) );
		$atts['job_types']          = is_array( $atts['job_types'] ) ? $atts['job_types'] : array_filter( array_map( 'trim', explode( ',', $atts['job_types'] ) ) );
		$atts['post_status']        = is_array( $atts['post_status'] ) ? $atts['post_status'] : array_filter( array_map( 'trim', explode( ',', $atts['post_status'] ) ) );
		$atts['selected_job_types'] = is_array( $atts['selected_job_types'] ) ? $atts['selected_job_types'] : array_filter( array_map( 'trim', explode( ',', $atts['selected_job_types'] ) ) );

		// Normalize field for categories.
		if ( ! empty( $atts['selected_category'] ) ) {
			foreach ( $atts['selected_category'] as $cat_index => $category ) {
				if ( ! is_numeric( $category ) ) {
					$term = get_term_by( 'slug', $category, 'job_listing_category' );

					if ( $term ) {
						$atts['selected_category'][ $cat_index ] = $term->term_id;
					}
				}
			}
		}

		$data_attributes = [
			'job_layout'                 => $jobs_layout,
			'job_version'                => $jobs_version,

			// Default.
			'location'                   => $atts['location'],
			'keywords'                   => $atts['keywords'],
			'show_filters'               => $atts['show_filters'] ? 'true' : 'false',
			'show_pagination'            => $atts['show_pagination'] ? 'true' : 'false',
			'per_page'                   => $atts['per_page'],
			'orderby'                    => $atts['orderby'],
			'order'                      => $atts['order'],
			'categories'                 => implode( ',', $atts['categories'] ),
			'disable-form-state-storage' => $disable_client_state,
		];

		if ( $atts['show_filters'] ) {
			get_job_manager_template(
				'job-filters.php',
				[
					'per_page'                  => $atts['per_page'],
					'orderby'                   => $atts['orderby'],
					'order'                     => $atts['order'],
					'show_categories'           => $atts['show_categories'],
					'categories'                => $atts['categories'],
					'selected_category'         => $atts['selected_category'],
					'job_types'                 => $atts['job_types'],
					'atts'                      => $atts,
					'location'                  => $atts['location'],
					'remote_position'           => $atts['remote_position'],
					'keywords'                  => $atts['keywords'],
					'selected_job_types'        => $atts['selected_job_types'],
					'show_category_multiselect' => $atts['show_category_multiselect'],
				]
			);

			echo '<ul class="job_listings job-listings-main ' . esc_attr( $jobs_layout_wrapper ) . ' row">';
			// get_job_manager_template( 'job-listings-start.php' );
			get_job_manager_template( 'job-listings-end.php' );

			if ( ! $atts['show_pagination'] && $atts['show_more'] ) {
				echo '<div class="col-md-12 text-center"><a class="load_more_jobs btn btn-main btn-effect mt40" href="#" style="display:none;">' . esc_html__( 'Load more listings', 'cariera' ) . '</a></div>';
			}
		} else {
			$jobs = get_job_listings(
				apply_filters(
					'job_manager_output_jobs_args',
					[
						'search_location'   => $atts['location'],
						'search_keywords'   => $atts['keywords'],
						'post_status'       => $atts['post_status'],
						'search_categories' => $atts['categories'],
						'job_types'         => $atts['job_types'],
						'orderby'           => $atts['orderby'],
						'order'             => $atts['order'],
						'posts_per_page'    => $atts['per_page'],
						'featured'          => $atts['featured'],
						'filled'            => $atts['filled'],
						'remote_position'   => $atts['remote_position'],
					]
				)
			);

			if ( ! empty( $atts['job_types'] ) ) {
				$data_attributes['job_types'] = implode( ',', $atts['job_types'] );
			}

			// Custom sidebar_search check added.
			if ( $jobs->have_posts() || 1 === absint( $atts['sidebar_search'] ) ) {

				echo '<ul class="job_listings job-listings-main ' . esc_attr( $jobs_layout_wrapper ) . ' row">';
				// get_job_manager_template( 'job-listings-start.php' );

				while ( $jobs->have_posts() ) {
					$jobs->the_post();
					get_job_manager_template_part( 'job-templates/content', 'job_listing' . esc_attr( $jobs_layout ) . esc_attr( $jobs_version ) );
				}

				get_job_manager_template( 'job-listings-end.php' );

				if ( $jobs->found_posts > $atts['per_page'] && $atts['show_more'] ) {
					wp_enqueue_script( 'wp-job-manager-ajax-filters' );
					if ( $atts['show_pagination'] ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template output.
						echo get_job_listing_pagination( $jobs->max_num_pages );
					} else {
						echo '<div class="col-md-12 text-center"><a class="load_more_jobs btn btn-main btn-effect" href="#">' . esc_html__( 'Load more listings', 'cariera' ) . '</a></div>';
					}
				}
			} else {
				do_action( 'job_manager_output_jobs_no_results' );
			}
			wp_reset_postdata();
		}

		$data_attributes_string = '';
		if ( ! is_null( $atts['featured'] ) ) {
			$data_attributes['featured'] = $atts['featured'] ? 'true' : 'false';
		}
		if ( ! is_null( $atts['filled'] ) ) {
			$data_attributes['filled'] = $atts['filled'] ? 'true' : 'false';
		}
		if ( ! is_null( $atts['remote_position'] ) ) {
			$data_attributes['remote_position'] = $atts['remote_position'] ? 'true' : 'false';
		}
		if ( ! empty( $atts['post_status'] ) ) {
			$data_attributes['post_status'] = implode( ',', $atts['post_status'] );
		}

		$data_attributes['post_id'] = isset( $GLOBALS['post'] ) ? $GLOBALS['post']->ID : 0;

		/**
		 * Pass additional data to the job listings <div> wrapper.
		 */
		$data_attributes = apply_filters( 'job_manager_jobs_shortcode_data_attributes', $data_attributes, $atts );

		foreach ( $data_attributes as $key => $value ) {
			$data_attributes_string .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
		}

		$job_listings_output = apply_filters( 'job_manager_job_listings_output', ob_get_clean() );

		return '<div class="job_listings" ' . $data_attributes_string . '>' . $job_listings_output . '</div>';
	}

	/**
	 * Get Listings Results
	 *
	 * @since   1.4.8
	 * @version 1.5.0
	 */
	public function get_listings_result( $result, $jobs ) {

		ob_start();

		// Pull Job attr via ajax file.
		$job_layout  = isset( $_POST['job_layout'] ) ? sanitize_text_field( $_POST['job_layout'] ) : '';
		$job_version = isset( $_POST['job_version'] ) ? sanitize_text_field( $_POST['job_version'] ) : '';

		// In case S&F for WPJM is activated.
		$job_layout  = isset( $_POST['data_params'], $_POST['data_params']['job_layout'] ) ? sanitize_text_field( $_POST['data_params']['job_layout'] ) : $job_layout;
		$job_version = isset( $_POST['data_params'], $_POST['data_params']['job_version'] ) ? sanitize_text_field( $_POST['data_params']['job_version'] ) : $job_version;

		/*
		 * Show jobs with the right template
		 */
		if ( $result['found_jobs'] ) {
			while ( $jobs->have_posts() ) {
				$jobs->the_post();
				get_job_manager_template_part( 'job-templates/content', 'job_listing' . $job_layout . $job_version );
			}
		} else {
			get_job_manager_template_part( 'content', 'no-jobs-found' );
		}

		$result['html'] = ob_get_clean();

		return $result;
	}
}
