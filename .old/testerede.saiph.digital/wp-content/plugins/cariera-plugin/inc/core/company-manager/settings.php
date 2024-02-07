<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager_Settings' ) ) {
	include JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-settings.php';
}

class Settings extends \WP_Job_Manager_Settings {

	/**
	 * Construct
	 *
	 * @since  1.4.4
	 */
	public function __construct() {
		$this->settings_group = 'cariera_company_manager';

		// Register settings.
		add_action( 'admin_init', [ $this, 'register_settings' ] );

		// Add settings page to menu.
		add_action( 'admin_menu', [ $this, 'add_menu_item' ], 12 );
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @since   1.4.4
	 * @version 1.5.2
	 */
	public function add_menu_item() {
		add_submenu_page( 'edit.php?post_type=company', esc_html__( 'Settings', 'cariera' ), esc_html__( 'Settings', 'cariera' ), 'manage_options', 'cariera_company_manager_settings', [ $this, 'settings_output' ] );
	}

	/**
	 * Output settings page
	 *
	 * @since 1.5.2
	 */
	public function settings_output() {
		if ( ! cariera_core_theme_status() ) { ?>
			<div class="activate-theme-alert">
				<p><?php esc_html_e( 'Please activate the theme to be able to edit the core settings.', 'cariera' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cariera_theme' ) ); ?>" class="cariera-btn"><?php echo esc_html( 'Activate Cariera' ); ?></a>
			</div>

			<?php
			return;
		}

		$this->output();
	}

	/**
	 * Initializes the configuration for the plugin's setting fields.
	 *
	 * @since   1.4.4
	 * @version 1.6.3
	 */
	protected function init_settings() {
		// Prepare roles option.
		$roles         = get_editable_roles();
		$account_roles = [];

		$singular = cariera_get_company_manager_singular_label();
		$plural   = cariera_get_company_manager_plural_label();

		foreach ( $roles as $key => $role ) {
			if ( 'administrator' === $key ) {
				continue;
			}
			$account_roles[ $key ] = $role['name'];
		}

		$prefix = 'cariera_';

		$this->settings = apply_filters(
			'cariera_company_manager_settings',
			[

				/* COMPANY LISTINGS OPTIONS */
				'company_listings'            => [
					esc_html__( 'Company Listings', 'cariera' ),
					[
						[
							'name'        => $prefix . 'companies_per_page',
							'std'         => '10',
							'placeholder' => '',
							'label'       => esc_html__( 'Listings Per Page', 'cariera' ),
							'desc'        => esc_html__( 'Number of job listings to display per page.', 'cariera' ),
							'attributes'  => [],
						],
						[
							'name'     => $prefix . 'company_category',
							'std'      => '1',
							'label'    => esc_html__( 'Company Category', 'cariera' ),
							'cb_label' => esc_html__( 'Enable Company Category', 'cariera' ),
							'desc'     => esc_html__( 'Enabling this option will show the Company Categories in sidebar, job posting and in the backend.', 'cariera' ),
							'type'     => 'checkbox',
						],
						[
							'name'     => $prefix . 'company_team_size',
							'std'      => '1',
							'label'    => esc_html__( 'Team Size', 'cariera' ),
							'cb_label' => esc_html__( 'Enable Team Size', 'cariera' ),
							'desc'     => esc_html__( 'Enabling this option will show the Team Size in sidebar, job posting and in the backend.', 'cariera' ),
							'type'     => 'checkbox',
						],
					],
				],

				/* SINGLE COMPANY PAGE OPTIONS */
				'company_page'                => [
					esc_html__( 'Company Single Page', 'cariera' ),
					[
						[
							'name'    => $prefix . 'single_company_layout',
							'std'     => 'v1',
							'label'   => esc_html__( 'Single Company Layout', 'cariera' ),
							'desc'    => esc_html__( 'Select the default layout version for your single company page.', 'cariera' ),
							'type'    => 'select',
							'options' => [
								'v1' => esc_html__( 'Version 1', 'cariera' ),
								'v2' => esc_html__( 'Version 2', 'cariera' ),
								'v3' => esc_html__( 'Version 3', 'cariera' ),
							],
						],
						[
							'name'    => $prefix . 'single_company_contact_form',
							'std'     => '',
							'label'   => esc_html__( 'Single Company Contact Form', 'cariera' ),
							'desc'    => esc_html__( 'Select the form for single company contact form. This lets the plugin know the contact form of single company.', 'cariera' ),
							'type'    => 'select',
							'options' => function_exists( 'cariera_get_forms' ) ? cariera_get_forms() : [ 0 => esc_html__( 'Please select a form', 'cariera' ) ],
						],
						[
							'name'       => $prefix . 'single_company_active_jobs',
							'std'        => '1',
							'label'      => esc_html__( 'Active Jobs', 'cariera' ),
							'cb_label'   => esc_html__( 'Display active Jobs', 'cariera' ),
							'desc'       => sprintf( esc_html__( 'If the %s has active Jobs, a list will be output at the bottom of the page.', 'cariera' ), $singular ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],

				/* COMPANY SUBMISSION OPTIONS */
				'company_submission'          => [
					esc_html__( 'Company Submission', 'cariera' ),
					[
						[
							'name'       => $prefix . 'company_user_requires_account',
							'std'        => '1',
							'label'      => esc_html__( 'Account Required', 'cariera' ),
							'cb_label'   => esc_html__( 'Submitting listings requires an account', 'cariera' ),
							'desc'       => esc_html__( 'Limits company submissions to registered, logged-in users.', 'cariera' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'enable_company_registration',
							'std'        => '1',
							'label'      => esc_html__( 'Account Creation', 'cariera' ),
							'cb_label'   => esc_html__( 'Allow account creation', 'cariera' ),
							'desc'       => esc_html__( 'Includes account creation on the company submission form, to allow non-registered users to create an account and submit a company simultaneously.', 'cariera' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'    => $prefix . 'company_registration_role',
							'std'     => 'employer',
							'label'   => esc_html__( 'Account Role', 'cariera' ),
							'desc'    => esc_html__( 'If you enable registration on your submission form, choose a role for the new user.', 'cariera' ),
							'type'    => 'select',
							'options' => $account_roles,
						],
						[
							'name'       => $prefix . 'company_submission_requires_approval',
							'std'        => '1',
							'label'      => esc_html__( 'Moderate New Listings', 'cariera' ),
							'cb_label'   => esc_html__( 'New submissions require admin approval', 'cariera' ),
							'desc'       => esc_html__( 'Sets all new submissions to "pending." They will not appear on your site until an admin approves them.', 'cariera' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'company_user_can_edit_pending_submissions',
							'std'        => '0',
							'label'      => esc_html__( 'Allow Pending Edits', 'cariera' ),
							'cb_label'   => esc_html__( 'Allow editing of pending listings', 'cariera' ),
							'desc'       => esc_html__( 'Users can continue to edit pending listings until they are approved by an admin.', 'cariera' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'company_user_edit_published_submissions',
							'std'        => 'yes',
							'label'      => esc_html__( 'Allow Published Edits', 'cariera' ),
							'cb_label'   => esc_html__( 'Allow editing of published listings', 'cariera' ),
							'desc'       => esc_html__( 'Choose whether published company listings can be edited and if edits require admin approval. When moderation is required, the original company listings will be unpublished while edits await admin approval.', 'cariera' ),
							'type'       => 'radio',
							'options'    => [
								'no'            => esc_html__( 'Users cannot edit', 'cariera' ),
								'yes'           => esc_html__( 'Users can edit without admin approval', 'cariera' ),
								'yes_moderated' => esc_html__( 'Users can edit, but edits require admin approval', 'cariera' ),
							],
							'attributes' => [],
						],
						[
							'name'        => $prefix . 'company_submission_limit',
							'std'         => '',
							'label'       => esc_html__( 'Listing Limit', 'cariera' ),
							'desc'        => sprintf( esc_html__( 'Limit users submission by adding a max number. Can be left blank to allow unlimited %s per account.', 'cariera' ), $plural ),
							'attributes'  => [],
							'placeholder' => esc_html__( 'No limit', 'cariera' ),
						],
						[
							'name'     => $prefix . 'user_specific_company',
							'std'      => '1',
							'label'    => esc_html__( 'User Specific Companies', 'cariera' ),
							'cb_label' => esc_html__( 'Enable User Specific Companies', 'cariera' ),
							'desc'     => esc_html__( 'If enabled the user will be able to see only the companies created by the user under the "existing company". If disabled all companies will be visible, even for non logged in users.', 'cariera' ),
							'type'     => 'checkbox',
						],
						[
							'name'       => $prefix . 'show_agreement_company_submission',
							'std'        => '0',
							'label'      => esc_html__( 'Terms and Conditions Checkbox', 'cariera' ),
							'cb_label'   => esc_html__( 'Enable required Terms and Conditions checkbox on the form', 'cariera' ),
							'desc'       => sprintf(
								// translators: Placeholder %s is the URL to the page in WP Job Manager's settings to set the pages.
								__( 'Require a Terms and Conditions checkbox to be marked before a company can be submitted. The linked page can be set from the <a href="%s">WP Job Manager\'s settings</a>.', 'cariera' ),
								esc_url( admin_url( 'edit.php?post_type=job_listing&page=job-manager-settings#settings-job_pages' ) )
							),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						'recaptcha' => [
							'name'       => $prefix . 'enable_recaptcha_company_submission',
							'std'        => '0',
							'label'      => esc_html__( 'reCAPTCHA', 'cariera' ),
							'cb_label'   => esc_html__( 'Display a reCAPTCHA field on company submission form.', 'cariera' ),
							'desc'       => sprintf(
								// translators: Placeholder %s is the URL to the page in WP Job Manager's settings to make the change.
								__( 'This will help prevent bots from submitting companies. You must have entered a valid site key and secret key in <a href="%s">WP Job Manager\'s settings</a>.', 'cariera' ),
								esc_url( admin_url( 'edit.php?post_type=job_listing&page=job-manager-settings#settings-recaptcha' ) )
							),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],

				/* WPJM INTEGRATION OPTIONS */
				'company_integration'         => [
					esc_html__( 'WPJM Integration', 'cariera' ),
					[
						[
							'name'     => $prefix . 'company_manager_integration',
							'std'      => '1',
							'label'    => esc_html__( 'Cariera Company Manager', 'cariera' ),
							'cb_label' => esc_html__( 'WPJM Integration', 'cariera' ),
							'desc'     => sprintf( esc_html__( 'Replace all the default %s fields from WP Job Manager with the main Cariera Company Manager fields.', 'cariera' ), $singular ),
							'type'     => 'checkbox',
						],
						[
							'name'     => $prefix . 'add_new_company',
							'std'      => '1',
							'label'    => sprintf( esc_html__( 'Add New %s', 'cariera' ), $singular ),
							'cb_label' => sprintf( esc_html__( 'Enable new %s submission', 'cariera' ), $singular ),
							'desc'     => sprintf( esc_html__( 'If disabled you will not be able to post a new %s on job submission.', 'cariera' ), $singular ),
							'type'     => 'checkbox',
						],
						[
							'name'       => $prefix . 'job_submit_company_required',
							'std'        => '1',
							'label'      => sprintf( esc_html__( '%s Required', 'cariera' ), $singular ),
							'cb_label'   => sprintf( esc_html__( 'Require the user to select an existing %s, or add a new one when submitting a new job.', 'cariera' ), $singular ),
							'desc'       => sprintf( esc_html__( 'When enabled, this will set the dropdown to select existing %1$s from (or add new one) as a required field, so the user will have to select an existing %2$s or add a new one.', 'cariera' ), $singular, $singular ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],

				/* PAGES OPTIONS */
				'company_pages'               => [
					esc_html__( 'Pages', 'cariera' ),
					[
						[
							'name'  => $prefix . 'submit_company_page',
							'std'   => '',
							'label' => esc_html__( 'Submit Company Page', 'cariera' ),
							'desc'  => esc_html__( 'Select the page where you have placed the [submit_company] shortcode. This lets the plugin know the location of the company submission page.', 'cariera' ),
							'type'  => 'page',
						],
						[
							'name'  => $prefix . 'company_dashboard_page',
							'std'   => '',
							'label' => esc_html__( 'Company Dashboard Page', 'cariera' ),
							'desc'  => esc_html__( 'Select the page where you have placed the [company_dashboard] shortcode. This lets the plugin know the location of the company dashboard page.', 'cariera' ),
							'type'  => 'page',
						],
						[
							'name'  => $prefix . 'companies_page',
							'std'   => '',
							'label' => esc_html__( 'Company Listings Page', 'cariera' ),
							'desc'  => esc_html__( 'Select the page where you have placed the [companies] shortcode or companies element via Elementor. This lets the plugin know the location of the company listings page.', 'cariera' ),
							'type'  => 'page',
						],
					],
				],

				/* VISIBILITY OPTIONS */
				'company_visibility'          => [
					sprintf( esc_html__( '%s Visibility', 'cariera' ), $singular ),
					[
						[
							'name'              => $prefix . 'company_manager_browse_company_capability',
							'std'               => [],
							'label'             => esc_html__( 'Browse Capability', 'cariera' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to browse. If no value is selected, everyone (including logged out guests) will be able to browse companies.', 'cariera' ), 'https://wordpress.org/support/article/roles-and-capabilities/' ),
						],
						[
							'name'              => $prefix . 'company_manager_view_company_capability',
							'std'               => [],
							'label'             => esc_html__( 'View Capability', 'cariera' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view a single company. If no value is selected, everyone (including logged out guests) will be able to view companies.', 'cariera' ), 'https://wordpress.org/support/article/roles-and-capabilities/' ),
						],
						[
							'name'              => $prefix . 'company_manager_contact_company_capability',
							'std'               => [],
							'label'             => esc_html__( 'Contact Capability', 'cariera' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view a contact details on a single company. If no value is selected, contact details will be publicly available.', 'cariera' ), 'https://wordpress.org/support/article/roles-and-capabilities/' ),
						],
						[
							'name'       => $prefix . 'company_manager_discourage_company_search_indexing',
							'std'        => '0',
							'label'      => esc_html__( 'Search Engine Visibility', 'cariera' ),
							'cb_label'   => esc_html__( 'Discourage search engines from indexing company listings', 'cariera' ),
							'desc'       => esc_html__( 'Search engines choose whether to honor this request.', 'cariera' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],

				/* VISIBILITY OPTIONS */
				'company_email_notifications' => [
					esc_html__( 'Email Notifications', 'cariera' ),
					[
						[
							'name'       => $prefix . 'company_submission_notification',
							'std'        => '1',
							'label'      => esc_html__( 'New Submission', 'cariera' ),
							'cb_label'   => esc_html__( 'Admin Notice on New Listing', 'cariera' ),
							'desc'       => esc_html__( 'Send a notice to the site administrator when a new company listing is submitted on the frontend.', 'cariera' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],

				/* OTHER OPTIONS */
				'company_other'               => [
					esc_html__( 'Other', 'cariera' ),
					[
						[
							'name'        => $prefix . 'company_manager_cpt_singular_label',
							'std'         => esc_html__( 'Company', 'cariera' ),
							'placeholder' => esc_html__( 'Company', 'cariera' ),
							'label'       => esc_html__( 'Singular Label', 'cariera' ),
							'desc'        => esc_html__( 'You can change the singular label and use a custom label instead of "Company".', 'cariera' ),
							'attributes'  => [],
						],
						[
							'name'        => $prefix . 'company_manager_cpt_plural_label',
							'std'         => esc_html__( 'Companies', 'cariera' ),
							'placeholder' => esc_html__( 'Companies', 'cariera' ),
							'label'       => esc_html__( 'Plural Label', 'cariera' ),
							'desc'        => esc_html__( 'You can change the plural label and use a custom label instead of "Companies".', 'cariera' ),
							'attributes'  => [],
						],
					],
				],

			]
		);
	}
}
