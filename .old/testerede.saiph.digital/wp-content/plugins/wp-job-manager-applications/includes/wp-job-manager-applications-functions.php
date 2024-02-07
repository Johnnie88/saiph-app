<?php
/**
 * File containing the global functions for the plugin.
 *
 * @package wp-job-manager-applications
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'create_job_application' ) ) {
	/**
	 * Create a new job application
	 *
	 * @param  int    $job_id
	 * @param  string $candidate_name
	 * @param  string $application_message
	 * @param  string $candidate_email
	 * @param  array  $meta
	 * @param  bool   $notification
	 * @return int|bool success
	 */
	function create_job_application( $job_id, $candidate_name, $candidate_email, $application_message, $meta = [], $notification = true, $source = '' ) {
		$job = get_post( $job_id );

		if ( ! $job || $job->post_type !== 'job_listing' ) {
			return false;
		}

		$application_data = [
			'post_title'     => wp_kses_post( $candidate_name ),
			'post_content'   => wp_kses_post( $application_message ),
			'post_status'    => current( array_keys( get_job_application_statuses() ) ),
			'post_type'      => 'job_application',
			'comment_status' => 'closed',
			'post_author'    => $job->post_author,
			'post_parent'    => $job_id,
		];
		$application_id   = wp_insert_post( $application_data );

		if ( $application_id ) {
			update_post_meta( $application_id, '_job_applied_for', $job->post_title );
			update_post_meta( $application_id, '_candidate_email', $candidate_email );
			update_post_meta( $application_id, '_candidate_user_id', get_current_user_id() );
			update_post_meta( $application_id, '_rating', 0 );
			update_post_meta( $application_id, '_application_source', $source );

			if ( $meta ) {
				foreach ( $meta as $key => $value ) {
					update_post_meta( $application_id, $key, $value );
				}
			}

			if ( $notification ) {
				$method = get_the_job_application_method( $job_id );

				if ( 'email' === $method->type ) {
					$send_to = $method->raw_email;
				} elseif ( $job->post_author ) {
					$user    = get_user_by( 'id', $job->post_author );
					$send_to = $user->user_email;
				} else {
					$send_to = '';
				}

				if ( $send_to ) {
					$attachments = [];

					if ( function_exists( 'get_resume_attachments' ) ) {
						$resume_id = get_job_application_resume_id( $application_id );
						if ( $resume_id && 'publish' === get_post_status( $resume_id ) ) {
							$resume_files = get_resume_attachments( $resume_id );
							$attachments  = $resume_files['attachments'];
						}
					}

					if ( ! empty( $meta['_attachment_file'] ) ) {
						if ( is_array( $meta['_attachment_file'] ) ) {
							foreach ( $meta['_attachment_file'] as $file ) {
								$attachments[] = $file;
							}
						} else {
							$attachments[] = $meta['_attachment_file'];
						}
					}

					$existing_shortcode_tags = $GLOBALS['shortcode_tags'];
					remove_all_shortcodes();
					job_application_email_add_shortcodes(
						[
							'application_id'      => $application_id,
							'job_id'              => $job_id,
							'user_id'             => get_current_user_id(),
							'candidate_name'      => $candidate_name,
							'candidate_email'     => $candidate_email,
							'application_message' => $application_message,
							'meta'                => $meta,
						]
					);
					$subject = do_shortcode( get_job_application_email_subject() );
					$message = do_shortcode( get_job_application_email_content( $meta['_form_id'] ) );
					$message = str_replace( "\n\n\n\n", "\n\n", implode( "\n", array_map( 'trim', explode( "\n", $message ) ) ) );
					$is_html = ( $message != strip_tags( $message ) );

					// Does this message contain formatting already?
					if ( $is_html && ! strstr( $message, '<p' ) && ! strstr( $message, '<br' ) ) {
						$message = nl2br( $message );
					}

					$GLOBALS['shortcode_tags'] = $existing_shortcode_tags;

					$headers   = [];
					$headers[] = 'Reply-To: ' . $candidate_email;
					$headers[] = $is_html ? 'Content-Type: text/html' : 'Content-Type: text/plain';
					$headers[] = 'charset=utf-8';

					wp_mail(
						apply_filters( 'create_job_application_notification_recipient', $send_to, $job_id, $application_id ),
						apply_filters( 'create_job_application_notification_subject', $subject, $job_id, $application_id ),
						apply_filters( 'create_job_application_notification_message', $message ),
						apply_filters( 'create_job_application_notification_headers', $headers, $job_id, $application_id ),
						apply_filters( 'create_job_application_notification_attachments', $attachments, $job_id, $application_id )
					);
				}
			}

			return $application_id;
		}

		return false;
	}
}

if ( ! function_exists( 'get_job_application_count' ) ) {
	/**
	 * Get number of applications for a job
	 *
	 * @param  int $job_id
	 * @return int
	 */
	function get_job_application_count( $job_id ) {
		return count(
			get_posts(
				[
					'post_type'      => 'job_application',
					'post_status'    => array_merge( array_keys( get_job_application_statuses() ), [ 'publish' ] ),
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'post_parent'    => $job_id,
				]
			)
		);
	}
}

if ( ! function_exists( 'user_has_applied_for_job' ) ) {
	/**
	 * See if a user has already appled for a job
	 *
	 * @param  int         $user_id
	 * @param  int|WP_Post $job_id
	 * @return bool
	 */
	function user_has_applied_for_job( $user_id, $job_id ) {
		if ( ! $user_id ) {
			return false;
		}
		if ( is_object( $job_id ) && $job_id instanceof WP_Post ) {
			$job_id = $job_id->ID;
		}
		return count(
			get_posts(
				[
					'post_type'      => 'job_application',
					'post_status'    => array_merge( array_keys( get_job_application_statuses() ), [ 'publish' ] ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'post_parent'    => $job_id,
					'meta_query'     => [
						[
							'key'   => '_candidate_user_id',
							'value' => absint( $user_id ),
						],
					],
				]
			)
		);
	}
}

/**
 * Job Application Statuses
 *
 * @return array
 */
function get_job_application_statuses() {
	return apply_filters(
		'job_application_statuses',
		[
			'new'         => _x( 'New', 'job_application', 'wp-job-manager-applications' ),
			'interviewed' => _x( 'Interviewed', 'job_application', 'wp-job-manager-applications' ),
			'offer'       => _x( 'Offer extended', 'job_application', 'wp-job-manager-applications' ),
			'hired'       => _x( 'Hired', 'job_application', 'wp-job-manager-applications' ),
			'rejected'    => _x( 'Rejected', 'job_application', 'wp-job-manager-applications' ),
			'archived'    => _x( 'Archived', 'job_application', 'wp-job-manager-applications' ),
		]
	);
}

/**
 * Get default form fields
 *
 * @return array
 */
function get_job_application_default_form_fields() {
	$default_fields = [
		'candidate_name'         => [
			'label'       => __( 'Full name', 'wp-job-manager-applications' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 1,
			'rules'       => [ 'from_name' ],
		],
		'candidate_email'        => [
			'label'       => __( 'Email address', 'wp-job-manager-applications' ),
			'description' => '',
			'type'        => 'text',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 2,
			'rules'       => [ 'from_email' ],
		],
		'application_message'    => [
			'label'       => __( 'Message', 'wp-job-manager-applications' ),
			'type'        => 'textarea',
			'required'    => true,
			'placeholder' => __( 'Your cover letter/message sent to the employer', 'wp-job-manager-applications' ),
			'priority'    => 3,
			'rules'       => [ 'message' ],
		],
		'resume_id'              => [
			'label'       => __( 'Online Resume', 'wp-job-manager-applications' ),
			'description' => '',
			'type'        => 'resumes',
			'required'    => false,
			'priority'    => 4,
			'rules'       => [],
		],
		'application_attachment' => [
			'label'       => __( 'Upload CV', 'wp-job-manager-applications' ),
			'type'        => 'file',
			'required'    => true,
			'priority'    => 5,
			'placeholder' => '',
			'multiple'    => true,
			'rules'       => [ 'attachment' ],
			'description' => sprintf( __( 'Upload your CV/resume or any other relevant file. Max. file size: %s.', 'wp-job-manager-applications' ), size_format( wp_max_upload_size() ) ),
		],
	];

	if ( ! function_exists( 'get_resume_share_link' ) ) {
		unset( $default_fields['resume_id'] );
		$default_fields['application_attachment']['required'] = true;
	} else {
		$default_fields['application_attachment']['required'] = false;
	}

	return $default_fields;
}

/**
 * Get the form fields for the application form
 *
 * @param bool     $suppress_filters Whether to apply filters to the returned result.
 * @param int|null $form_id Optional. The form ID.
 *
 * @return array
 */
function get_job_application_form_fields( $suppress_filters = false, $form_id = null ) {
	if ( $form_id && ! is_null( get_post( $form_id ) ) ) {
		$form   = new WP_Job_Manager_Applications_Application_Form( $form_id );
		$fields = $form->get( 'form_fields' );
	} else {
		$fields = WP_Job_Manager_Applications_Default_Form::get_default_form()['form_fields'];
	}
	return $suppress_filters ? $fields : apply_filters( 'job_application_form_fields', $fields, $form_id );
}

/**
 * Get all Application Forms
 *
 * @return array|null
 */
function get_application_forms() {
	$posts = new WP_Query(
		array(
			'post_type'        => 'job_application_form',
			'posts_per_page'   => - 1,
			'suppress_filters' => true,
			'orderby'          => 'title',
			'order'            => 'ASC',
		)
	);

	$default_form = WP_Job_Manager_Applications_Default_Form::get_default_form();

	if ( $posts->post_count <= 1 ) {
		return null;
	}
	$application_forms = [];

	if ( array_key_exists( 'ID', $default_form ) ) {
		$application_forms[ $default_form['ID'] ] = $default_form['post_title'];
	}

	foreach ( $posts->posts as $post ) {
		$application_forms[ $post->ID ] = $post->post_title;
	}
	return apply_filters( 'job_application_forms', $application_forms );
}

/**
 * Get Application Form Fields
 *
 * @return array|null
 */
function get_submit_job_application_form_field() {
	global $post;

	$application_forms = get_application_forms();

	if ( empty( $application_forms ) ) {
		return null;
	}

	$field_array = array(
		'label'       => __( 'Application Form', 'wp-job-manager-applications' ),
		'placeholder' => '',
		'type'        => 'select',
		'classes'     => array( 'select2' ),
		'options'     => array_map( 'esc_attr', $application_forms ),
	);

	return $field_array;
}

/**
 * Get the default email content
 *
 * @return string
 */
function get_job_application_default_email_content() {
	$message = <<<EOF
Hello

A candidate ([from_name]) has submitted their application for the position "[job_title]".

=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

[message]

=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

[meta_data]

[job_dashboard_url prefix="You can view this and any other applications here: "]

You can contact them directly at: [from_email]
EOF;
	return $message;
}

/**
 * Get employer email content.
 *
 * @param int|null $form_id Optional. The form ID.
 *
 * @return string
 */
function get_job_application_email_content( $form_id = null ) {
	if ( $form_id && ! is_null( get_post( $form_id ) ) ) {
		$form     = new WP_Job_Manager_Applications_Application_Form( $form_id );
		$template = $form->get( 'employer_email_template' );
		$content  = $template['content'];
	} else {
		$content = WP_Job_Manager_Applications_Default_Form::get_default_form()['employer_email_template']['content'];
	}
	return apply_filters( 'job_application_email_content', $content, $form_id );
}

/**
 * Get the default email subject
 *
 * @return string
 */
function get_job_application_default_email_subject() {
	return __( 'New job application for [job_title]', 'wp-job-manager-applications' );
}

/**
 * Get employer email subject
 *
 * @param int|null $form_id Optional. The form ID.
 *
 * @return string
 */
function get_job_application_email_subject( $form_id = null ) {
	if ( $form_id && ! is_null( get_post( $form_id ) ) ) {
		$form     = new WP_Job_Manager_Applications_Application_Form( $form_id );
		$template = $form->get( 'employer_email_template' );
		$subject  = $template['subject'];
	} else {
		$subject = WP_Job_Manager_Applications_Default_Form::get_default_form()['employer_email_template']['subject'];
	}
	return apply_filters( 'job_application_email_subject', $subject, $form_id );
}

/**
 * Get candidate email content.
 *
 * @param int|null $form_id Optional. The form ID.
 *
 * @return string
 */
function get_job_application_candidate_email_content( $form_id = null ) {
	if ( $form_id && ! is_null( get_post( $form_id ) ) ) {
		$form     = new WP_Job_Manager_Applications_Application_Form( $form_id );
		$template = $form->get( 'candidate_email_template' );
		$content  = $template['content'];
	} else {
		$content = WP_Job_Manager_Applications_Default_Form::get_default_form()['candidate_email_template']['content'];
	}
	return apply_filters( 'job_application_candidate_email_content', $content, $form_id );
}

/**
 * Get the default email subject.
 *
 * @return string
 */
function get_job_application_default_candidate_email_subject() {
	return __( 'Your job application for [job_title]', 'wp-job-manager-applications' );
}

/**
 * Get candidate email subject
 *
 * @param int|null $form_id Optional. The form ID.
 *
 * @return string
 */
function get_job_application_candidate_email_subject( $form_id = null ) {
	if ( $form_id && ! is_null( get_post( $form_id ) ) ) {
		$form     = new WP_Job_Manager_Applications_Application_Form( $form_id );
		$template = $form->get( 'candidate_email_template' );
		$subject  = $template['subject'];
	} else {
		$subject = WP_Job_Manager_Applications_Default_Form::get_default_form()['candidate_email_template']['subject'];
	}
	return apply_filters( 'job_application_candidate_email_subject', $subject, $form_id );
}

/**
 * Get tags to dynamically replace in the notification email
 *
 * @param int|null $form_id Optional. The form ID.
 *
 * @return array
 */
function get_job_application_email_tags( $form_id = null ) {
	$tags = [
		'from_name'         => __( 'Candidate Name', 'wp-job-manager-applications' ),
		'from_email'        => __( 'Candidate Email', 'wp-job-manager-applications' ),
		'message'           => __( 'Message from candidate', 'wp-job-manager-applications' ),
		'meta_data'         => __( 'All custom form fields in list format', 'wp-job-manager-applications' ),
		'application_id'    => __( 'Application ID', 'wp-job-manager-applications' ),
		'user_id'           => __( 'User ID of applicant', 'wp-job-manager-applications' ),
		'job_id'            => __( 'Job ID', 'wp-job-manager-applications' ),
		'job_title'         => __( 'Job Title', 'wp-job-manager-applications' ),
		'job_url'           => __( 'URL of the job listing', 'wp-job-manager-applications' ),
		'job_dashboard_url' => __( 'URL to the frontend job dashboard page', 'wp-job-manager-applications' ),
		'company_name'      => __( 'Name of the company which submitted the job listing', 'wp-job-manager-applications' ),
		'job_post_meta'     => __( 'Some meta data from the job. e.g. <code>[job_post_meta key="_job_location"]</code>', 'wp-job-manager-applications' ),
	];

	foreach ( get_job_application_form_fields( false, $form_id ) as $key => $field ) {

		if ( isset( $tags[ $key ] ) ) {
			continue;
		}
		if ( in_array( 'message', $field['rules'] ) || in_array( 'from_name', $field['rules'] ) || in_array( 'from_email', $field['rules'] ) || in_array( 'attachment', $field['rules'] ) ) {
			continue;
		}
		$tags[ $key ] = sprintf( __( 'Custom field named "%s"', 'wp-job-manager-applications' ), $field['label'] );
	}

	return $tags;
}

/**
 * Get the default form data for the application form.
 *
 * @return array
 */
function get_default_job_application_form_data() {
	return WP_Job_Manager_Applications_Default_Form::get_default_form();
}

/**
 * Shortcode handler
 *
 * @param  array $atts
 * @return string
 */
function job_application_email_shortcode_handler( $atts, $content, $value ) {
	$atts = shortcode_atts(
		[
			'prefix' => '',
			'suffix' => '',
		],
		$atts
	);

	if ( ! empty( $value ) ) {
		return wp_kses_post( $atts['prefix'] ) . $value . wp_kses_post( $atts['suffix'] );
	}
}

/**
 * Add shortcodes for email content
 *
 * @param  array $data
 */
function job_application_email_add_shortcodes( $data ) {
	extract( $data );

	$job_title         = html_entity_decode( strip_tags( get_the_title( $job_id ) ) );
	$dashboard_id      = get_option( 'job_manager_job_dashboard_page_id' );
	$job_dashboard_url = $dashboard_id ? htmlspecialchars_decode(
		add_query_arg(
			[
				'action' => 'show_applications',
				'job_id' => $job_id,
			],
			get_permalink( $dashboard_id )
		)
	) : '';
	$meta_data         = [];
	$company_name      = get_the_company_name( $job_id );
	$application_id    = $data['application_id'];
	$user_id           = $data['user_id'];

	add_shortcode(
		'from_name',
		function( $atts, $content = '' ) use ( $candidate_name ) {
			return job_application_email_shortcode_handler( $atts, $content, $candidate_name );
		}
	);
	add_shortcode(
		'from_email',
		function( $atts, $content = '' ) use ( $candidate_email ) {
			return job_application_email_shortcode_handler( $atts, $content, $candidate_email );
		}
	);
	add_shortcode(
		'message',
		function( $atts, $content = '' ) use ( $application_message ) {
			return job_application_email_shortcode_handler( $atts, $content, $application_message );
		}
	);
	add_shortcode(
		'job_id',
		function( $atts, $content = '' ) use ( $job_id ) {
			return job_application_email_shortcode_handler( $atts, $content, $job_id );
		}
	);
	add_shortcode(
		'job_title',
		function( $atts, $content = '' ) use ( $job_title ) {
			return job_application_email_shortcode_handler( $atts, $content, $job_title );
		}
	);
	add_shortcode(
		'job_url',
		function( $atts, $content = '' ) use ( $job_id ) {
			return job_application_email_shortcode_handler( $atts, $content, get_permalink( $job_id ) );
		}
	);
	add_shortcode(
		'job_dashboard_url',
		function( $atts, $content = '' ) use ( $job_dashboard_url ) {
			return job_application_email_shortcode_handler( $atts, $content, $job_dashboard_url );
		}
	);
	add_shortcode(
		'company_name',
		function( $atts, $content = '' ) use ( $company_name ) {
			return job_application_email_shortcode_handler( $atts, $content, $company_name );
		}
	);
	add_shortcode(
		'application_id',
		function( $atts, $content = '' ) use ( $application_id ) {
			return job_application_email_shortcode_handler( $atts, $content, $application_id );
		}
	);
	add_shortcode(
		'user_id',
		function( $atts, $content = '' ) use ( $user_id ) {
			return job_application_email_shortcode_handler( $atts, $content, $user_id );
		}
	);
	add_shortcode(
		'job_post_meta',
		function( $atts, $content = '' ) use ( $job_id ) {
			$atts  = shortcode_atts( [ 'key' => '' ], $atts );
			$value = get_post_meta( $job_id, sanitize_text_field( $atts['key'] ), true );
			return job_application_email_shortcode_handler( $atts, $content, $value );
		}
	);
	$form_id = get_post_meta( get_the_ID(), WP_Job_Manager_Applications_Application_Form::FORM_POST_META_KEY, true );

	foreach ( get_job_application_form_fields( false, $form_id ) as $key => $field ) {
		if ( in_array( 'message', $field['rules'] ) || in_array( 'from_name', $field['rules'] ) || in_array( 'from_email', $field['rules'] ) || in_array( 'attachment', $field['rules'] ) ) {
			continue;
		}

		$multiple_files = '';
		if ( 'file' === $field['type'] && true == $field['multiple'] ) {
			foreach ( $meta as $index => $filename ) {
				if ( false !== strpos( $index, $field['label'] ) ) {
					$multiple_files .= $meta[ $index ] . ' ';
				}
			}
			$value = $multiple_files;
		} else {
			$value = isset( $meta[ $field['label'] ] ) ? $meta[ $field['label'] ] : '';
		}

		if ( $field['type'] === 'resumes' && function_exists( 'get_resume_share_link' ) && isset( $meta['_resume_id'] ) ) {
			$value = get_resume_share_link( $meta['_resume_id'] );
		}

		$meta_data[] = [
			'label' => $field['label'],
			'value' => $value,
		];

		add_shortcode(
			$key,
			function( $atts, $content = '' ) use ( $value ) {
				return job_application_email_shortcode_handler( $atts, $content, $value );
			}
		);
	}

	$meta_data         = array_filter( $meta_data );
	$meta_data_strings = [];

	foreach ( $meta_data as $data ) {
		$meta_data_strings[] = $data['label'] . ': ' . $data['value'];
	}
	$meta_data_strings = implode( "\n", $meta_data_strings );

	add_shortcode(
		'meta_data',
		function( $atts, $content = '' ) use ( $meta_data_strings ) {
			return job_application_email_shortcode_handler( $atts, $content, $meta_data_strings );
		}
	);

	do_action( 'job_application_email_add_shortcodes', $data );
}
