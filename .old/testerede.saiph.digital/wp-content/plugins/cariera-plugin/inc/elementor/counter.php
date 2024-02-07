<?php
/**
 * ELEMENTOR WIDGET - COUNTER
 *
 * @since   1.4.0
 * @version 1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Counter extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'cariera_counter';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Counter', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-counter';
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

		// SECTION CONTENT.
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'cariera' ),
			]
		);

		// CONTROLS.
		$this->add_control(
			'theme',
			[
				'label'       => esc_html__( 'Theme', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => [
					'light' => esc_html__( 'Light', 'cariera' ),
					'dark'  => esc_html__( 'Dark', 'cariera' ),
				],
				'default'     => 'light',
				'description' => '',
			]
		);
		$this->add_control(
			'layout',
			[
				'label'       => esc_html__( 'Layout', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => [
					'layout1' => esc_html__( 'Layout 1', 'cariera' ),
					'layout2' => esc_html__( 'Layout 2', 'cariera' ),
				],
				'default'     => 'layout1',
				'description' => '',
			]
		);
		$this->add_control(
			'enable_icon',
			[
				'label'        => esc_html__( 'Counter Icon', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'cariera' ),
				'label_off'    => esc_html__( 'Disable', 'cariera' ),
				'return_value' => 'enable',
				'default'      => 'enable',
				'description'  => '',
			]
		);
		$this->add_control(
			'icon_type',
			[
				'label'       => esc_html__( 'Icon Type', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => [
					'icon'  => esc_html__( 'Icon', 'cariera' ),
					'image' => esc_html__( 'Custom Image', 'cariera' ),
				],
				'default'     => 'icon',
				'description' => '',
				'condition'   => [
					'enable_icon' => 'enable',
				],
			]
		);
		$this->add_control(
			'icon',
			[
				'label'     => esc_html__( 'Icon', 'cariera' ),
				'type'      => \Elementor\Controls_Manager::ICONS,
				'condition' => [
					'enable_icon' => 'enable',
					'icon_type'   => 'icon',
				],
			]
		);
		$this->add_control(
			'image',
			[
				'label'       => esc_html__( 'Image', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'default'     => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'description' => esc_html__( 'Recommended image size is 94x94 px.', 'cariera' ),
				'condition'   => [
					'enable_icon' => 'enable',
					'icon_type'   => 'image',
				],
			]
		);
		$this->add_control(
			'value',
			[
				'label'       => esc_html__( 'Value', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => [
					'custom'    => esc_html__( 'Custom Number', 'cariera' ),
					'jobs'      => esc_html__( 'Total Jobs', 'cariera' ),
					'companies' => esc_html__( 'Total Companies', 'cariera' ),
					'resumes'   => esc_html__( 'Total Resumes', 'cariera' ),
					'users'     => esc_html__( 'Registered Users', 'cariera' ),
				],
				'default'     => 'custom',
				'description' => '',
			]
		);
		$this->add_control(
			'number',
			[
				'label'       => esc_html__( 'Counter Number', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '5000',
				'description' => '',
				'condition'   => [
					'value' => 'custom',
				],
			]
		);
		$this->add_control(
			'suffix',
			[
				'label'       => esc_html__( 'Counter Suffix', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => esc_html__( 'Put any text or symbol after Counter Number eg. +', 'cariera' ),
				'condition'   => [
					'value' => 'custom',
				],
			]
		);
		$this->add_control(
			'title',
			[
				'label'       => esc_html__( 'Title', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'Counter Title',
				'description' => '',
			]
		);

		$this->end_controls_section();

		// SECTION STYLE.
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Style', 'cariera' ),
			]
		);

		// CONTROLS.
		$this->add_control(
			'icon_color',
			[
				'label'       => esc_html__( 'Icon Color', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'default'     => '',
				'description' => '',
				'selectors'   => [ '{{WRAPPER}} .counter-container .counter .counter-icon i' => 'color: {{VALUE}}' ],
			]
		);
		$this->add_control(
			'counter_color',
			[
				'label'       => esc_html__( 'Counter Color', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'default'     => '',
				'description' => '',
				'selectors'   => [ '{{WRAPPER}} .counter-container .counter .counter-number-wrapper' => 'color: {{VALUE}}' ],
			]
		);
		$this->add_control(
			'counter_size',
			[
				'label'       => esc_html__( 'Counter Font Size', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => '',
				],
				'description' => '',
				'selectors'   => [ '{{WRAPPER}} .counter-container .counter .counter-number-wrapper' => 'font-size: {{SIZE}}{{UNIT}}' ],
			]
		);
		$this->add_control(
			'title_color',
			[
				'label'       => esc_html__( 'Title Color', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'default'     => '',
				'description' => '',
				'selectors'   => [ '{{WRAPPER}} .counter-container .counter .title' => 'color: {{VALUE}}' ],
			]
		);
		$this->add_control(
			'title_size',
			[
				'label'       => esc_html__( 'Title Font Size', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => '',
				],
				'description' => '',
				'selectors'   => [ '{{WRAPPER}} .counter-container .counter .title' => 'font-size: {{SIZE}}{{UNIT}}' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();

		switch ( $settings['value'] ) {
			case 'custom':
				$number = $settings['number'];
				break;

			case 'jobs':
				if ( class_exists( 'WP_Job_Manager' ) ) {
					$count  = wp_count_posts( 'job_listing' );
					$number = $count->publish;
				} else {
					$number = 0;
				}
				break;

			case 'resumes':
				if ( class_exists( 'WP_Resume_Manager' ) ) {
					$count  = wp_count_posts( 'resume' );
					$number = $count->publish;
				} else {
					$number = 0;
				}
				break;

			case 'companies':
				if ( class_exists( 'WP_Job_Manager' ) ) {
					$count  = wp_count_posts( 'company' );
					$number = $count->publish;
				} else {
					$number = 0;
				}
				break;

			case 'users':
				$number = count_users();
				$number = $number['total_users'];
				break;
		}

		cariera_get_template(
			'elements/counter.php',
			[
				'settings' => $this->get_settings(),
				'number'   => $number,
			]
		);
	}
}
