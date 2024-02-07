<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Widget_Resume_Search extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		$widget_options = [
			'classname'   => 'resume-search-widget',
			'description' => esc_html__( 'Resume search form.', 'cariera' ),
		];

		parent::__construct( 'resume-search-widget', esc_html__( 'Custom: Resume Search Widget', 'cariera' ), $widget_options, 'cariera' );
	}

	/**
	 * Front-End Display of the Widget
	 *
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {
		echo wp_kses_post( $args['before_widget'] );

		do_shortcode( '[cariera_resume_sidebar_search]' );

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Back-End display of the Widget
	 *
	 * @param array $instance
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function form( $instance ) {}

	/**
	 * Handles updating the settings for the widget instance.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
}

register_widget( 'Cariera_Widget_Resume_Search' );
