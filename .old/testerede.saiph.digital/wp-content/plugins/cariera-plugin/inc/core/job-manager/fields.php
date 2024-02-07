<?php

namespace Cariera_Core\Core\Job_Manager;

use Cariera_Core\Core\Job_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Fields extends Job_Manager {

	/**
	 * Constructor
	 *
	 * @since 1.4.5
	 */
	public function __construct() {
		add_filter( 'submit_job_form_fields', [ $this, 'frontend_wpjm_extra_fields' ] );
		add_action( 'job_manager_update_job_data', [ $this, 'wpjm_update_job_data' ], 10, 2 );
		add_filter( 'job_manager_job_listing_data_fields', [ $this, 'admin_wpjm_extra_fields' ] );

		// Salary Schema Data.
		add_filter( 'wpjm_get_job_listing_structured_data', [ $this, 'salary_field_structured_data' ], 10, 2 );
	}

	/**
	 * Adding Extra Job Fields - Front-End.
	 *
	 * @since   1.0.0
	 * @version 1.5.3
	 */
	public function frontend_wpjm_extra_fields( $fields ) {

		$fields['job']['apply_link'] = [
			'label'       => esc_html__( 'External "Apply for Job" link', 'cariera' ),
			'type'        => 'text',
			'required'    => false,
			'placeholder' => esc_html__( 'http://', 'cariera' ),
			'priority'    => 6,
		];

		if ( get_option( 'cariera_job_manager_enable_qualification' ) ) {
			$fields['job']['job_listing_qualification'] = [
				'label'       => esc_html__( 'Job Qualification', 'cariera' ),
				'type'        => 'term-multiselect',
				'taxonomy'    => 'job_listing_qualification',
				'required'    => false,
				'placeholder' => esc_html__( 'Choose a job qualification', 'cariera' ),
				'priority'    => 7,
			];
		}

		if ( get_option( 'cariera_job_manager_enable_career_level' ) ) {
			$fields['job']['job_listing_career_level'] = [
				'label'       => esc_html__( 'Job Career Level', 'cariera' ),
				'type'        => 'term-select',
				'taxonomy'    => 'job_listing_career_level',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( 'Choose a career level', 'cariera' ),
				'priority'    => 8,
			];
		}

		if ( get_option( 'cariera_job_manager_enable_experience' ) ) {
			$fields['job']['job_listing_experience'] = [
				'label'       => esc_html__( 'Job Experience', 'cariera' ),
				'type'        => 'term-select',
				'taxonomy'    => 'job_listing_experience',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( 'Choose a job experience', 'cariera' ),
				'priority'    => 9,
			];
		}

		// If true Enable Rate fields.
		if ( get_option( 'cariera_enable_filter_rate' ) ) {
			$fields['job']['rate_min'] = [
				'label'       => esc_html__( 'Minimum rate/h', 'cariera' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'e.g. 20', 'cariera' ),
				'priority'    => 10,
			];
			$fields['job']['rate_max'] = [
				'label'       => esc_html__( 'Maximum rate/h', 'cariera' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'e.g. 50', 'cariera' ),
				'priority'    => 10.1,
			];
		}

		// If true Enable Salary fields.
		if ( get_option( 'cariera_enable_filter_salary' ) ) {
			$fields['job']['salary_min'] = [
				'label'       => esc_html__( 'Minimum Salary', 'cariera' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'e.g. 20000', 'cariera' ),
				'priority'    => 11,
			];
			$fields['job']['salary_max'] = [
				'label'       => esc_html__( 'Maximum Salary', 'cariera' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'e.g. 50000', 'cariera' ),
				'priority'    => 11.1,
			];
		}

		$fields['job']['hours']           = [
			'label'       => esc_html__( 'Hours per week', 'cariera' ),
			'type'        => 'text',
			'required'    => false,
			'placeholder' => esc_html__( 'e.g. 72', 'cariera' ),
			'priority'    => 12,
		];
		$fields['job']['job_cover_image'] = [
			'label'              => esc_html__( 'Cover Image', 'cariera' ),
			'type'               => 'file',
			'required'           => false,
			'description'        => esc_html__( 'The cover image size should be at least 1600x200px', 'cariera' ),
			'priority'           => 13,
			'ajax'               => true,
			'multiple'           => false,
			'allowed_mime_types' => [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif'  => 'image/gif',
				'png'  => 'image/png',
			],
		];

		return $fields;
	}

	/**
	 * Save the extra frontend fields.
	 *
	 * @since   1.0.0
	 * @version 1.5.3
	 */
	public function wpjm_update_job_data( $job_id, $values ) {
		if ( isset( $values['job']['rate_min'] ) ) {
			update_post_meta( $job_id, '_rate_min', $values['job']['rate_min'] );
		}
		if ( isset( $values['job']['rate_max'] ) ) {
			update_post_meta( $job_id, '_rate_max', $values['job']['rate_max'] );
		}
		if ( isset( $values['job']['salary_min'] ) ) {
			update_post_meta( $job_id, '_salary_min', $values['job']['salary_min'] );
		}
		if ( isset( $values['job']['salary_max'] ) ) {
			update_post_meta( $job_id, '_salary_max', $values['job']['salary_max'] );
		}
		if ( isset( $values['job']['hours'] ) ) {
			update_post_meta( $job_id, '_hours', $values['job']['hours'] );
		}
		if ( isset( $values['job']['apply_link'] ) ) {
			update_post_meta( $job_id, '_apply_link', $values['job']['apply_link'] );
		}
		if ( isset( $values['job']['job_cover_image'] ) ) {
			update_post_meta( $job_id, '_job_cover_image', $values['job']['job_cover_image'] );
		}
	}

	/**
	 * Adding Extra Job Fields - Back-End.
	 *
	 * @since 1.0.0
	 */
	public function admin_wpjm_extra_fields( $fields ) {

		$fields['_hours'] = [
			'label'        => esc_html__( 'Hours per week', 'cariera' ),
			'type'         => 'text',
			'priority'     => 11,
			'placeholder'  => esc_html( 'e.g. 72' ),
			'description'  => '',
			'show_in_rest' => true,
		];

		// If true Enable Rate fields.
		if ( get_option( 'cariera_enable_filter_rate' ) ) {
			$fields['_rate_min'] = [
				'label'        => esc_html__( 'Rate/h (minimum)', 'cariera' ),
				'type'         => 'text',
				'priority'     => 12,
				'placeholder'  => 'e.g. 20',
				'description'  => esc_html__( 'Put just a number', 'cariera' ),
				'show_in_rest' => true,
			];
			$fields['_rate_max'] = [
				'label'        => esc_html__( 'Rate/h (maximum) ', 'cariera' ),
				'type'         => 'text',
				'priority'     => 12,
				'placeholder'  => esc_html__( 'e.g. 20', 'cariera' ),
				'description'  => esc_html__( 'Put just a number - you can leave it empty and set only minimum rate value ', 'cariera' ),
				'show_in_rest' => true,
			];
		}

		// If true Enable Salary fields.
		if ( get_option( 'cariera_enable_filter_salary' ) ) {
			$fields['_salary_min'] = [
				'label'        => esc_html__( 'Salary min', 'cariera' ),
				'type'         => 'text',
				'priority'     => 12,
				'placeholder'  => esc_html__( 'e.g. 20.000', 'cariera' ),
				'description'  => esc_html__( 'Enter the min Salary of the Job', 'cariera' ),
				'show_in_rest' => true,
			];
			$fields['_salary_max'] = [
				'label'        => esc_html__( 'Salary max', 'cariera' ),
				'type'         => 'text',
				'priority'     => 12,
				'placeholder'  => esc_html__( 'e.g. 50.000', 'cariera' ),
				'description'  => esc_html__( 'Maximum of salary range you can offer - you can leave it empty and set only minimum salary ', 'cariera' ),
				'show_in_rest' => true,
			];
		}

		$fields['_apply_link'] = [
			'label'        => esc_html__( 'External "Apply for Job" link', 'cariera' ),
			'type'         => 'text',
			'priority'     => 5,
			'placeholder'  => esc_html( 'http://' ),
			'description'  => esc_html__( 'If the job applying is done on external page, here\'s the place to put link to that page - it will be used instead of standard Apply form', 'cariera' ),
			'show_in_rest' => true,
		];

		$fields['_job_cover_image'] = [
			'label'              => esc_html__( 'Job Cover Image', 'cariera' ),
			'type'               => 'file',
			'priority'           => 15,
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

	/**
	 * Adding Salary Field to Structured Data (schema.org markup)
	 *
	 * @since 1.4.6
	 */
	public function salary_field_structured_data( $data, $post ) {

		if ( $post && $post->ID ) {
			$salary = get_post_meta( $post->ID, '_job_salary', true );

			// Here you can add values that would be considered "not a salary" to skip output for.
			$no_salary_values = [ 'Not Disclosed', 'N/A', 'TBD' ];

			// Don't add anything if empty value, or value equals something above in no salary values.
			if ( empty( $salary ) || in_array( strtolower( $salary ), array_map( 'strtolower', $no_salary_values ), true ) ) {
				return $data;
			}

			// Determine float value, stripping all non-alphanumeric characters.
			$salary_float_val = (float) filter_var( $salary, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

			if ( ! empty( $salary_float_val ) ) {
				// @see https://schema.org/JobPosting
				// Simple value:
				// $data['baseSalary'] = $salary_float_val;

				// Or using Google's Structured Data format
				// @see https://developers.google.com/search/docs/data-types/job-posting
				// This is the format Google really wants it in, so you should customize this yourself
				// to match your setup and configuration.
				$data['baseSalary'] = [
					'@type'    => 'MonetaryAmount',
					'currency' => 'USD',
					'value'    => [
						'@type'    => 'QuantitativeValue',
						'value'    => $salary_float_val,
						// HOUR, DAY, WEEK, MONTH, or YEAR.
						'unitText' => 'YEAR',
					],
				];
			}
		}

		return $data;
	}
}
