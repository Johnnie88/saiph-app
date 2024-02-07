<?php

namespace Cariera\Integrations\WPJM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bookmarks {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since   1.7.0
	 * @version 1.7.0
	 */
	public function __construct() {
		if ( ! class_exists( 'WP_Job_Manager_Bookmarks' ) ) {
			return;
		}

		global $job_manager_bookmarks;

		// Remove Bookmark Form.
		remove_action( 'single_job_listing_meta_after', [ $job_manager_bookmarks, 'bookmark_form' ] );
		remove_action( 'single_resume_start', [ $job_manager_bookmarks, 'bookmark_form' ] );

		// Add Bookmark trigger and popup.
		add_action( 'cariera_bookmark_hook', [ $this, 'bookmark_trigger' ], 10 );
		add_action( 'cariera_bookmark_hook', [ $this, 'bookmark_popup' ], 11 );
		add_action( 'cariera_bookmark_popup_form', [ $job_manager_bookmarks, 'bookmark_form' ] );
	}

	/**
	 * Bookmark button trigger
	 *
	 * @since   1.3.3
	 * @version 1.7.0
	 */
	public function bookmark_trigger() {
		get_job_manager_template( 'bookmark-trigger.php', [], 'wp-job-manager-bookmarks' );
	}

	/**
	 * Bookmark Popup
	 *
	 * @since   1.3.3
	 * @version 1.7.0
	 */
	public function bookmark_popup() {
		get_job_manager_template( 'bookmark-popup.php', [], 'wp-job-manager-bookmarks' );
	}
}
