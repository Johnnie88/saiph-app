<?php
/**
 * ELEMENTOR WIDGET - TESTIMONIALS
 *
 * @since    1.4.5
 * @version  1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Testimonials extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'testimonials';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Testimonials', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-slider-device';
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
			'layout_style',
			[
				'label'       => esc_html__( 'Testimonial Style', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'layout1' => esc_html__( 'Layout 1', 'cariera' ),
					'layout2' => esc_html__( 'Layout 2', 'cariera' ),
					'layout3' => esc_html__( 'Layout 3', 'cariera' ),
				],
				'default'     => 'layout1',
				'description' => '',
			]
		);
		$this->add_control(
			'show_testimonials',
			[
				'label'       => esc_html__( 'Show Testimonials', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'random'   => esc_html__( 'Random', 'cariera' ),
					'latest'   => esc_html__( 'Latest', 'cariera' ),
					'selected' => esc_html__( 'Selected IDs', 'cariera' ),
				],
				'default'     => 'random',
				'description' => '',
			]
		);
		$this->add_control(
			'ids',
			[
				'label'       => esc_html__( 'Enter Post IDs', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => esc_html__( 'Enter Post ID\'s separated by a comma. Leave empty to show all.', 'cariera' ),
				'condition'   => [
					'show_testimonials' => 'selected',
				],
			]
		);
		$this->add_control(
			'posts_per_page',
			[
				'label'       => esc_html__( 'Posts to show', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '5',
				'description' => esc_html__( 'Number of testimonials to show (-1 for all).', 'cariera' ),
				'condition'   => [
					'show_testimonials' => [ 'random', 'latest' ],
				],
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
		return [ 'cariera-testimonials' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		wp_enqueue_style( 'cariera-testimonials' );

		$settings = $this->get_settings();

		if ( $settings['layout_style'] === 'layout1' ) {
			$layout_class = 'testimonials-carousel-style1';
		} elseif ( $settings['layout_style'] === 'layout2' ) {
			$layout_class = 'testimonials-carousel-style2';
		} elseif ( $settings['layout_style'] === 'layout3' ) {
			$layout_class = 'testimonials-carousel-style3';
		} elseif ( $settings['layout_style'] === 'layout4' ) {
			$layout_class = 'testimonials-carousel-style4';
		}

		if ( $settings['show_testimonials'] === 'selected' ) {
			$show_only_ids = explode( ',', $settings['ids'] );
			$args          = [
				'post_type' => 'testimonial',
				'post__in'  => $show_only_ids,
			];
		} else {
			$args = [
				'post_type'      => 'testimonial',
				'posts_per_page' => $settings['posts_per_page'],
			];
		}

		echo '<div class="testimonials-carousel ' . esc_attr( $layout_class ) . '">';

		$posts_query = new \WP_Query( $args );
		if ( $posts_query->have_posts() ) {
			while ( $posts_query->have_posts() ) :
				$posts_query->the_post();
				global $post;
				echo '<div class="testimonial-item">';

					// TESTIMONIALS LAYOUT 1.
				if ( $settings['layout_style'] === 'layout1' ) {
					get_template_part( '/templates/content/content-testimonial1' );
				}

					// TESTIMONIALS LAYOUT 2.
				elseif ( $settings['layout_style'] === 'layout2' ) {
					get_template_part( '/templates/content/content-testimonial2' );
				}

					// TESTIMONIALS LAYOUT 3.
				elseif ( $settings['layout_style'] === 'layout3' ) {
					get_template_part( '/templates/content/content-testimonial3' );
				}

				echo '</div>';

			endwhile;
		}

		echo '</div>';

		wp_reset_postdata();
	}
}
