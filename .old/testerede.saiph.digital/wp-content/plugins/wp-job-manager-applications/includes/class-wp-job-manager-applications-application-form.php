<?php
/**
 * File containing the class WP_Job_Manager_Applications_Application_Form.
 *
 * @package wp-job-manager-applications
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Job_Manager_Applications_Application_Form class.
 *
 * This class manages all data handling for the post type: `job_application_form`.
 *
 * @since 3.0.0
 */
class WP_Job_Manager_Applications_Application_Form {
	/**
	 * The post ID.
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * The post title.
	 *
	 * @var string
	 */
	private $post_title;

	/**
	 * The form fields.
	 *
	 * @var array
	 */
	private $form_fields;

	/**
	 * The candidate email template.
	 *
	 * @var array {
	 *      @type string $content The content of the email.
	 *      @type string $subject The subject of the email.
	 * }
	 */
	private $candidate_email_template;

	/**
	 * The employer email template.
	 *
	 * @var array {
	 *      @type string $content The content of the email.
	 *      @type string $subject The subject of the email.
	 * }
	 */
	private $employer_email_template;

	/**
	 * The meta keys for 'job_application_form' post type.
	 *
	 * @var string
	 */
	private const FORM_FIELDS             = '_form_fields';
	private const CANDIDATE_EMAIL_CONTENT = '_candidate_email_content';
	private const CANDIDATE_EMAIL_SUBJECT = '_candidate_email_subject';
	private const EMPLOYER_EMAIL_CONTENT  = '_employer_email_content';
	private const EMPLOYER_EMAIL_SUBJECT  = '_employer_email_subject';
	public const FORM_POST_META_KEY       = '_application_form';

	/**
	 * Constructor.
	 * Sets the post ID and post title.
	 *
	 * @param int $post_id The post ID. If null, a new post will be created on save.
	 */
	public function __construct( $post_id = null ) {
		$this->post_id = $post_id ?? null;

		if ( $this->post_id ) {
			$this->load_post_data();
		} else {
			$this->load_default_data();
		}
	}

	/**
	 * Load the form fields. Only called when the post ID is set.
	 *
	 * @return void
	 */
	private function load_post_data() {
		$this->post_title  = get_the_title( $this->post_id );
		$this->form_fields = get_post_meta( $this->post_id, self::FORM_FIELDS, true );

		if ( ! is_array( $this->form_fields ) ) {
			$this->load_default_data();
			return;
		}

		$this->candidate_email_template = [
			'content' => get_post_meta( $this->post_id, self::CANDIDATE_EMAIL_CONTENT, true ),
			'subject' => get_post_meta( $this->post_id, self::CANDIDATE_EMAIL_SUBJECT, true ),
		];
		$this->employer_email_template  = [
			'content' => get_post_meta( $this->post_id, self::EMPLOYER_EMAIL_CONTENT, true ),
			'subject' => get_post_meta( $this->post_id, self::EMPLOYER_EMAIL_SUBJECT, true ),
		];
	}

	/**
	 * Load the default data for the form.
	 *
	 * @return void
	 */
	private function load_default_data() {
		$defaults          = self::get_default_data();
		$this->form_fields = $defaults['form_fields'];

		$this->candidate_email_template = $defaults['candidate_email_template'];
		$this->employer_email_template  = $defaults['employer_email_template'];
	}

	/**
	 * Save/persist the form fields.
	 * For new forms, this assumes fields and post title would have been set using the `set` method.
	 *
	 * @param array $args More arguments to pass to wp_insert_post eg 'post_status' and 'post_title'.
	 * @return void
	 */
	public function save( $args = [] ) {
		$post_data = [
			'ID'         => $this->post_id,
			'post_title' => $this->post_title ?? '',
			'post_type'  => 'job_application_form',
			'meta_input' => [
				self::FORM_FIELDS             => $this->form_fields,
				self::CANDIDATE_EMAIL_CONTENT => $this->candidate_email_template['content'] ?? '',
				self::CANDIDATE_EMAIL_SUBJECT => $this->candidate_email_template['subject'] ?? '',
				self::EMPLOYER_EMAIL_CONTENT  => $this->employer_email_template['content'] ?? '',
				self::EMPLOYER_EMAIL_SUBJECT  => $this->employer_email_template['subject'] ?? '',
			],
		];

		$post_data        = array_merge( $post_data, $args );
		$this->post_id    = $this->post_id ? wp_update_post( $post_data ) : wp_insert_post( $post_data );
		$this->post_title = get_the_title( $this->post_id );
	}

	/**
	 * Set a form field.
	 *
	 * @param array $args {
	 *
	 *      @type array $form_fields The form fields.
	 *      @type array $candidate_email_template {
	 *           @type string $subject The subject of the email.
	 *           @type string $content The content of the email.
	 *      }
	 *      @type array $employer_email_template {
	 *           @type string $subject The subject of the email.
	 *           @type string $content The content of the email.
	 *      }
	 *      @type string $post_title The post title.
	 * }
	 */
	public function set( $args ) {
		if ( isset( $args['form_fields'] ) ) {
			$this->form_fields = $args['form_fields'];
		}
		if ( isset( $args['candidate_email_template'] ) ) {
			$this->candidate_email_template = $args['candidate_email_template'];
		}
		if ( isset( $args['employer_email_template'] ) ) {
			$this->employer_email_template = $args['employer_email_template'];
		}
		if ( isset( $args['post_title'] ) ) {
			$this->post_title = $args['post_title'];
		}
	}

	/**
	 * Get a form field.
	 *
	 * @param string $key The key of the field.
	 * @return mixed
	 */
	public function get( $key = null ) {

		if ( $key === 'form_fields' ) {
			return $this->form_fields;
		} elseif ( $key === 'candidate_email_template' ) {
			return $this->candidate_email_template;
		} elseif ( $key === 'employer_email_template' ) {
			return $this->employer_email_template;
		} elseif ( $key === 'post_id' ) {
			return $this->post_id;
		} elseif ( $key === 'post_title' ) {
			return $this->post_title;
		} else {
			return [
				'ID'                       => $this->post_id,
				'post_title'               => $this->post_title,
				'form_fields'              => $this->form_fields,
				'employer_email_template'  => $this->employer_email_template,
				'candidate_email_template' => $this->candidate_email_template,
			];
		}
	}

	/**
	 * Get all form data for a form.
	 *
	 * @param int|null $post_id
	 *
	 * @return array
	 */
	public static function get_form_data( $post_id = null ) {
		return ( new self( $post_id ) )->get();
	}

	/**
	 * Get the default data for creating a new form.
	 *
	 * @return array
	 */
	public static function get_default_data() {
		return [
			'form_fields'              => get_job_application_default_form_fields(),
			'employer_email_template'  => [
				'content' => get_job_application_default_email_content(),
				'subject' => get_job_application_default_email_subject(),
			],

			'candidate_email_template' => [
				'content' => '',
				'subject' => get_job_application_default_candidate_email_subject(),
			],
		];
	}
}
