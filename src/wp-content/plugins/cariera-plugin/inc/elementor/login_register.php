<?php
/**
 * ELEMENTOR WIDGET - LOGIN & REGISTER FORMS
 *
 * @since   1.5.0
 * @version 1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Login_Register extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'login_register';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Login & Register Form', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
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
			'form',
			[
				'label'       => esc_html__( 'Choose Form', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'login'    => esc_html__( 'Login Form', 'cariera' ),
					'register' => esc_html__( 'Register Form', 'cariera' ),
				],
				'default'     => 'login',
				'description' => esc_html__( 'The login & register form will not be displayed for loggedin users, you have to check the page as a non loggedin user.', 'cariera' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();

		if ( $settings['form'] === 'login' ) {
			$output = do_shortcode( '[cariera_login_form]' );
		} else {
			$output = do_shortcode( '[cariera_registration_form]' );
		}

		echo $output;
	}
}
