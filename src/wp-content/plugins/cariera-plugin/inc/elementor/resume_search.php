<?php
/**
 * ELEMENTOR WIDGET - RESUME SEARCH
 *
 * @since    1.4.3
 * @version  1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Resume_Search extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'resume_search';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Resume Search Form', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-search';
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
			'search_style',
			[
				'label'       => esc_html__( 'Search Layout', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'style-1' => esc_html__( 'Style 1', 'cariera' ),
					'style-2' => esc_html__( 'Style 2', 'cariera' ),
				],
				'default'     => 'style-1',
				'description' => esc_html__( 'Choose the layout version that you want your search to have.', 'cariera' ),
			]
		);
		$this->add_control(
			'location',
			[
				'label'        => esc_html__( 'Location', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		$this->add_control(
			'region',
			[
				'label'        => esc_html__( 'Region', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);
		$this->add_control(
			'categories',
			[
				'label'        => esc_html__( 'Categories', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'yes',
				'default'      => 'yes',
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
	 * Widget output
	 */
	protected function render() {
		cariera_get_template(
			'elements/resume-search.php',
			[
				'settings' => $this->get_settings(),
			]
		);
	}
}
