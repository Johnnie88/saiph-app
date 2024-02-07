<?php

namespace Cariera_Core\Core\Resume_Manager;

use Cariera_Core\Core\Resume_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Fields extends Resume_Manager {

	/**
	 * Constructor
	 *
	 * @since 1.4.5
	 */
	public function __construct() {
		add_filter( 'submit_resume_form_fields', [ $this, 'frontend_wprm_fields' ] );
		add_action( 'resume_manager_update_resume_data', [ $this, 'wprm_update_data' ], 10, 2 );
		add_filter( 'resume_manager_resume_fields', [ $this, 'admin_wprm_fields' ] );
	}

	/**
	 * Adding custom fields for resumes - Front-End
	 *
	 * @since 1.0.0
	 */
	public function frontend_wprm_fields( $fields ) {
		$fields['resume_fields']['candidate_rate'] = [
			'label'       => esc_html__( 'Rate per Hour', 'cariera' ),
			'type'        => 'text',
			'required'    => false,
			'placeholder' => esc_html__( 'e.g. 20', 'cariera' ),
			'priority'    => 9,
		];

		if ( get_option( 'cariera_resume_manager_enable_education' ) ) {
			$fields['resume_fields']['candidate_education_level'] = [
				'label'       => esc_html__( 'Education Level', 'cariera' ),
				'type'        => 'term-select',
				'taxonomy'    => 'resume_education_level',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( 'Bachelor degree', 'cariera' ),
				'priority'    => 9,
			];
		}

		$fields['resume_fields']['candidate_languages'] = [
			'label'       => esc_html__( 'Languages', 'cariera' ),
			'type'        => 'text',
			'required'    => false,
			'placeholder' => esc_html__( 'English, German, Chinese', 'cariera' ),
			'priority'    => 9,
		];

		if ( get_option( 'cariera_resume_manager_enable_experience' ) ) {
			$fields['resume_fields']['candidate_experience_years'] = [
				'label'       => esc_html__( 'Experience', 'cariera' ),
				'type'        => 'term-select',
				'taxonomy'    => 'resume_experience',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( '3 years', 'cariera' ),
				'priority'    => 9,
			];
		}

		$fields['resume_fields']['candidate_featured_image'] = [
			'label'              => esc_html__( 'Cover Image', 'cariera' ),
			'type'               => 'file',
			'required'           => false,
			'description'        => esc_html__( 'The cover image size should be max 1920x400px', 'cariera' ),
			'priority'           => 5,
			'ajax'               => true,
			'multiple'           => false,
			'allowed_mime_types' => [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif'  => 'image/gif',
				'png'  => 'image/png',
			],
		];

		$fields['resume_fields']['candidate_facebook'] = [
			'label'       => esc_html__( 'Facebook', 'cariera' ),
			'type'        => 'text',
			'placeholder' => esc_html__( 'Your Facebook page link', 'cariera' ),
			'priority'    => 9.4,
			'required'    => false,
		];

		$fields['resume_fields']['candidate_twitter'] = [
			'label'       => esc_html__( 'Twitter', 'cariera' ),
			'type'        => 'text',
			'placeholder' => esc_html__( 'Your Twitter page link', 'cariera' ),
			'priority'    => 9.5,
			'required'    => false,
		];

		$fields['resume_fields']['candidate_linkedin'] = [
			'label'       => esc_html__( 'LinkedIn', 'cariera' ),
			'type'        => 'text',
			'placeholder' => esc_html__( 'Your LinkedIn page link', 'cariera' ),
			'priority'    => 9.7,
			'required'    => false,
		];

		$fields['resume_fields']['candidate_instagram'] = [
			'label'       => esc_html__( 'Instagram', 'cariera' ),
			'type'        => 'text',
			'placeholder' => esc_html__( 'Your Instagram page link', 'cariera' ),
			'priority'    => 9.8,
			'required'    => false,
		];

		$fields['resume_fields']['candidate_youtube'] = [
			'label'       => esc_html__( 'Youtube', 'cariera' ),
			'type'        => 'text',
			'placeholder' => esc_html__( 'Your Youtube page link', 'cariera' ),
			'priority'    => 9.9,
			'required'    => false,
		];

		return $fields;
	}

	/**
	 * Update frontend fields.
	 *
	 * @since 1.3.0
	 */
	public function wprm_update_data( $resume_id, $values ) {
		if ( isset( $values['resume_fields']['candidate_rate'] ) ) {
			update_post_meta( $resume_id, '_rate', $values['resume_fields']['candidate_rate'] );
		}
		if ( isset( $values['resume_fields']['candidate_languages'] ) ) {
			update_post_meta( $resume_id, '_languages', $values['resume_fields']['candidate_languages'] );
		}
		if ( isset( $values['resume_fields']['candidate_featured_image'] ) ) {
			update_post_meta( $resume_id, '_featured_image', $values['resume_fields']['candidate_featured_image'] );
		}
		if ( isset( $values['resume_fields']['candidate_facebook'] ) ) {
			update_post_meta( $resume_id, '_facebook', $values['resume_fields']['candidate_facebook'] );
		}
		if ( isset( $values['resume_fields']['candidate_twitter'] ) ) {
			update_post_meta( $resume_id, '_twitter', $values['resume_fields']['candidate_twitter'] );
		}
		if ( isset( $values['resume_fields']['candidate_linkedin'] ) ) {
			update_post_meta( $resume_id, '_linkedin', $values['resume_fields']['candidate_linkedin'] );
		}
		if ( isset( $values['resume_fields']['candidate_instagram'] ) ) {
			update_post_meta( $resume_id, '_instagram', $values['resume_fields']['candidate_instagram'] );
		}
		if ( isset( $values['resume_fields']['candidate_youtube'] ) ) {
			update_post_meta( $resume_id, '_youtube', $values['resume_fields']['candidate_youtube'] );
		}
	}

	/**
	 * Adding custom fields for resumes - Back-End
	 *
	 * @since 1.0.0
	 */
	public function admin_wprm_fields( $fields ) {
		$fields['_rate'] = [
			'label'        => esc_html__( 'Rate per Hour', 'cariera' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'e.g. 20', 'cariera' ),
			'description'  => '',
			'show_in_rest' => true,
		];

		$fields['_languages'] = [
			'label'        => esc_html__( 'Languages', 'cariera' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'English, German, Chinese', 'cariera' ),
			'description'  => '',
			'show_in_rest' => true,
		];

		$fields['_facebook'] = [
			'label'        => esc_html__( 'Facebook', 'cariera' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'Your Facebook page link', 'cariera' ),
			'show_in_rest' => true,
		];

		$fields['_twitter'] = [
			'label'        => esc_html__( 'Twitter', 'cariera' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'Your Twitter page link', 'cariera' ),
			'show_in_rest' => true,
		];

		$fields['_linkedin'] = [
			'label'        => esc_html__( 'LinkedIn', 'cariera' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'Your LinkedIn page link', 'cariera' ),
			'show_in_rest' => true,
		];

		$fields['_instagram'] = [
			'label'        => esc_html__( 'Instagram', 'cariera' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'Your Instagram page link', 'cariera' ),
			'show_in_rest' => true,
		];

		$fields['_youtube'] = [
			'label'        => esc_html__( 'Youtube', 'cariera' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'Your Youtube page link', 'cariera' ),
			'show_in_rest' => true,
		];

		$fields['_featured_image'] = [
			'label'              => esc_html__( 'Resume Cover Image', 'cariera' ),
			'type'               => 'file',
			'description'        => '',
			'multiple'           => false,
			'allowed_mime_types' => [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif'  => 'image/gif',
				'png'  => 'image/png',
			],
			'show_in_rest'       => true,
		];

		return $fields;
	}
}
