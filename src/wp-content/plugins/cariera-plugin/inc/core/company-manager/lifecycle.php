<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Lifecycle {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor.
	 *
	 * @since 1.4.7
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'initialize_lifecycle_hooks' ] );
	}

	/**
	 * Set up the lifecycle hooks for Companies.
	 *
	 * @since 1.4.7
	 */
	public function initialize_lifecycle_hooks() {
		add_action( 'transition_post_status', [ $this, 'transition_post_status' ], 10, 3 );
	}

	/**
	 * Capture the company post status transition to "publish" or "pending", and
	 * finalize the submission at that point.
	 *
	 * @since 1.4.7
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		if ( 'company' === $post->post_type ) {
			// Finalize public submission when new status is publish or pending.
			if ( in_array( $new_status, [ 'publish', 'pending' ], true ) ) {
				$this->finalize_submission( $post );
			}
		}
	}

	/**
	 * Finalize the submission for a public submitted company.
	 *
	 * @since 1.4.7
	 */
	private function finalize_submission( $company ) {
		// Only finalize if it is a public submission.
		if ( ! get_post_meta( $company->ID, '_public_submission', true ) ) {
			return;
		}

		// Only finalize once!
		if ( get_post_meta( $company->ID, '_submission_finalized', true ) ) {
			return;
		} else {
			update_post_meta( $company->ID, '_submission_finalized', true );
		}

		// Fire action after a company is submitted.
		do_action( 'cariera_company_submitted', $company->ID );
		delete_post_meta( $company->id, '_submitting_key' );
	}
}
