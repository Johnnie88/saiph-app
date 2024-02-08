<?php

namespace Cariera_Core\Core\Resume_Manager;

use Cariera_Core\Core\Resume_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Resumes_Extender extends Resume_Manager {

	/**
	 * Constructor
	 *
	 * @since 1.4.5
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'resume_shortcodes' ], 20 );
		add_filter( 'resume_manager_get_listings_result', [ $this, 'get_listings_result' ], 15, 2 );
	}

	/**
	 * Resumes shortcode function
	 *
	 * @since  1.4.5
	 */
	public function resume_shortcodes() {
		// remove_shortcode( 'resumes' );
		add_shortcode( 'resumes', [ $this, 'output_resumes' ] );
	}

	/**
	 * Resumes shortcode
	 *
	 * @since   1.4.5
	 * @version 1.7.0
	 */
	public function output_resumes( $atts ) {
		global $resume_manager;

		wp_enqueue_style( 'cariera-resume-listings' );

		ob_start();

		if ( ! resume_manager_user_can_browse_resumes() ) {
			get_job_manager_template_part( 'access-denied', 'browse-resumes', 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' );
			return ob_get_clean();
		}

		extract(
			$atts = shortcode_atts(
				apply_filters(
					'resume_manager_output_resumes_defaults',
					[
						// Custom.
						'resumes_layout'            => 'list',
						'resumes_list_version'      => '1',
						'resumes_grid_version'      => '1',

						// Default.
						'per_page'                  => get_option( 'resume_manager_per_page' ),
						'order'                     => 'DESC',
						'orderby'                   => 'featured',
						'show_filters'              => true,
						'show_categories'           => get_option( 'resume_manager_enable_categories' ),
						'categories'                => '',
						'featured'                  => null, // True to show only featured, false to hide featured, leave null to show both.
						'show_category_multiselect' => get_option( 'resume_manager_enable_default_category_multiselect', false ),
						'selected_category'         => '',
						'show_pagination'           => false,
						'show_more'                 => true,
					]
				),
				$atts
			)
		);

		$categories = array_filter( array_map( 'trim', explode( ',', $categories ) ) );
		$keywords   = '';
		$location   = '';
		$skills     = '';

		// Resume Layout.
		if ( $resumes_layout === 'list' ) {
			$resumes_layout         = '_list';
			$resumes_layout_wrapper = 'resume_list';
			$resumes_version        = $resumes_list_version;
		} else {
			$resumes_layout         = '_' . $resumes_layout;
			$resumes_layout_wrapper = 'resume_grid';
			$resumes_version        = $resumes_grid_version;
		}

		// String and bool handling.
		$show_filters              = $this->string_to_bool( $show_filters );
		$show_categories           = $this->string_to_bool( $show_categories );
		$show_category_multiselect = $this->string_to_bool( $show_category_multiselect );
		$show_more                 = $this->string_to_bool( $show_more );
		$show_pagination           = $this->string_to_bool( $show_pagination );

		if ( ! is_null( $featured ) ) {
			$featured = ( is_bool( $featured ) && $featured ) || in_array( $featured, [ '1', 'true', 'yes' ] ) ? true : false;
		}

		if ( ! empty( $_GET['search_keywords'] ) ) {
			$keywords = sanitize_text_field( $_GET['search_keywords'] );
		}

		if ( ! empty( $_GET['search_location'] ) ) {
			$location = sanitize_text_field( $_GET['search_location'] );
		}

		if ( ! empty( $_GET['search_category'] ) ) {
			$selected_category = sanitize_text_field( $_GET['search_category'] );
		}

		if ( ! empty( $_GET['search_skills'] ) ) {
			$skills = sanitize_text_field( $_GET['search_skills'] );
		}

		if ( $show_filters ) {

			get_job_manager_template(
				'resume-filters.php',
				[
					'per_page'                  => $per_page,
					'orderby'                   => $orderby,
					'order'                     => $order,
					'show_categories'           => $show_categories,
					'categories'                => $categories,
					'selected_category'         => $selected_category,
					'atts'                      => $atts,
					'location'                  => $location,
					'keywords'                  => $keywords,
					'show_category_multiselect' => $show_category_multiselect,
					'skills'                    => $skills,
				],
				'wp-job-manager-resumes',
				RESUME_MANAGER_PLUGIN_DIR . '/templates/'
			);

			// get_job_manager_template( 'resumes-start.php', [], 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' );
			echo '<ul class="resumes resumes_main ' . esc_attr( $resumes_layout_wrapper ) . '">';
			get_job_manager_template( 'resumes-end.php', [], 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' );

			if ( ! $show_pagination && $show_more ) {
				echo '<a class="load_more_resumes" href="#" style="display:none;"><strong>' . esc_html__( 'Load more resumes', 'cariera' ) . '</strong></a>';
			}
		} else {

			$resumes = get_resumes(
				apply_filters(
					'resume_manager_output_resumes_args',
					[
						'search_categories' => $categories,
						'orderby'           => $orderby,
						'order'             => $order,
						'posts_per_page'    => $per_page,
						'featured'          => $featured,
					]
				)
			);

			if ( $resumes->have_posts() ) {

				echo '<ul class="resumes resumes_main ' . esc_attr( $resumes_layout_wrapper ) . '">';
				/**
				 * Show resumes with the right template
				 */
				while ( $resumes->have_posts() ) {
					$resumes->the_post();
					get_job_manager_template_part( 'resume-templates/content', 'resume' . $resumes_layout . $resumes_version, 'wp-job-manager-resumes' );
				}

				get_job_manager_template( 'resumes-end.php', [], 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' );

				if ( $resumes->found_posts > $per_page && $show_more ) {
					wp_enqueue_script( 'wp-resume-manager-ajax-filters' );

					if ( $show_pagination ) {
						echo get_job_listing_pagination( $resumes->max_num_pages );
					} else { ?>
						<a class="load_more_resumes" href="#"><strong><?php esc_html_e( 'Load more resumes', 'cariera' ); ?></strong></a>
						<?php
					}
				}
			} else {
				do_action( 'resume_manager_output_resumes_no_results' );
			}

			wp_reset_postdata();
		}

		$data_attributes_string = '';
		$data_attributes        = [
			'resume_layout'   => $resumes_layout,
			'resume_version'  => $resumes_version,

			// Default.
			'location'        => $location,
			'keywords'        => $keywords,
			'show_filters'    => $show_filters ? 'true' : 'false',
			'show_pagination' => $show_pagination ? 'true' : 'false',
			'per_page'        => $per_page,
			'orderby'         => $orderby,
			'order'           => $order,
			'categories'      => implode( ',', $categories ),
		];
		if ( ! is_null( $featured ) ) {
			$data_attributes['featured'] = $featured ? 'true' : 'false';
		}

		/**
		 * Pass additional data to the resume listing <div> wrapper.
		 *
		 * @since 1.18.5
		 *
		 * @param array $data_attributes {
		 *     Key => Value array of data attributes to pass.
		 *
		 *     @type string $$key Value to pass as a data attribute.
		 * }
		 * @param array $atts            Attributes for the shortcode.
		 */
		$data_attributes = apply_filters( 'job_manager_resumes_shortcode_data_attributes', $data_attributes, $atts );

		foreach ( $data_attributes as $key => $value ) {
			$data_attributes_string .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
		}

		/**
		 * Get current output buffer contents ( ob_get_clean() )
		 *
		 * @since 1.18.5
		 */
		$resume_listings_output = apply_filters( 'job_manager_resume_listings_output', ob_get_clean() );

		return '<div class="resumes" ' . $data_attributes_string . '>' . $resume_listings_output . '</div>';
	}

	/**
	 * Get Listings Results
	 *
	 * @since   1.5.0
	 * @version 1.6.0
	 */
	public function get_listings_result( $result, $resumes ) {

		ob_start();

		// Pull Resume attr via ajax file.
		$resumes_layout  = isset( $_POST['resume_layout'] ) ? sanitize_text_field( $_POST['resume_layout'] ) : '';
		$resumes_version = isset( $_POST['resume_version'] ) ? sanitize_text_field( $_POST['resume_version'] ) : '';

		// In case S&F for WPJM is activated.
		$resumes_layout  = isset( $_POST['data_params'], $_POST['data_params']['resume_layout'] ) ? sanitize_text_field( $_POST['data_params']['resume_layout'] ) : $resumes_layout;
		$resumes_version = isset( $_POST['data_params'], $_POST['data_params']['resume_version'] ) ? sanitize_text_field( $_POST['data_params']['resume_version'] ) : $resumes_version;

		$search_location   = isset( $_POST['search_location'] ) ? sanitize_text_field( wp_unslash( $_POST['search_location'] ) ) : '';
		$search_keywords   = isset( $_POST['search_keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['search_keywords'] ) ) : '';
		$search_categories = isset( $_POST['search_categories'] ) ? $_POST['search_categories'] : '';
		$search_skills     = isset( $_POST['search_skills'] ) ? sanitize_text_field( wp_unslash( $_POST['search_skills'] ) ) : '';

		if ( is_array( $search_categories ) ) {
			$search_categories = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $search_categories ) );
		} else {
			$search_categories = [ sanitize_text_field( stripslashes( $search_categories ) ), 0 ];
		}

		$search_categories = array_filter( $search_categories );

		$args = [
			'search_location'   => $search_location,
			'search_keywords'   => $search_keywords,
			'search_categories' => $search_categories,
			'search_skills'     => $search_skills,
			'orderby'           => sanitize_text_field( $_POST['orderby'] ),
			'order'             => sanitize_text_field( $_POST['order'] ),
			'posts_per_page'    => absint( $_POST['per_page'] ),
		];

		if ( ! empty( $_POST['exclude_ids'] ) ) {
			$args['post__not_in'] = array_map( 'absint', $_POST['exclude_ids'] );
		}

		if ( ! in_array( $_POST['orderby'], [ 'rand', 'rand_featured' ], true ) ) {
			$args['offset'] = ( absint( $_POST['page'] ) - 1 ) * absint( $_POST['per_page'] );
		}

		if ( isset( $_POST['featured'] ) && ( $_POST['featured'] === 'true' || $_POST['featured'] === 'false' ) ) {
			$args['featured'] = $_POST['featured'] === 'true' ? true : false;
		}

		$resumes = get_resumes( apply_filters( 'resume_manager_get_resumes_args', $args ) );

		$result                  = [];
		$result['found_resumes'] = false;
		$result['post_ids']      = [];

		if ( $resumes->have_posts() ) :
			$result['found_resumes'] = true;
			?>

			<?php
			while ( $resumes->have_posts() ) :
				$resumes->the_post();
				$result['post_ids'][] = the_resume_id();

				get_job_manager_template_part( 'resume-templates/content', 'resume' . $resumes_layout . $resumes_version, 'wp-job-manager-resumes' );
			endwhile;
			?>

		<?php else : ?>

			<li class="no_resumes_found"><?php esc_html_e( 'No resumes found matching your selection.', 'cariera' ); ?></li>

			<?php
		endif;

		$result['html'] = ob_get_clean();

		/*******
		* Everything below is totally taken from WPRM because there is no filter to target only the $result['html']
		*/
		// Generate 'showing' text.
		if ( $search_keywords || $search_location || $search_categories || apply_filters( 'resume_manager_get_resumes_custom_filter', false ) ) {

			$showing_categories = [];

			if ( $search_categories ) {
				foreach ( $search_categories as $category ) {
					if ( ! is_numeric( $category ) ) {
						$category_object = get_term_by( 'slug', $category, 'resume_category' );
					}
					if ( is_numeric( $category ) || is_wp_error( $category_object ) || ! $category_object ) {
						$category_object = get_term_by( 'id', $category, 'resume_category' );
					}
					if ( ! is_wp_error( $category_object ) ) {
						$showing_categories[] = $category_object->name;
					}
				}
			}

			if ( $search_keywords ) {
				$showing_resumes = sprintf( esc_html__( 'Showing &ldquo;%1$s&rdquo; %2$sresumes', 'cariera' ), $search_keywords, implode( ', ', $showing_categories ) );
			} else {
				$showing_resumes = sprintf( esc_html__( 'Showing all %sresumes', 'cariera' ), implode( ', ', $showing_categories ) . ' ' );
			}

			$showing_location = $search_location ? sprintf( ' ' . esc_html__( 'located in &ldquo;%s&rdquo;', 'cariera' ), $search_location ) : '';

			$result['showing'] = apply_filters( 'resume_manager_get_resumes_custom_filter_text', $showing_resumes . $showing_location );

		} else {
			$result['showing'] = '';
		}

		// Generate RSS link.
		$result['showing_links'] = resume_manager_get_filtered_links(
			[
				'search_location'   => $search_location,
				'search_categories' => $search_categories,
				'search_keywords'   => $search_keywords,
			]
		);

		// Generate pagination.
		if ( isset( $_POST['show_pagination'] ) && $_POST['show_pagination'] === 'true' ) {
			$result['pagination'] = get_job_listing_pagination( $resumes->max_num_pages, absint( $_POST['page'] ) );
		}

		$result['max_num_pages'] = $resumes->max_num_pages;

		return $result;
	}

	/**
	 * Gets string as a bool.
	 *
	 * @since  1.3.0
	 */
	public function string_to_bool( $value ) {
		return ( is_bool( $value ) && $value ) || in_array( $value, [ '1', 'true', 'yes' ] ) ? true : false;
	}
}
