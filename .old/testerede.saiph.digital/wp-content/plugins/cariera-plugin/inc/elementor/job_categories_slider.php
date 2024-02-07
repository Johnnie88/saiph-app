<?php
/**
 * ELEMENTOR WIDGET - JOB CATEGORIES SLIDER
 *
 * @since    1.4.0
 * @version  1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Job_Categories_Slider extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'job_categories_slider';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Job Categories Slider', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-slider-3d';
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
			'style',
			[
				'label'       => esc_html__( 'Category Box Style', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'dark'  => esc_html__( 'Dark', 'cariera' ),
					'light' => esc_html__( 'Light', 'cariera' ),
				],
				'default'     => 'dark',
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
			]
		);
		$this->add_control(
			'columns',
			[
				'label'       => esc_html__( 'Visible Items per Slide', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '5',
				'min'         => '1',
				'max'         => '10',
				'description' => esc_html__( 'This will change how many categories will be visible per slide.', 'cariera' ),
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
				'min'         => '1',
				'description' => esc_html__( 'Set max limit for items (limited to 1000).', 'cariera' ),
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
	 * @since 1.7.1
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

		$output = '';

		$categories = get_terms(
			[
				'taxonomy'   => 'job_listing_category',
				'orderby'    => $settings['orderby'],
				'order'      => $settings['order'],
				'hide_empty' => $settings['hide_empty'],
				'number'     => $settings['items'],
			]
		);

		if ( ! is_wp_error( $categories ) ) {
			$output .= '<div class="category-groups category-slider-layout ' . esc_attr( $settings['custom_class'] ) . '">';
			$chunks  = cariera_partition( $categories, $settings['columns'] );

			/* Category Layout - Slider */
			$output .= '<div class="job-cat-slider1" data-columns="' . esc_attr( $settings['columns'] ) . '">';
			foreach ( $chunks as $chunk ) {
				foreach ( $chunk as $term ) {
					$t_id      = $term->term_id;
					$term_meta = get_option( "taxonomy_$t_id" ); // This might need to be changed.

					$img_icon  = isset( $term_meta['image_icon'] ) ? $term_meta['image_icon'] : '';
					$font_icon = isset( $term_meta['font_icon'] ) ? $term_meta['font_icon'] : '';

					$output .= '<a href="' . get_term_link( $term ) . '" class="item">';
					$output .= '<div class="cat-item ' . $settings['style'] . '-style">';

					// Category Icon.
					if ( $settings['icon'] === 'show' ) {
						$output .= '<span class="cat-icon">';
						if ( ! empty( $img_icon ) ) {
							$output .= '<img src="' . esc_attr( $img_icon ) . '" class="category-icon" />';
						} elseif ( ! empty( $font_icon ) ) {
							$output .= ' <i class="' . esc_attr( $font_icon ) . '"></i>';
						}
						$output .= '</span>';
					}

					$output .= '<span class="cat-title">' . $term->name . '</span>';
					$output .= '</div></a>';
				}
			}
			$output .= '</div>';

			$output .= '</div>';
		}

		echo $output;
	}
}
