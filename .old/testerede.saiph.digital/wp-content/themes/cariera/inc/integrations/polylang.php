<?php

namespace Cariera\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Polylang {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since   1.6.3
	 * @version 1.7.0
	 */
	public function __construct() {
		if ( did_action( 'pll_init' ) ) {
			// Dashboard Pages.
			add_filter( 'cariera_dashboard_main_dashboard_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_employer_dashboard_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_company_dashboard_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_candidate_dashboard_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_job_alerts_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_resume_alerts_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_bookmarks_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_past_applications_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_listing_reports_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_user_packages_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_job_submit_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_company_submit_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_resume_submit_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_user_profile_page', [ $this, 'get_post_id' ] );

			// Edit single listing button page ids.
			add_filter( 'cariera_edit_single_resume_dashboard_id', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_edit_single_company_dashboard_id', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_edit_single_job_listing_dashboard_id', [ $this, 'get_post_id' ] );

			// User Menu items.
			add_filter( 'cariera_user_menu_dashboard_page_id', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_user_menu_employer_dashboard_page_id', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_user_menu_company_dashboard_page_id', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_user_menu_candidate_dashboard_page_id', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_user_menu_user_packages_page_id', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_user_menu_profile_dashboard_page_id', [ $this, 'get_post_id' ] );

			// Header CTA.
			add_filter( 'cariera_header_job_link', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_header_resume_link', [ $this, 'get_post_id' ] );

			// Extras.
			add_filter( 'cariera_login_register_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_register_privacy_policy_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_login_redirection', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_login_redirection_candidate', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_login_redirection_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_login_candi_redirection_page', [ $this, 'get_post_id' ] );
			add_filter( 'cariera_dashboard_page', [ $this, 'get_post_id' ] );
		}
	}

	/**
	 * Post translate function.
	 *
	 * @since   1.6.3
	 * @version 1.7.0
	 */
	public function get_post_id( $page_id, $post_type = 'page' ) {
		if ( function_exists( 'pll_get_post' ) ) {
			$page_id = pll_get_post( $page_id );
		}

		return absint( $page_id );
	}
}
