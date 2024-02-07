<?php
/**
 * File containing the class WP_Job_Manager_Applications_Default_Form.
 *
 * @package wp-job-manager-applications
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * Add a default form to the job application form post type.
 */
class WP_Job_Manager_Applications_Default_Form {

	const DEFAULT_FORM_META = '_default_form';

	/**
	 * ID for default form.
	 *
	 * @var string
	 */
	private static $default_form_id;

	/**
	 * Set up hooks.
	 */
	public function __construct() {
		self::$default_form_id = null;
		add_action( 'posts_results', [ $this, 'add_default_form' ], 10, 2 );
		add_action( 'pre_trash_post', [ $this, 'prevent_trash' ], 10, 2 );
		add_action( 'pre_delete_post', [ $this, 'prevent_trash' ], 10, 2 );
		add_filter( 'post_row_actions', [ $this, 'hide_trash_button_for_post' ], 10, 2 );
		add_filter( 'display_post_states', [ $this, 'add_default_label_as_state' ], 10, 2 );
	}

	/**
	 * Add a "Default" label as a post state for the default form.
	 *
	 * @access private
	 *
	 * @param  array  $post_states  Post States for the provided Post.
	 * @param  object $post         Post object.
	 * @return array               Updated Post States.
	 */
	public function add_default_label_as_state( $post_states, $post ) {
		if ( self::get_default_form_id() === $post->ID ) {
			$post_states[] = __( 'Default', 'wp-job-manager-applications' );
		}

		return $post_states;
	}

	/**
	 * Prevent the default form from being trashed.
	 *
	 * @access private
	 *
	 * @param boolean     $delete    Whether to delete or not.
	 * @param int|WP_Post $post_id   Post ID or post object.
	 *
	 * @return boolean Whether to delete or not.
	 */
	public function prevent_trash( $delete, $post_id ) {

		if ( is_a( $post_id, 'WP_Post' ) ) {
			$post_id = $post_id->ID;
		}

		$default_form_id = self::get_default_form_id();
		if ( $default_form_id === $post_id ) {
			wp_die( wp_kses_post( '<h1>' . __( 'The default application form cannot be deleted.', 'wp-job-manager-applications' ) . '</h1><p>' . __( 'To disable it for new job listings, unpublish the form.', 'wp-job-manager-applications' ) ) . '</p>', '', [ 'back_link' => true ] );
		}

		return $delete;
	}

	/**
	 * Get the ID of the default form.
	 *
	 * @return int|null
	 */
	public static function get_default_form_id() {
		if ( self::$default_form_id ) {
			return self::$default_form_id;
		}

		$posts = get_posts(
			[
				'post_type'      => 'job_application_form',
				'posts_per_page' => 1,
				//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_key'       => self::DEFAULT_FORM_META,
				//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'meta_value'     => '1',
				'numberposts'    => 1,
			]
		);

		self::$default_form_id = ! empty( $posts ) ? $posts[0]->ID : null;

		return self::$default_form_id;
	}

	/**
	 * Get the data for the default form.
	 *
	 * @return array|null
	 */
	public static function get_default_form() {
		/**
		 * Filters the default job application form.
		 *
		 * @since 3.0.0
		 *
		 * @param array $form The default application form.
		 */
		return apply_filters( 'job_application_default_form', self::get_form_data_from_post_type() ?? self::get_form_data_from_options() );
	}

	/**
	 * Hide the trash button for the default form.
	 *
	 * @param array   $actions Post actions.
	 * @param WP_Post $post    Post object.
	 *
	 * @return array
	 */
	public function hide_trash_button_for_post( $actions, $post ) {
		if ( self::get_default_form_id() === $post->ID ) {
			unset( $actions['trash'] );
		}
		return $actions;
	}

	/**
	 * Add the default form if no other forms are listed.
	 *
	 * @param array    $posts   Array of posts.
	 * @param WP_Query $query   WP_Query object.
	 *
	 * @return array
	 */
	public function add_default_form( $posts, WP_Query $query ) {
		if ( empty( $posts ) && 'job_application_form' === $query->query_vars['post_type'] ) {

			remove_filter( 'posts_results', [ $this, 'add_default_form' ], 10, 2 );

			$post_id = $this->migrate_from_options_to_post_type();
			if ( ! empty( $post_id ) ) {
				$posts[] = get_post( $post_id );
			}
		}
		return $posts;
	}

	/**
	 * Get the form data from the options.
	 *
	 * @return array
	 */
	private static function get_form_data_from_options() {

		return [
			'ID'                       => null,
			'post_title'               => __( 'Default Form', 'wp-job-manager-applications' ),
			'form_fields'              => get_option( 'job_application_form_fields', get_job_application_default_form_fields() ),
			'employer_email_template'  => [
				'content' => get_option( 'job_application_email_content', get_job_application_default_email_content() ),
				'subject' => get_option( 'job_application_email_subject', get_job_application_default_email_subject() ),
			],
			'candidate_email_template' => [
				'content' => get_option( 'job_application_candidate_email_content' ),
				'subject' => get_option( 'job_application_candidate_email_subject', get_job_application_default_candidate_email_subject() ),
			],
		];
	}

	/**
	 * Get the form data from the post type.
	 *
	 * @return array|null
	 */
	private static function get_form_data_from_post_type() {

		$default_form_id = self::get_default_form_id();

		if ( ! $default_form_id ) {
			return null;
		}

		$form = new WP_Job_Manager_Applications_Application_Form( $default_form_id );
		return $form->get();

	}

	/**
	 * Migrate the form data from the options to the post type.
	 *
	 * @access private
	 *
	 * @return int Post ID for the default form.
	 */
	public function migrate_from_options_to_post_type() {
		$default_form = self::get_default_form_id();

		if ( ! empty( $default_form ) ) {
			return self::get_default_form_id();
		}

		$form_data = self::get_form_data_from_options();

		$form = new WP_Job_Manager_Applications_Application_Form();
		$form->set( $form_data );
		$form->save();

		$id = $form->get( 'post_id' );

		self::$default_form_id = $id;

		return wp_update_post(
			[
				'ID'          => $id,
				'post_status' => 'publish',
				'meta_input'  => [
					self::DEFAULT_FORM_META => '1',
				],
			]
		);

	}
}

new WP_Job_Manager_Applications_Default_Form();
