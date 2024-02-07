<?php
/**
 * ELEMENTOR WIDGET - COUNT DOWN
 *
 * @since   1.4.5
 * @version 1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Count_Down extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'count_down';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Count Down', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-countdown';
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
			'countdown_date',
			[
				'label'       => esc_html__( 'Select Date', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::DATE_TIME,
				'default'     => date( 'Y-m-d H:i' ),
				'placeholder' => date( 'Y-m-d H:i' ),
			]
		);
		$this->add_control(
			'countdown_days',
			[
				'label'   => esc_html__( 'Days Text', 'cariera' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Days',
			]
		);
		$this->add_control(
			'countdown_hours',
			[
				'label'   => esc_html__( 'Hours Text', 'cariera' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Hours',
			]
		);
		$this->add_control(
			'countdown_mins',
			[
				'label'   => esc_html__( 'Minutes Text', 'cariera' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Minutes',
			]
		);
		$this->add_control(
			'countdown_secs',
			[
				'label'   => esc_html__( 'Seconds Text', 'cariera' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Seconds',
			]
		);
		$this->add_control(
			'countdown_color',
			[
				'label'     => esc_html__( 'Color', 'cariera' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [ '{{WRAPPER}} .cariera-countdown .value' => 'color: {{VALUE}}' ],
			]
		);
	}

	/**
	 * Script Dependecy
	 */
	public function get_script_depends() {
		return [ 'count-down' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings(); ?>

		<div class="cariera-countdown" data-countdown="<?php echo esc_attr( $settings['countdown_date'] ); ?>" data-days="<?php echo esc_attr( $settings['countdown_days'] ); ?>" data-hours="<?php echo esc_attr( $settings['countdown_hours'] ); ?>" data-mins="<?php echo esc_attr( $settings['countdown_mins'] ); ?>" data-secs="<?php echo esc_attr( $settings['countdown_secs'] ); ?>"></div>
		<?php
	}
}
