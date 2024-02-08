<?php
/**
 * ELEMENTOR WIDGET - JOB SEARCH BOX
 *
 * @since    1.4.0
 * @version  1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Job_Search_Box extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'job_search_box';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Job Search Box Form', 'cariera' );
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
			'title',
			[
				'label'   => esc_html__( 'Search Box Title', 'cariera' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '',
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
			'elements/job-searchbox.php',
			[
				'settings' => $this->get_settings(),
			]
		);
	}
}
