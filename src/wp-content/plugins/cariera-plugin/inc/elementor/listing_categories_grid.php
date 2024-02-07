<?php
/**
 * ELEMENTOR WIDGET - LISTING CATEGORIES GRID
 *
 * @since    1.4.5
 * @version  1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Listing_Categories_Grid extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'listing_categories_grid';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Listing Categories Grid', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * Get widget's categories.
	 */
	public function get_categories() {
		return [ 'cariera-elements' ];
	}

	/**
	 * Register the controls for the widget
	 */
	protected function register_controls() {

		// SECTION.
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'cariera' ),
			]
		);

		// CONTROLS.
		$this->add_control(
			'listing',
			[
				'label'       => esc_html__( 'Listing', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'job_listing' => esc_html__( 'Job Listing', 'cariera' ),
					'resume'      => esc_html__( 'Resume', 'cariera' ),
				],
				'default'     => 'job_listing',
				'description' => '',
			]
		);
		$this->add_control(
			'category_layout',
			[
				'label'       => esc_html__( 'Category Grid Layout', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'layout1' => esc_html__( 'Layout 1', 'cariera' ),
					'layout2' => esc_html__( 'Layout 2', 'cariera' ),
					'layout3' => esc_html__( 'Layout 3', 'cariera' ),
					'layout4' => esc_html__( 'Layout 4', 'cariera' ),
				],
				'default'     => 'layout1',
				'description' => '',
			]
		);
		$this->add_control(
			'icon',
			[
				'label'        => esc_html__( 'Category Icon', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
				'condition'    => [
					'category_layout' => [ 'layout1', 'layout2' ],
				],
			]
		);
		$this->add_control(
			'background',
			[
				'label'        => esc_html__( 'Category Background', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
				'condition'    => [
					'category_layout' => 'layout1',
				],
			]
		);
		$this->add_control(
			'job_counter',
			[
				'label'        => esc_html__( 'Job Counter', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => esc_html__( 'Show number of jobs inside of category', 'cariera' ),
				'condition'    => [
					'listing' => 'job_listing',
				],
			]
		);
		$this->add_control(
			'resume_counter',
			[
				'label'        => esc_html__( 'Resume Counter', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => esc_html__( 'Show number of resumes inside of the category.', 'cariera' ),
				'condition'    => [
					'listing' => 'resume',
				],
			]
		);
		$this->add_control(
			'hide_empty',
			[
				'label'        => esc_html__( 'Hide Empty', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'true',
				'default'      => '',
				'description'  => esc_html__( 'Hides categories that doesn\'t have any listings.', 'cariera' ),
			]
		);
		$this->add_control(
			'orderby',
			[
				'label'       => esc_html__( 'Order by', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'name'  => esc_html__( 'Name', 'cariera' ),
					'ID'    => esc_html__( 'ID', 'cariera' ),
					'count' => esc_html__( 'Count', 'cariera' ),
					'slug'  => esc_html__( 'Slug', 'cariera' ),
					'none'  => esc_html__( 'None', 'cariera' ),
				],
				'default'     => 'count',
				'description' => '',
			]
		);
		$this->add_control(
			'order',
			[
				'label'       => esc_html__( 'Order', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'DESC' => esc_html__( 'Descending', 'cariera' ),
					'ASC'  => esc_html__( 'Ascending', 'cariera' ),
				],
				'default'     => 'DESC',
				'description' => '',
			]
		);
		$this->add_control(
			'items',
			[
				'label'       => esc_html__( 'Total Items', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '10',
				'description' => esc_html__( 'Set max limit for items (limited to 1000).', 'cariera' ),
			]
		);
		$this->add_control(
			'exclude_job_listing',
			[
				'label'     => esc_html__( 'Exclude Job Categories', 'cariera' ),
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'multiple'  => true,
				'default'   => [],
				'options'   => $this->get_terms( 'job_listing_category' ),
				'condition' => [
					'listing' => 'job_listing',
				],
			]
		);
		$this->add_control(
			'exclude_resume',
			[
				'label'     => esc_html__( 'Exclude Resume Categories', 'cariera' ),
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'multiple'  => true,
				'default'   => [],
				'options'   => $this->get_terms( 'resume_category' ),
				'condition' => [
					'listing' => 'resume',
				],
			]
		);
		$this->add_control(
			'custom_class',
			[
				'label'       => esc_html__( 'Custom Class', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => '',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 *
	 * @since 1.7.0
	 */
	public function get_style_depends() {
		return [ 'cariera-listing-categories' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		wp_enqueue_style( 'cariera-listing-categories' );

		$settings = $this->get_settings();
		$attrs    = '';

		$output  = '';
		$listing = $settings['listing'];

		$categories = get_terms(
			[
				'taxonomy'   => $settings['listing'] . '_category',
				'orderby'    => $settings['orderby'],
				'order'      => $settings['order'],
				'hide_empty' => $settings['hide_empty'],
				'number'     => $settings['items'],
				'exclude'    => $settings[ 'exclude_' . $listing ],
			]
		);

		if ( ! is_wp_error( $categories ) ) {
			$output .= '<div class="listing-category-wrapper ' . $settings['custom_class'] . '">';
			$chunks  = cariera_partition( $categories, 1 );

			// Listing Layout.
			foreach ( $chunks as $chunk ) {

				// Category Grid Layout 1.
				if ( $settings['category_layout'] === 'layout1' ) {
					$output .= '<div class="listing-categories ' . $settings['listing'] . '-categories grid-layout1">';
					foreach ( $chunk as $term ) {
						$t_id      = $term->term_id;
						$term_meta = get_option( "taxonomy_$t_id" );
						$bg_img    = isset( $term_meta['background_image'] ) ? $term_meta['background_image'] : '';
						$img_icon  = isset( $term_meta['image_icon'] ) ? $term_meta['image_icon'] : '';
						$font_icon = isset( $term_meta['font_icon'] ) ? $term_meta['font_icon'] : '';

						$output .= '<div class="listing-category">';

						// Background Options.
						if ( $settings['background'] === 'show' ) {
							if ( ! empty( $bg_img ) ) {
								$output .= '<a href="' . get_term_link( $term ) . '" class="listing-category-bg" style="background-image: url(' . esc_attr( $bg_img ) . ')">';
							} else {
								$output .= '<a href="' . get_term_link( $term ) . '">';
							}
						} else {
							$output .= '<a href="' . get_term_link( $term ) . '">';
						}

						// Category Icon.
						if ( $settings['icon'] === 'show' ) {
							if ( ! empty( $img_icon ) ) {
								$output .= '<img src="' . esc_attr( $img_icon ) . '" class="category-icon" />';
							} elseif ( ! empty( $font_icon ) ) {
								$output .= ' <i class="' . esc_attr( $font_icon ) . '"></i>';
							}
						}

						$output .= '<h4 class="title">' . $term->name . '</h4>';

						if ( $settings['listing'] === 'job_listing' ) {
							if ( $settings['job_counter'] === 'show' ) {
								$output .= '<span class="positions">(' . $term->count . esc_html__( ' open positions', 'cariera' ) . ')</span>';
							}
						} else {
							if ( $settings['resume_counter'] === 'show' ) {
								$output .= '<span class="positions">(' . $term->count . esc_html__( ' Resumes', 'cariera' ) . ')</span>';
							}
						}

						$output .= '</a></div>';
					}
					$output .= '</div>';
				}

				// Category Grid Layout 2.
				elseif ( $settings['category_layout'] === 'layout2' ) {
					$output .= '<div class="listing-categories ' . $settings['listing'] . '-categories grid-layout2">';
					foreach ( $chunk as $term ) {
						$t_id      = $term->term_id;
						$term_meta = get_option( "taxonomy_$t_id" );
						$img_icon  = isset( $term_meta['image_icon'] ) ? $term_meta['image_icon'] : '';
						$font_icon = isset( $term_meta['font_icon'] ) ? $term_meta['font_icon'] : '';

						$output .= '<div class="listing-category">';

						$output .= '<a href="' . get_term_link( $term ) . '">';

						// Category Icon.
						if ( $settings['icon'] === 'show' ) {
							if ( ! empty( $img_icon ) ) {
								$output .= '<img src="' . esc_attr( $img_icon ) . '" class="category-icon" />';
							} elseif ( ! empty( $font_icon ) ) {
								$output .= ' <i class="' . esc_attr( $font_icon ) . '"></i>';
							}
						}

						$output .= '<h4 class="title">' . $term->name . '</h4>';

						if ( $settings['listing'] === 'job_listing' ) {
							if ( $settings['job_counter'] === 'show' ) {
								$output .= '<span class="positions">(' . $term->count . esc_html__( ' open positions', 'cariera' ) . ')</span>';
							}
						} else {
							if ( $settings['resume_counter'] === 'show' ) {
								$output .= '<span class="positions">(' . $term->count . esc_html__( ' Resumes', 'cariera' ) . ')</span>';
							}
						}

						$output .= '</a></div>';
					}
					$output .= '</div>';
				}

				// Category Grid Layout 3.
				elseif ( $settings['category_layout'] === 'layout3' ) {
					$output .= '<div class="listing-categories ' . $settings['listing'] . '-categories grid-layout3">';
					foreach ( $chunk as $term ) {
						$t_id      = $term->term_id;
						$term_meta = get_option( 'taxonomy_' . $t_id );
						$img_icon  = isset( $term_meta['image_icon'] ) ? $term_meta['image_icon'] : '';
						$font_icon = isset( $term_meta['font_icon'] ) ? $term_meta['font_icon'] : '';

						$output .= '<div class="listing-category">';

						$output .= '<a href="' . get_term_link( $term ) . '">';

						// Category Icon.
						if ( ! empty( $img_icon ) ) {
							$output .= '<img src="' . esc_attr( $img_icon ) . '" class="category-icon" />';
						} elseif ( ! empty( $font_icon ) ) {
							$output .= ' <i class="' . esc_attr( $font_icon ) . '"></i>';
						}

						$output .= '<h4 class="title">' . $term->name . '</h4>';

						if ( $settings['listing'] === 'job_listing' ) {
							if ( $settings['job_counter'] === 'show' ) {
								$output .= '<span class="positions">(' . $term->count . esc_html__( ' open positions', 'cariera' ) . ')</span>';
							}
						} else {
							if ( $settings['resume_counter'] === 'show' ) {
								$output .= '<span class="positions">(' . $term->count . esc_html__( ' Resumes', 'cariera' ) . ')</span>';
							}
						}

						$output .= '</a></div>';
					}
					$output .= '</div>';

				}

				// Category Grid Layout 4.
				elseif ( $settings['category_layout'] === 'layout4' ) {
					$output .= '<div class="listing-categories ' . $settings['listing'] . '-categories grid-layout4">';
					foreach ( $chunk as $term ) {
						$t_id      = $term->term_id;
						$term_meta = get_option( 'taxonomy_' . $t_id );
						$img_icon  = isset( $term_meta['image_icon'] ) ? $term_meta['image_icon'] : '';
						$font_icon = isset( $term_meta['font_icon'] ) ? $term_meta['font_icon'] : '';

						$output .= '<a href="' . get_term_link( $term ) . '" class="category-item">';

						// Category Icon.
						if ( ! empty( $img_icon ) ) {
							$output .= '<img src="' . esc_attr( $img_icon ) . '" class="category-icon" />';
						} elseif ( ! empty( $font_icon ) ) {
							$output .= ' <i class="' . esc_attr( $font_icon ) . '"></i>';
						}

						$output .= '<h4 class="title">' . $term->name . '</h4>';

						if ( $settings['listing'] === 'job_listing' ) {
							if ( $settings['job_counter'] === 'show' ) {
								$output .= '<span class="category-count">(' . $term->count . esc_html__( ' open positions', 'cariera' ) . ')</span>';
							}
						} else {
							if ( $settings['resume_counter'] === 'show' ) {
								$output .= '<span class="category-count">(' . $term->count . esc_html__( ' Resumes', 'cariera' ) . ')</span>';
							}
						}

						$output .= '</a>';
					}
					$output .= '</div>';

				}
			}

			$output .= '</div>';
		}

		echo $output;
	}

	/**
	 * Get Terms of a taxonomy
	 */
	protected function get_terms( $taxonomy ) {
		$taxonomies = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]
		);

		$options = [ '' => '' ];

		if ( is_array( $taxonomies ) && ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$options[ $taxonomy->term_id ] = $taxonomy->name;
			}
		}

		return $options;
	}
}
