<?php

namespace Cariera_Core\Core\Company_Manager\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Submit_Company extends \WP_Job_Manager_Form {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Form name slug.
	 *
	 * @var string
	 */
	public $form_name = 'submit-company';

	/**
	 * Company Listing ID
	 *
	 * @var int
	 */
	protected $company_id;

	/**
	 * Job Listing ID
	 *
	 * @var int
	 */
	protected $job_id;

	/**
	 * Preview job (unused)
	 *
	 * @access protected
	 * @var string
	 */
	protected $preview_company;

	/**
	 * Constructor.
	 *
	 * @since 1.4.4
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'process' ] );
		add_action( 'submit_company_form_start', [ $this, 'output_submit_form_nonce_field' ] );
		add_action( 'preview_company_form_start', [ $this, 'output_preview_form_nonce_field' ] );

		if ( $this->use_agreement_checkbox() ) {
			add_action( 'submit_company_form_company_fields_end', [ $this, 'display_agreement_checkbox_field' ] );
			add_filter( 'submit_company_form_validate_fields', [ $this, 'validate_agreement_checkbox' ] );
		}

		if ( $this->use_recaptcha_field() ) {
			add_action( 'submit_company_form_company_fields_end', [ $this, 'display_recaptcha_field' ] );
			add_action( 'submit_company_form_validate_fields', [ $this, 'validate_recaptcha_field' ] );
		}

		$this->steps = (array) apply_filters(
			'cariera_submit_company_steps',
			[
				'submit'  => [
					'name'     => esc_html__( 'Submit Details', 'cariera' ),
					'view'     => [ $this, 'submit' ],
					'handler'  => [ $this, 'submit_handler' ],
					'priority' => 10,
				],
				'preview' => [
					'name'     => esc_html__( 'Preview', 'cariera' ),
					'view'     => [ $this, 'preview' ],
					'handler'  => [ $this, 'preview_handler' ],
					'priority' => 20,
				],
				'done'    => [
					'name'     => esc_html__( 'Done', 'cariera' ),
					'view'     => [ $this, 'done' ],
					'handler'  => '',
					'priority' => 30,
				],
			]
		);

		uasort( $this->steps, [ $this, 'sort_by_priority' ] );

		// Get step/company.
		if ( isset( $_POST['step'] ) ) {
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( sanitize_text_field( $_POST['step'] ), array_keys( $this->steps ), true );
		} elseif ( ! empty( $_REQUEST['step'] ) ) {
			$this->step = is_numeric( $_POST['step'] ) ? max( absint( $_POST['step'] ), 0 ) : array_search( sanitize_text_field( $_POST['step'] ), array_keys( $this->steps ), true );
		}

		$this->job_id     = ! empty( $_REQUEST['job_id'] ) ? absint( $_REQUEST['job_id'] ) : 0;
		$this->company_id = ! empty( $_REQUEST['company_id'] ) ? absint( $_REQUEST['company_id'] ) : 0;

		if ( ! cariera_user_can_edit_company( $this->company_id ) ) {
			$this->company_id = 0;
		}

		// Load company details.
		if ( $this->company_id ) {
			$company_status = get_post_status( $this->company_id );
			if ( 'expired' === $company_status ) {
				if ( ! cariera_user_can_edit_company( $this->company_id ) ) {
					$this->company_id = 0;
					$this->job_id     = 0;
					$this->step       = 0;
				}
			} elseif ( 0 === $this->step && ! in_array( $company_status, apply_filters( 'cariera_valid_submit_company_statuses', [ 'preview' ] ), true ) && empty( $_POST['company_application_submit_button'] ) ) {
				$this->company_id = 0;
				$this->job_id     = 0;
				$this->step       = 0;
			}
		}
	}

	/**
	 * Get the submitted company ID
	 *
	 * @since 1.4.4
	 */
	public function get_company_id() {
		return absint( $this->company_id );
	}

	/**
	 * Get the job ID if applying
	 *
	 * @since 1.4.4
	 */
	public function get_job_id() {
		return absint( $this->job_id );
	}

	/**
	 * Get a field from either company manager or job manager
	 *
	 * @since 1.4.4
	 */
	public function get_field_template( $key, $field ) {
		switch ( $field['type'] ) {
			case 'company-select':
				get_job_manager_template(
					'form-fields/company-select-field.php',
					[
						'key'   => $key,
						'field' => $field,
						'class' => $this,
					],
					'wp-job-manager-companies'
				);
				break;
			default:
				get_job_manager_template(
					'form-fields/' . $field['type'] . '-field.php',
					[
						'key'   => $key,
						'field' => $field,
						'class' => $this,
					]
				);
				break;
		}
	}

	/**
	 * Init fields function.
	 *
	 * @since   1.4.4
	 * @version 1.5.2
	 */
	public function init_fields() {
		if ( $this->fields ) {
			return;
		}

		$this->fields = apply_filters(
			'cariera_submit_company_form_fields',
			[
				'company_fields' => [
					'company_name'         => [
						'label'       => esc_html__( 'Company Name', 'cariera' ),
						'type'        => 'text',
						'required'    => true,
						'placeholder' => esc_html__( 'The full Company name', 'cariera' ),
						'priority'    => 1,
					],
					'company_website'      => [
						'label'       => esc_html__( 'Company Website', 'cariera' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => esc_html__( 'https://domain.com', 'cariera' ),
						'priority'    => 2,
					],
					'company_email'        => [
						'label'       => esc_html__( 'Company Email', 'cariera' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => esc_html__( 'you@yourdomain.com', 'cariera' ),
						'priority'    => 2,
					],
					'company_phone'        => [
						'label'       => esc_html__( 'Company Contact Phone', 'cariera' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => esc_html__( '+1 (202) 555 0183', 'cariera' ),
						'priority'    => 2,
					],
					'company_location'     => [
						'label'       => esc_html__( 'Location', 'cariera' ),
						'type'        => 'text',
						'required'    => false,
						'placeholder' => esc_html__( 'e.g. "London, UK"', 'cariera' ),
						'priority'    => 3,
					],
					'company_since'        => [
						'label'       => esc_html__( 'Since', 'cariera' ),
						'type'        => 'date',
						'required'    => false,
						'placeholder' => esc_html__( 'Established date/year', 'cariera' ),
						'priority'    => 3,
					],
					'company_category'     => [
						'label'       => esc_html__( 'Company category', 'cariera' ),
						'type'        => 'term-multiselect',
						'taxonomy'    => 'company_category',
						'required'    => true,
						'placeholder' => '',
						'default'     => '',
						'priority'    => 4,
					],
					'company_team_size'    => [
						'label'       => esc_html__( 'Company team size', 'cariera' ),
						'type'        => 'term-select',
						'taxonomy'    => 'company_team_size',
						'required'    => true,
						'placeholder' => '',
						'default'     => '',
						'priority'    => 4,
					],
					'company_content'      => [
						'label'       => esc_html__( 'Company Description', 'cariera' ),
						'type'        => 'wp-editor',
						'required'    => true,
						'placeholder' => '',
						'priority'    => 5,
					],
					'company_video'        => [
						'label'       => esc_html__( 'Video', 'cariera' ),
						'type'        => 'text',
						'required'    => false,
						'priority'    => 6,
						'placeholder' => esc_html__( 'A link to a video about the company', 'cariera' ),
					],
					'company_facebook'     => [
						'label'       => esc_html__( 'Facebook', 'cariera' ),
						'type'        => 'text',
						'required'    => false,
						'priority'    => 6,
						'placeholder' => esc_html__( 'Facebook Page URL', 'cariera' ),
					],
					'company_twitter'      => [
						'label'       => esc_html__( 'X', 'cariera' ),
						'type'        => 'text',
						'required'    => false,
						'priority'    => 6,
						'placeholder' => esc_html__( 'X Page URL', 'cariera' ),
					],
					'company_linkedin'     => [
						'label'       => esc_html__( 'LinkedIn', 'cariera' ),
						'type'        => 'text',
						'required'    => false,
						'priority'    => 6,
						'placeholder' => esc_html__( 'LinkedIn Page URL', 'cariera' ),
					],
					'company_instagram'    => [
						'label'       => esc_html__( 'Instagram', 'cariera' ),
						'type'        => 'text',
						'required'    => false,
						'priority'    => 6,
						'placeholder' => esc_html__( 'Instagram Page URL', 'cariera' ),
					],
					'company_logo'         => [
						'label'              => esc_html__( 'Logo', 'cariera' ),
						'type'               => 'file',
						'required'           => false,
						'placeholder'        => '',
						'priority'           => 7,
						'ajax'               => true,
						'allowed_mime_types' => [
							'jpg'  => 'image/jpeg',
							'jpeg' => 'image/jpeg',
							'gif'  => 'image/gif',
							'png'  => 'image/png',
						],
					],
					'company_header_image' => [
						'label'              => esc_html__( 'Cover Image', 'cariera' ),
						'type'               => 'file',
						'required'           => false,
						'description'        => esc_html__( 'The cover image size should be max 1920x400px', 'cariera' ),
						'priority'           => 8,
						'ajax'               => true,
						'multiple'           => false,
						'allowed_mime_types' => [
							'jpg'  => 'image/jpeg',
							'jpeg' => 'image/jpeg',
							'gif'  => 'image/gif',
							'png'  => 'image/png',
						],
					],
				],
			]
		);

		if ( ! get_option( 'cariera_company_category' ) || 0 === intval( wp_count_terms( 'company_category' ) ) ) {
			unset( $this->fields['company_fields']['company_category'] );
		}

		if ( ! get_option( 'cariera_company_team_size' ) || 0 === intval( wp_count_terms( 'company_team_size' ) ) ) {
			unset( $this->fields['company_fields']['company_team_size'] );
		}
	}

	/**
	 * Reset the `fields` variable so it gets reinitialized. For testing!
	 *
	 * @since 1.4.4
	 */
	public function reset_fields() {
		$this->fields = null;
	}

	/**
	 * Validate the posted fields
	 *
	 * @since 1.4.4
	 */
	public function validate_fields( $values ) {
		foreach ( $this->fields as $group_key => $fields ) {
			foreach ( $fields as $key => $field ) {
				if ( $field['required'] && empty( $values[ $group_key ][ $key ] ) ) {
					return new \WP_Error( 'validation-error', sprintf( esc_html__( '%s is a required field', 'cariera' ), $field['label'] ) );
				}

				if ( ! empty( $field['taxonomy'] ) && in_array( $field['type'], [ 'term-checklist', 'term-select', 'term-multiselect' ], true ) ) {
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						foreach ( $values[ $group_key ][ $key ] as $term ) {
							if ( ! term_exists( $term, $field['taxonomy'] ) ) {
								return new \WP_Error( 'validation-error', sprintf( esc_html__( '%s is invalid', 'cariera' ), $field['label'] ) );
							}
						}
					} elseif ( ! empty( $values[ $group_key ][ $key ] ) ) {
						if ( ! term_exists( $values[ $group_key ][ $key ], $field['taxonomy'] ) ) {
							return new \WP_Error( 'validation-error', sprintf( esc_html__( '%s is invalid', 'cariera' ), $field['label'] ) );
						}
					}
				}

				if ( 'company_email' === $key ) {
					if ( ! empty( $values[ $group_key ][ $key ] ) && ! is_email( $values[ $group_key ][ $key ] ) ) {
						throw new \Exception( esc_html__( 'Please enter a valid email address', 'cariera' ) );
					}
				}

				if ( 'file' === $field['type'] ) {
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						$check_value = array_filter( $values[ $group_key ][ $key ] );
					} else {
						$check_value = array_filter( [ $values[ $group_key ][ $key ] ] );
					}
					if ( ! empty( $check_value ) ) {
						foreach ( $check_value as $file_url ) {
							if ( is_numeric( $file_url ) ) {
								continue;
							}
							$file_url = esc_url( $file_url, [ 'http', 'https' ] );
							if ( empty( $file_url ) ) {
								throw new \Exception( esc_html__( 'Invalid attachment provided.', 'cariera' ) );
							}
						}
					}
				}
			}
		}

		return apply_filters( 'cariera_submit_company_form_validate_fields', true, $this->fields, $values );
	}

	/**
	 * Get categories.
	 *
	 * @since 1.4.4
	 */
	private function company_categories() {
		$options = [];
		$terms   = get_company_categories();
		foreach ( $terms as $term ) {
			$options[ $term->slug ] = $term->name;
		}

		return $options;
	}

	/**
	 * Submit Step
	 */
	public function submit() {
		global $job_manager, $post;

		$this->init_fields();

		// Load data if neccessary.
		if ( $this->company_id ) {
			$company = get_post( $this->company_id );
			foreach ( $this->fields as $group_key => $fields ) {
				foreach ( $fields as $key => $field ) {
					switch ( $key ) {
						case 'company_name':
							$this->fields[ $group_key ][ $key ]['value'] = $company->post_title;
							break;
						case 'company_content':
							$this->fields[ $group_key ][ $key ]['value'] = $company->post_content;
							break;
						case 'company_category':
							$this->fields[ $group_key ][ $key ]['value'] = wp_get_object_terms( $company->ID, 'company_category', [ 'fields' => 'ids' ] );
							break;
						case 'company_team_size':
							$this->fields[ $group_key ][ $key ]['value'] = wp_get_object_terms( $company->ID, 'company_team_size', [ 'fields' => 'ids' ] );
							break;
						case 'company_logo':
							$this->fields[ $group_key ][ $key ]['value'] = has_post_thumbnail( $company->ID ) ? get_post_thumbnail_id( $company->ID ) : get_post_meta( $company->ID, '_' . $key, true );
							break;
						default:
							$this->fields[ $group_key ][ $key ]['value'] = get_post_meta( $company->ID, '_' . $key, true );
							break;
					}
				}
			}
			$this->fields = apply_filters( 'cariera_submit_company_form_fields_get_company_data', $this->fields, $company );

			// Get user meta.
		} elseif ( is_user_logged_in() && empty( $_POST['submit_company'] ) ) {
			$user = wp_get_current_user();
			foreach ( $this->fields as $group_key => $fields ) {
				foreach ( $fields as $key => $field ) {
					switch ( $key ) {
						case 'company_email':
							$this->fields[ $group_key ][ $key ]['value'] = $user->user_email;
							break;
					}
				}
			}
			$this->fields = apply_filters( 'cariera_submit_company_form_fields_get_user_data', $this->fields, get_current_user_id() );
		}

		$submission_limit = get_option( 'cariera_company_submission_limit' );
		$total_companies  = cariera_count_user_companies();

		if ( $total_companies < $submission_limit || ! $submission_limit ) {
			get_job_manager_template(
				'company-submit.php',
				[
					'class'              => $this,
					'form'               => $this->form_name,
					'company_id'         => $this->get_company_id(),
					'job_id'             => $this->get_job_id(),
					'action'             => $this->get_action(),
					'company_fields'     => $this->get_fields( 'company_fields' ),
					'step'               => $this->get_step(),
					'submit_button_text' => apply_filters( 'cariera_submit_company_form_submit_button_text', esc_html__( 'Preview Company', 'cariera' ) ),
				],
				'wp-job-manager-companies'
			);
		} else {
			get_job_manager_template( 'company-submit-denied.php', [], 'wp-job-manager-companies' );
		}
	}

	/**
	 * Submit Step is posted
	 *
	 * @since   1.4.4
	 * @version 1.7.0
	 */
	public function submit_handler() {
		try {

			// Init fields.
			$this->init_fields();

			// Get posted values.
			$values = $this->get_posted_fields();

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Input is used safely. Nonce checked below when possible.
			$input_create_account_username        = isset( $_POST['create_account_username'] ) ? sanitize_text_field( wp_unslash( $_POST['create_account_username'] ) ) : false;
			$input_create_account_password        = isset( $_POST['create_account_password'] ) ? sanitize_text_field( wp_unslash( $_POST['create_account_password'] ) ) : false;
			$input_create_account_password_verify = isset( $_POST['create_account_password_verify'] ) ? sanitize_text_field( wp_unslash( $_POST['create_account_password_verify'] ) ) : false;
			$input_create_account_email           = isset( $_POST['company_email'] ) ? sanitize_text_field( wp_unslash( $_POST['company_email'] ) ) : false;

			if ( empty( $_POST['submit_company'] ) ) {
				return;
			}

			$this->check_submit_form_nonce_field();

			// Validate required.
			$validation_status = $this->validate_fields( $values );
			if ( is_wp_error( $validation_status ) ) {
				throw new \Exception( $validation_status->get_error_message() );
			}

			// Account creation.
			if ( ! is_user_logged_in() ) {
				$create_account = false;

				if ( cariera_enable_registration() ) {
					if ( cariera_company_manager_user_requires_account() ) {
						if ( empty( $input_create_account_username ) ) {
							throw new \Exception( esc_html__( 'Please enter a username.', 'cariera' ) );
						}
						// if ( ! cariera_use_standard_password_setup_email() ) {
						if ( empty( $input_create_account_password ) ) {
							throw new \Exception( esc_html__( 'Please enter a password.', 'cariera' ) );
						}
						// }
						if ( empty( $input_create_account_email ) ) {
							throw new \Exception( esc_html__( 'Please enter your company email address.', 'cariera' ) );
						}
					}

					if ( ! empty( $input_create_account_password ) ) {
						if ( empty( $input_create_account_password_verify ) || $input_create_account_password_verify !== $input_create_account_password ) {
							throw new \Exception( esc_html__( 'Passwords must match.', 'cariera' ) );
						}
						if ( ! wpjm_validate_new_password( $input_create_account_password ) ) {
							$password_hint = wpjm_get_password_rules_hint();
							if ( $password_hint ) {
								// translators: Placeholder %s is the password hint.
								throw new \Exception( sprintf( esc_html__( 'Invalid Password: %s', 'cariera' ), $password_hint ) );
							} else {
								throw new \Exception( esc_html__( 'Password is not valid.', 'cariera' ) );
							}
						}
					}

					if ( ! empty( $input_create_account_email ) ) {
						if ( version_compare( JOB_MANAGER_VERSION, '1.20.0', '<' ) ) {
							$create_account = wp_job_manager_create_account( $input_create_account_email, get_option( 'cariera_company_registration_role', 'employer' ) );
						} else {
							$create_account = wp_job_manager_create_account(
								[
									'username' => ( job_manager_generate_username_from_email() || empty( $input_create_account_username ) ) ? '' : $input_create_account_username,
									'password' => $input_create_account_password,
									'email'    => $input_create_account_email,
									'role'     => get_option( 'cariera_company_registration_role', 'employer' ),
								]
							);
						}
					}
				}

				if ( is_wp_error( $create_account ) ) {
					throw new \Exception( $create_account->get_error_message() );
				}
			}

			if ( cariera_company_manager_user_requires_account() && ! is_user_logged_in() ) {
				throw new \Exception( esc_html__( 'You must be signed in to post your company.', 'cariera' ) );
			}

			// Update the job.
			$this->save_company( $values['company_fields']['company_name'], $values['company_fields']['company_content'], $this->company_id ? '' : 'preview', $values );
			$this->update_company_data( $values );

			// Successful, show next step.
			++$this->step;

		} catch ( \Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Creates a file attachment.
	 *
	 * @since 1.4.4
	 */
	protected function create_attachment( $attachment_url ) {

		include_once ABSPATH . 'wp-admin/includes/image.php';
		include_once ABSPATH . 'wp-admin/includes/media.php';

		$upload_dir     = wp_upload_dir();
		$attachment_url = esc_url( $attachment_url, [ 'http', 'https' ] );
		if ( empty( $attachment_url ) ) {
			return 0;
		}

		$attachment_url_parts = wp_parse_url( $attachment_url );

		// Relative paths aren't allowed.
		if ( false !== strpos( $attachment_url_parts['path'], '../' ) ) {
			return 0;
		}

		$attachment_url = sprintf( '%s://%s%s', $attachment_url_parts['scheme'], $attachment_url_parts['host'], $attachment_url_parts['path'] );

		$attachment_url = str_replace( [ $upload_dir['baseurl'], WP_CONTENT_URL, site_url( '/' ) ], [ $upload_dir['basedir'], WP_CONTENT_DIR, ABSPATH ], $attachment_url );
		if ( empty( $attachment_url ) || ! is_string( $attachment_url ) ) {
			return 0;
		}

		$attachment = [
			'post_title'   => cariera_company_name( $this->company_id ),
			'post_content' => '',
			'post_status'  => 'inherit',
			'post_parent'  => $this->company_id,
			'guid'         => $attachment_url,
		];

		$info = wp_check_filetype( $attachment_url );
		if ( $info ) {
			$attachment['post_mime_type'] = $info['type'];
		}

		$attachment_id = wp_insert_attachment( $attachment, $attachment_url, $this->job_id );

		if ( ! is_wp_error( $attachment_id ) ) {
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $attachment_url ) );

			return $attachment_id;
		}

		return 0;
	}

	/**
	 * Gets job posted data for fields.
	 *
	 * @since 1.4.4
	 * @return array of data.
	 */
	public function job_submit_get_posted_fields() {
		return $this->get_posted_fields();
	}

	/**
	 * Update or create a job listing from posted data
	 *
	 * @since 1.4.4
	 */
	public function save_company( $post_title, $post_content, $status = 'preview', $values = [], $id = '' ) {

		$this->company_id = empty( $id ) ? $this->company_id : $id;
		$company_slug     = [];
		$company_slug[]   = current( explode( ' ', $post_title ) );

		if ( ! empty( $values['company_fields']['company_name'] ) ) {
			$company_slug[] = $values['company_fields']['company_name'];
		}

		if ( ! empty( $values['company_fields']['company_location'] ) ) {
			$company_slug[] = $values['company_fields']['company_location'];
		}

		$data = [
			'post_title'     => $post_title,
			'post_content'   => $post_content,
			'post_type'      => 'company',
			'comment_status' => 'closed',
			'post_password'  => '',
			'post_name'      => sanitize_title( implode( '-', $company_slug ) ),
		];

		if ( $status ) {
			$data['post_status'] = $status;
		}

		$data = apply_filters( 'cariera_submit_company_form_save_company_data', $data, $post_title, $post_content, $status, $values, $this );

		if ( $this->company_id ) {
			$data['ID'] = $this->company_id;
			wp_update_post( $data );
		} else {
			$this->company_id = wp_insert_post( $data );
			update_post_meta( $this->company_id, '_public_submission', true );

			// If and only if we're dealing with a logged out user and that is allowed, allow the user to continue a submission after it was started.
			if ( ! is_user_logged_in() && ! cariera_company_manager_user_requires_account() ) {
				$submitting_key = sha1( uniqid() );
				setcookie( 'wp-job-manager-submitting-company-key-' . $this->company_id, $submitting_key, 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
				update_post_meta( $this->company_id, '_submitting_key', $submitting_key );
			}
		}

		return $this->company_id;
	}

	/**
	 * Set company meta + terms based on posted values
	 *
	 * @since 1.4.4
	 */
	public function update_company_data( $values ) {
		// Set defaults.
		add_post_meta( $this->company_id, '_featured', 0, true );

		$maybe_attach = [];

		// Loop fields and save meta and term data.
		foreach ( $this->fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {
				// Save taxonomies.
				if ( ! empty( $field['taxonomy'] ) ) {
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						wp_set_object_terms( $this->company_id, $values[ $group_key ][ $key ], $field['taxonomy'], false );
					} else {
						wp_set_object_terms( $this->company_id, [ $values[ $group_key ][ $key ] ], $field['taxonomy'], false );
					}

					// Company logo is a featured image.
				} elseif ( 'company_logo' === $key ) {
					$attachment_id = is_numeric( $values[ $group_key ][ $key ] ) ? absint( $values[ $group_key ][ $key ] ) : $this->create_attachment( $values[ $group_key ][ $key ] );
					if ( empty( $attachment_id ) ) {
						delete_post_thumbnail( $this->company_id );
					} else {
						set_post_thumbnail( $this->company_id, $attachment_id );
					}

					update_post_meta( $this->company_id, '_' . $key, $values[ $group_key ][ $key ] );
					update_user_meta( get_current_user_id(), '_company_logo', $attachment_id );

					// Save meta data.
				} else {
					update_post_meta( $this->company_id, '_' . $key, $values[ $group_key ][ $key ] );
				}

				// Handle attachments.
				if ( 'file' === $field['type'] ) {
					// Must be absolute.
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						foreach ( $values[ $group_key ][ $key ] as $file_url ) {
							$maybe_attach[] = str_replace( [ WP_CONTENT_URL, site_url() ], [ WP_CONTENT_DIR, ABSPATH ], $file_url );
						}
					} else {
						$maybe_attach[] = str_replace( [ WP_CONTENT_URL, site_url() ], [ WP_CONTENT_DIR, ABSPATH ], $values[ $group_key ][ $key ] );
					}
				}
			}
		}

		// Handle attachments.
		if ( count( $maybe_attach ) && cariera_company_attach_uploaded_files() ) {
			/** WordPress Administration Image API */
			include_once ABSPATH . 'wp-admin/includes/image.php';

			// Get attachments.
			$attachments     = get_posts( 'post_parent=' . $this->company_id . '&post_type=attachment&fields=ids&post_mime_type=image&numberposts=-1' );
			$attachment_urls = [];

			// Loop attachments already attached to the job.
			foreach ( $attachments as $attachment_key => $attachment ) {
				$attachment_urls[] = str_replace( [ WP_CONTENT_URL, site_url() ], [ WP_CONTENT_DIR, ABSPATH ], wp_get_attachment_url( $attachment ) );
			}

			foreach ( $maybe_attach as $attachment_url ) {
				$attachment_url = esc_url( $attachment_url, [ 'http', 'https' ] );

				if ( empty( $attachment_url ) ) {
					continue;
				}

				if ( ! in_array( $attachment_url, $attachment_urls, true ) ) {
					$attachment = [
						'post_title'   => get_the_title( $this->company_id ),
						'post_content' => '',
						'post_status'  => 'inherit',
						'post_parent'  => $this->company_id,
						'guid'         => $attachment_url,
					];

					$info = wp_check_filetype( $attachment_url );
					if ( $info ) {
						$attachment['post_mime_type'] = $info['type'];
					}

					$attachment_id = wp_insert_attachment( $attachment, $attachment_url, $this->company_id );

					if ( ! is_wp_error( $attachment_id ) ) {
						wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $attachment_url ) );
					}
				}
			}
		}

		do_action( 'cariera_update_company_data', $this->company_id, $values );
	}

	/**
	 * Preview Step
	 *
	 * @since   1.4.4
	 * @version 1.5.2
	 */
	public function preview() {
		global $post, $company_preview;

		if ( $this->company_id ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Company preview depends on temporary override. Reset below.
			$post              = get_post( $this->company_id );
			$company_preview   = true;
			$post->post_status = 'preview';

			setup_postdata( $post );

			get_job_manager_template(
				'company-preview.php',
				[
					'form' => $this,
				],
				'wp-job-manager-companies'
			);

			wp_reset_postdata();
		}
	}

	/**
	 * Preview Step Form handler
	 *
	 * @since   1.4.4
	 * @version 1.7.0
	 */
	public function preview_handler() {
		if ( ! $_POST ) {
			return;
		}

		$this->check_preview_form_nonce_field();

		// Edit = show submit form again.
		if ( ! empty( $_POST['edit_company'] ) ) {
			$this->step --;
		}

		// Continue = change job status then show next screen.
		if ( ! empty( $_POST['continue'] ) ) {
			$company = get_post( $this->company_id );

			if ( in_array( $company->post_status, [ 'preview', 'expired' ], true ) ) {
				// Reset expiry.
				delete_post_meta( $company->ID, '_company_expires' );

				// Update listing.
				$update_company                  = [];
				$update_company['ID']            = $company->ID;
				$update_company['post_date']     = current_time( 'mysql' );
				$update_company['post_date_gmt'] = current_time( 'mysql', 1 );
				$update_company['post_author']   = get_current_user_id();
				$update_company['post_status']   = apply_filters( 'cariera_submit_company_post_status', get_option( 'cariera_company_submission_requires_approval' ) ? 'pending' : 'publish', $company );

				wp_update_post( $update_company );
			}

			$this->step ++;
		}
	}

	/**
	 * Output the nonce field on company submission form.
	 *
	 * @since 1.7.0
	 */
	public function output_submit_form_nonce_field() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		wp_nonce_field( 'submit-company-' . $this->company_id, '_wpjm_nonce' );
	}

	/**
	 * Check the nonce field on the submit form.
	 *
	 * @since 1.7.0
	 */
	public function check_submit_form_nonce_field() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		if (
			empty( $_REQUEST['_wpjm_nonce'] )
			|| ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpjm_nonce'] ), 'submit-company-' . $this->company_id ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce should not be modified.
		) {
			wp_nonce_ays( 'submit-company-' . $this->company_id );
			die();
		}
	}

	/**
	 * Output the nonce field on job preview form.
	 *
	 * @since 1.7.0
	 */
	public function output_preview_form_nonce_field() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		wp_nonce_field( 'preview-company-' . $this->company_id, '_wpjm_nonce' );
	}

	/**
	 * Check the nonce field on the preview form.
	 *
	 * @since 1.7.0
	 */
	public function check_preview_form_nonce_field() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if (
			empty( $_REQUEST['_wpjm_nonce'] )
			|| ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpjm_nonce'] ), 'preview-company-' . $this->company_id ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce should not be modified.
		) {
			wp_nonce_ays( 'preview-company-' . $this->company_id );
			die();
		}
	}

	/**
	 * Done Step
	 *
	 * @since 1.4.4
	 */
	public function done() {
		get_job_manager_template(
			'company-submitted.php',
			[
				'company' => get_post( $this->company_id ),
				'job_id'  => $this->job_id,
			],
			'wp-job-manager-companies'
		);
	}

	/**
	 * Get company fields
	 *
	 * @since 1.4.4
	 */
	public static function get_company_fields() {
		$instance = self::instance();
		$instance->init_fields();
		return $instance->get_fields( 'company_fields' );
	}

	/**
	 * Use reCAPTCHA field on the form?
	 *
	 * @since 1.7.0
	 */
	public function use_recaptcha_field() {
		if ( ! method_exists( $this, 'is_recaptcha_available' ) || ! $this->is_recaptcha_available() ) {
			return false;
		}
		return 1 === absint( get_option( 'cariera_enable_recaptcha_company_submission' ) );
	}

	/**
	 * Use agreement checkbox field on the form?
	 *
	 * @since 1.7.0
	 */
	private function use_agreement_checkbox() {
		if ( ! method_exists( $this, 'display_agreement_checkbox_field' ) || ! method_exists( $this, 'validate_agreement_checkbox' ) ) {
			return false;
		}

		return 1 === absint( get_option( 'cariera_show_agreement_company_submission' ) );
	}
}
