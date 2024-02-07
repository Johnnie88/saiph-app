<?php

namespace Cariera_Core\Core\Resume_Manager;

use Cariera_Core\Core\Resume_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings extends Resume_Manager {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 *
	 * @since 1.4.5
	 */
	public function __construct() {
		add_filter( 'resume_manager_settings', [ $this, 'settings' ] );
	}

	/**
	 * Add extra settings to Resume Options
	 *
	 * @since 1.3.0
	 */
	public function settings( $settings = [] ) {

		$settings['resume_listings'][1][] = [
			'name'       => 'cariera_resume_manager_enable_education',
			'std'        => '1',
			'label'      => esc_html__( 'Education', 'cariera' ),
			'cb_label'   => esc_html__( 'Enable listing education', 'cariera' ),
			'desc'       => esc_html__( 'This lets users select their education when submitting a resume. Note: an admin has to create experience before site users can select them.', 'cariera' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];
		$settings['resume_listings'][1][] = [
			'name'       => 'cariera_resume_manager_enable_experience',
			'std'        => '1',
			'label'      => esc_html__( 'Experience', 'cariera' ),
			'cb_label'   => esc_html__( 'Enable listing experience', 'cariera' ),
			'desc'       => esc_html__( 'This lets users select their experience when submitting a resume. Note: an admin has to create experience before site users can select them.', 'cariera' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];
		$settings['resume_listings'][1][] = [
			'name'    => 'resume_manager_single_resume_contact_form',
			'std'     => '',
			'label'   => esc_html__( 'Single Resume Contact Form', 'cariera' ),
			'desc'    => esc_html__( 'Select the contact form that you want to show on a single resume page.', 'cariera' ),
			'type'    => 'select',
			'options' => cariera_get_forms(),
		];
		$settings['resume_listings'][1][] = [
			'name'       => 'cariera_resume_manager_contact_owner',
			'std'        => '0',
			'label'      => esc_html__( 'Owner Contact', 'cariera' ),
			'cb_label'   => esc_html__( 'Hide Contact to Owner', 'cariera' ),
			'desc'       => esc_html__( 'When enabled the "contact button & form" of the Resume will be hidden from the owner of the Resume. This will avoid Candidates being able to send emails to themselves via their own Resume.', 'cariera' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];
		$settings['resume_listings'][1][] = [
			'name'    => 'cariera_resume_manager_single_resume_layout',
			'std'     => 'v1',
			'label'   => esc_html__( 'Single Resume Layout', 'cariera' ),
			'desc'    => esc_html__( 'Select the default layout version for your single resume page.', 'cariera' ),
			'type'    => 'select',
			'options' => [
				'v1' => esc_html__( 'Version 1', 'cariera' ),
				'v2' => esc_html__( 'Version 2', 'cariera' ),
				'v3' => esc_html__( 'Version 3', 'cariera' ),
			],
		];

		// Email Setting.
		$settings['email_notifications'][1][] = [
			'name'       => 'cariera_resume_manager_approved_resume_notification',
			'std'        => '1',
			'label'      => esc_html__( 'Approved Resume', 'cariera' ),
			'cb_label'   => esc_html__( 'Approved Resume Notification', 'cariera' ),
			'desc'       => esc_html__( 'When enabled the Candidate will receive an email notification when their resume get\'s approved.', 'cariera' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];
		$settings['email_notifications'][1][] = [
			'name'       => 'cariera_resume_manager_expired_resume_notification',
			'std'        => '1',
			'label'      => esc_html__( 'Expired Resume', 'cariera' ),
			'cb_label'   => esc_html__( 'Expired Resume Notification', 'cariera' ),
			'desc'       => esc_html__( 'When enabled the Candidate will receive an email notification when their resume get\'s expired.', 'cariera' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];

		return $settings;
	}
}
