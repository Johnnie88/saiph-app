<?php
/**
 * ELEMENTOR WIDGET - VIDEO POPUP
 *
 * @since   1.4.0
 * @version 1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Video_Popup extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'video_popup';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Video Popup', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-play';
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
			'link',
			[
				'label'         => esc_html__( 'Video Link', 'cariera' ),
				'type'          => \Elementor\Controls_Manager::URL,
				'default'       => [
					'url'         => 'http://',
					'is_external' => '',
					'nofollow'    => '',
				],
				'show_external' => false, // Show the 'open in new tab' button.
			]
		);
		$this->add_control(
			'image',
			[
				'label'   => esc_html__( 'Image', 'cariera' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);
		$this->add_control(
			'overlay',
			[
				'label'   => esc_html__( 'Overlay Color', 'cariera' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '',
			]
		);
		$this->add_control(
			'open',
			[
				'label'   => esc_html__( 'Open Video in', 'cariera' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'popup'  => esc_html__( 'Popup', 'cariera' ),
					'window' => esc_html__( 'New Window', 'cariera' ),
				],
				'default' => 'popup',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Widget output
	 */
	protected function render() {
		cariera_get_template(
			'elements/video-popup.php',
			[
				'settings' => $this->get_settings(),
			]
		);
	}
}
