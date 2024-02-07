<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Notifications {

	/**
	 * Constructor.
	 *
	 * @since 1.4.7
	 * @version 1.7.0
	 */
	public function __construct() {
		add_action( 'cariera_company_submitted', [ $this, 'send_new_company_notification' ] );
	}

	/**
	 * New company notification
	 *
	 * @since 1.4.7
	 */
	public function send_new_company_notification( $company_id ) {

		if ( ! get_option( 'cariera_company_submission_notification' ) ) {
			return;
		}

		$company     = get_post( $company_id );
		$admin_email = get_option( 'admin_email' );
		$subject     = sprintf( esc_html__( 'New Company Submission: %s', 'cariera' ), $company->post_title );
		$headers[]   = 'Content-type: text/html; charset: ' . get_bloginfo( 'charset' );

		ob_start();
		get_job_manager_template(
			'emails/admin-new-company.php',
			[
				'company'    => $company,
				'company_id' => $company_id,
			],
			'wp-job-manager-companies'
		);

		$message = ob_get_clean();

		wp_mail(
			$admin_email,
			apply_filters( 'cariera_new_company_notification_subject', $subject, $company_id ),
			$message,
			apply_filters( 'cariera_new_company_notification_headers', $headers, $company_id )
		);
	}
}
