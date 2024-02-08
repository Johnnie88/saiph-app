<?php

namespace Cariera_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

	/**
	 * Constructor function.
	 *
	 * @since 1.7.2
	 */
	public function __construct() {
		add_shortcode( 'cariera_job_search_form', [ $this, 'job_search_form' ] );
		add_shortcode( 'cariera_user_packages', [ $this, 'user_packages' ] );
		add_shortcode( 'cariera_job_sidebar_search', [ $this, 'job_sidebar_search' ] );
		add_shortcode( 'cariera_resume_sidebar_search', [ $this, 'resume_sidebar_search' ] );
		add_shortcode( 'cariera_company_sidebar_search', [ $this, 'company_sidebar_search' ] );
	}

	/**
	 * Job Search form shortcode
	 *
	 * @since   1.4.5
	 * @version 1.7.2
	 *
	 * @param array $atts
	 */
	public function job_search_form( $atts ) {
		$args = shortcode_atts(
			[
				'search_style' => 'stlye-1',
				'location'     => '',
				'region'       => '',
				'categories'   => '',
			],
			$atts
		);

		cariera_get_template(
			'wpjm/job-search-form.php',
			[
				'search_style' => $args['search_style'],
				'location'     => $args['location'],
				'region'       => $args['region'],
				'categories'   => $args['categories'],
			]
		);
	}

	/**
	 * List all the bought packages of a user
	 *
	 * @since   1.5.4
	 * @version 1.7.2
	 */
	public function user_packages() {
		if ( ! class_exists( 'WP_Job_Manager' ) || ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Paid_Listings' ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			cariera_get_template_part( 'account/account-signin' );
		} else {
			cariera_get_template_part( 'account/user-packages' );
		}
	}

	/**
	 * Job Sidebar Search
	 *
	 * @since   1.5.5
	 * @version 1.7.2
	 */
	public function job_sidebar_search() {
		cariera_get_template_part( 'wpjm/job-sidebar-search' );
	}

	/**
	 * Resume Sidebar Search
	 *
	 * @since   1.5.5
	 * @version 1.7.2
	 */
	public function resume_sidebar_search() {
		cariera_get_template_part( 'wpjm/resume-sidebar-search' );
	}

	/**
	 * Company Sidebar Search
	 *
	 * @since   1.5.5
	 * @version 1.7.2
	 */
	public function company_sidebar_search() {
		cariera_get_template_part( 'wpjm/company-sidebar-search' );
	}
}
