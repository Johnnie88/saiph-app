<?php
/**
 * ELEMENTOR WIDGET - LISTING MAP
 *
 * @since   1.4.0
 * @version 1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Listing_Map extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'listing_map';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Listing Map', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-google-maps';
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
			'listing_type',
			[
				'label'       => esc_html__( 'Listing Type', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'job_listing' => esc_html__( 'Job Listings', 'cariera' ),
					'company'     => esc_html__( 'Companies', 'cariera' ),
					'resumes'     => esc_html__( 'Resumes', 'cariera' ),
				],
				'default'     => 'job_listing',
				'description' => esc_html__( 'The page must have listings from your selected listing type so that the map can grab their locations and add them into the map.', 'cariera' ),
			]
		);
		$this->add_control(
			'height',
			[
				'label'       => esc_html__( 'Map Height', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '450px',
				'description' => esc_html__( 'Insert the height of the map. For example: 450px.', 'cariera' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();
		$attrs    = '';

		$output = do_shortcode( '[cariera-map type="' . $settings['listing_type'] . '" height="' . $settings['height'] . '"]' );

		echo $output;
	}
}
