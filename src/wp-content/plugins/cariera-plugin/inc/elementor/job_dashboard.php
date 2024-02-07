<?php
/**
 * ELEMENTOR WIDGET - JOB DASHBOARD
 *
 * @since   1.4.4
 * @version 1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Job_Dashboard extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'job_dashboard';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Job Dashboard', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-post-list';
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

		$this->end_controls_section();
	}

	/**
	 * Widget output
	 */
	protected function render() {
		// $settings   = $this->get_settings();
		// $attrs      = '';

		$output = do_shortcode( '[job_dashboard]' );

		echo $output;
	}
}
