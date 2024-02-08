<?php
/**
 * ELEMENTOR WIDGET - CONTACT FORM 7
 *
 * @since   1.4.5
 * @version 1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Contact_Form7 extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'contact_form7';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Contact Form 7', 'cariera' );
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
			'form_id',
			[
				'label'   => esc_html__( 'Select a form', 'cariera' ),
				'type'    => \Elementor\Controls_Manager::SELECT2,
				'default' => '0',
				'options' => cariera_get_forms(),
			]
		);
		$this->add_control(
			'form_title',
			[
				'label'       => esc_html__( 'Form Title', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => esc_html__( '(Optional) Title to search if no ID selected or cannot find by ID.', 'cariera' ),
				'label_block' => true,
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

		if ( $settings['form_id'] ) {
			$attrs .= ' id="' . $settings['form_id'] . '"';
		} elseif ( $settings['form_title'] ) {
			$attrs .= ' title="' . $settings['form_title'] . '"';
		}

		$shortcode = do_shortcode( '[contact-form-7' . $attrs . ']' ); ?>
		<div class="contact-form7"><?php echo $shortcode; ?></div>

		<?php
	}
}
