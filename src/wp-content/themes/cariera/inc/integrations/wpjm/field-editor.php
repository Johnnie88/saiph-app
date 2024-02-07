<?php

namespace Cariera\Integrations\WPJM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Field_Editor {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since   1.7.0
	 * @version 1.7.0
	 */
	public function __construct() {
		if ( ! class_exists( 'WP_Job_Manager_Field_Editor' ) ) {
			return;
		}

		add_filter( 'field_editor_output_options', [ $this, 'custom_field_editor_auto_output' ], 10, 2 );
	}

	/**
	 * Custom output areas for the WPJM Field Editor Plugin
	 *
	 * @since  1.4.6
	 */
	public function custom_field_editor_auto_output( $output_options, $list_field_group ) {

		// Job Output Options.
		$job_options = [
			// Job Listings - List ver 1 & 2.
			'cariera_job_listings_output_label'        => '---' . esc_html__( 'Job Listings - List Layout 1 & 2', 'cariera' ),
			'job_listing_meta_start'                   => esc_html__( 'Jobs Listing Meta Start', 'cariera' ),
			'job_listing_meta_end'                     => esc_html__( 'Jobs Listing Meta End', 'cariera' ),

			// Job Listings - all except grid 5.
			'cariera_job_listings_output_label2'       => '---' . esc_html__( 'Job Listings - All Except Grid 5 Layout', 'cariera' ),
			'job_listing_info_start'                   => esc_html__( 'Jobs Listing Info Start', 'cariera' ),
			'job_listing_info_end'                     => esc_html__( 'Jobs Listing Info End', 'cariera' ),

			// Single Job Page.
			'cariera_single_job_listing_page'          => '---' . esc_html__( 'Single Job Page', 'cariera' ),
			'single_job_listing_company_before'        => esc_html__( 'Before Company Meta', 'cariera' ),
			'single_job_listing_company_contact_start' => esc_html__( 'Company Contact Meta Start', 'cariera' ),
			'single_job_listing_company_contact_end'   => esc_html__( 'Company Contact Meta End', 'cariera' ),
			'single_job_listing_company_after'         => esc_html__( 'After Company Meta', 'cariera' ),
			'the_job_description_top'                  => esc_html__( 'Top of Job Description', 'cariera' ),
			'the_job_description'                      => esc_html__( 'Bottom of Job Description', 'cariera' ),
			'single_job_listing_end'                   => esc_html__( 'Bottom of Job Listing', 'cariera' ),
			'job_application_start'                    => esc_html__( 'Before Application Button', 'cariera' ),
			'job_application_end'                      => esc_html__( 'After Application Button', 'cariera' ),

			// Single Job Page Sidebar.
			'cariera_single_job_listing_sidebar'       => '---' . esc_html__( 'Single Job Page Sidebar', 'cariera' ),
			'single_job_listing_meta_before'           => esc_html__( 'Before Job Meta', 'cariera' ),
			'single_job_listing_meta_start'            => esc_html__( 'Job Meta Start', 'cariera' ),
			'single_job_listing_meta_end'              => esc_html__( 'Job Meta End', 'cariera' ),
			'single_job_listing_meta_after'            => esc_html__( 'After Job Meta', 'cariera' ),
		];

		// Company Output Options.
		$company_options = [
			// Company Listing - List ver 1 & 2.
			'cariera_company_listings_output_label' => '---' . esc_html__( 'Company Listings - List Layout 1 & 2', 'cariera' ),
			'cariera_company_listing_meta_start'    => esc_html__( 'Company Listing Meta Start', 'cariera' ),
			'cariera_company_listing_meta_end'      => esc_html__( 'Company Listing Meta End', 'cariera' ),

			// Single Company Page.
			'cariera_single_company_page'           => '---' . esc_html__( 'Single Company Page', 'cariera' ),
			'cariera_single_company_contact_start'  => esc_html__( 'Company Contact Meta Start', 'cariera' ),
			'cariera_single_company_contact_end'    => esc_html__( 'Company Contact Meta End', 'cariera' ),
			'cariera_single_company_listing_start'  => esc_html__( 'Top of Company Listing', 'cariera' ),
			'cariera_single_company_listing_end'    => esc_html__( 'Bottom of Company Listing', 'cariera' ),
			'cariera_the_company_description'       => esc_html__( 'Bottom of Company Description', 'cariera' ),

			// Single Company Page Sidebar.
			'cariera_single_company_sidebar'        => '---' . esc_html__( 'Single Company Page Sidebar', 'cariera' ),
			'cariera_single_company_meta_start'     => esc_html__( 'Company Meta Start', 'cariera' ),
			'cariera_single_company_meta_end'       => esc_html__( 'Company Meta End', 'cariera' ),
		];

		// Resume Output Options.
		$resume_options = [
			// Resume Listing - List ver 1 & 2.
			'cariera_resume_listings_output_label' => '---' . esc_html__( 'Resume Listings - List Layout 1 & 2', 'cariera' ),
			'resume_listing_meta_start'            => esc_html__( 'Resume Listing Meta Start', 'cariera' ),
			'resume_listing_meta_end'              => esc_html__( 'Resume Listing Meta End', 'cariera' ),

			// Single Resume Page.
			'cariera_single_resume_page'           => '---' . esc_html__( 'Single Resume Page', 'cariera' ),
			'single_resume_contact_start'          => esc_html__( 'Resume Contact Meta Start', 'cariera' ),
			'single_resume_contact_end'            => esc_html__( 'Resume Contact Meta End', 'cariera' ),
			'single_resume_start'                  => esc_html__( 'Top of Resume Listing', 'cariera' ),
			'single_resume_end'                    => esc_html__( 'Bottom of Resume Listing', 'cariera' ),
			'the_resume_description_top'           => esc_html__( 'Top of Resume Description', 'cariera' ),
			'the_resume_description'               => esc_html__( 'Bottom of Resume Description', 'cariera' ),

			// Single Resume Page Sidebar.
			'cariera_single_resume_sidebar'        => '---' . esc_html__( 'Single Resume Page Sidebar', 'cariera' ),
			'single_resume_meta_start'             => esc_html__( 'Meta Start', 'cariera' ),
			'single_resume_meta_end'               => esc_html__( 'Meta End', 'cariera' ),
		];

		switch ( $list_field_group ) {
			case 'job':
				$cariera_output_options = $job_options;
				break;
			case 'company_fields':
				$cariera_output_options = $company_options;
				break;
			case 'resume_fields':
				$cariera_output_options = $resume_options;
				break;
			default:
				$cariera_output_options = array_merge( $job_options, $company_options, $resume_options );
		}

		return $cariera_output_options;
	}
}
