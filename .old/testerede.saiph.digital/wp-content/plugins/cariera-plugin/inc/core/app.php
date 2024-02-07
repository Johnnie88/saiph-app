<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class App {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 *
	 * @since   1.6.2
	 * @version 1.6.2
	 */
	public function __construct() {

		// Check if Cariera mobile integration is enabled.
		if ( 0 === absint( get_option( 'cariera_mobile_app' ) ) ) {
			return;
		}

		add_action( 'rest_api_init', [ $this, 'wp_rest_endpoints' ] );
		add_filter( 'jwt_auth_valid_credential_response', [ $this, 'jwt_user_response' ], 10, 2 );

		// Adds additional data to user REST.
		add_filter( 'rest_prepare_user', [ $this, 'user_rest_data' ], 10, 3 );

		// Adds additional data to Listing REST.
		add_filter( 'rest_prepare_job_listing', [ $this, 'job_listing_rest_data' ], 10, 3 );
		add_filter( 'rest_prepare_company', [ $this, 'company_rest_data' ], 10, 3 );
		add_filter( 'rest_prepare_resume', [ $this, 'resume_rest_data' ], 10, 3 );

		// Listing REST search queries.
		add_filter( 'rest_job_listing_query', [ $this, 'job_listing_meta_request_params' ], 99, 2 );
		add_filter( 'rest_resume_query', [ $this, 'resume_meta_request_params' ], 99, 2 );
		add_filter( 'rest_company_query', [ $this, 'company_meta_request_params' ], 99, 2 );

		// Author support for resumes.
		add_action( 'init', [ $this, 'resume_author_support' ] );
		add_filter( 'manage_edit-resume_columns', [ $this, 'resume_columns' ] );
	}

	/**
	 * Register a new user
	 *
	 * @since 1.6.2
	 **/
	public function wp_rest_endpoints( $request ) {
		// Handle Register User request.
		register_rest_route(
			'wp/v2',
			'users/register',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_registration_endpoint_handler' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Rest user registration endpoint handler
	 *
	 * @since 1.6.2
	 **/
	public function rest_registration_endpoint_handler( $request = null ) {
		$response   = [];
		$parameters = $request->get_json_params();
		$username   = isset( $parameters['username'] ) ? sanitize_text_field( $parameters['username'] ) : '';
		$email      = isset( $parameters['email'] ) ? sanitize_text_field( $parameters['email'] ) : '';
		$password   = isset( $parameters['password'] ) ? sanitize_text_field( $parameters['password'] ) : '';
		$role       = isset( $parameters['role'] ) ? sanitize_text_field( $parameters['role'] ) : '';

		$error = new \WP_Error();

		// Error with the username field.
		if ( empty( $username ) ) {
			$error->add( 'username_empty', esc_html__( 'Username field is required.', 'cariera' ), [ 'status' => 400 ] );
			return $error;
		}

		if ( username_exists( $username ) ) {
			$error->add( 'username_exists', esc_html__( 'This Username already exists', 'cariera' ), [ 'status' => 400 ] );
			return $error;
		}

		if ( ! validate_username( $username ) ) {
			$error->add( 'username_invalid', esc_html__( 'The Username you entered is not valid', 'cariera' ), [ 'status' => 400 ] );
			return $error;
		}

		// Error with the email field.
		if ( empty( $email ) ) {
			$error->add( 'email_empty', esc_html__( 'Email field is required.', 'cariera' ), [ 'status' => 400 ] );
			return $error;
		}

		if ( ! is_email( $email ) ) {
			$error->add( 'email_invalid', esc_html__( 'Email is not valid, please provide a correct email address.', 'cariera' ), [ 'status' => 400 ] );
			return $error;
		}

		if ( email_exists( $email ) ) {
			$error->add( 'email_exists', esc_html__( 'This Email already exists.', 'cariera' ), [ 'status' => 400 ] );
			return $error;
		}

		// Error when password is empty.
		if ( empty( $password ) ) {
			$error->add( 404, esc_html__( 'Password field is required.', 'cariera' ), [ 'status' => 400 ] );
			return $error;
		}

		// Handling the user role.
		if ( empty( $role ) ) {
			$role = 'subscriber';
		} else {
			if ( $GLOBALS['wp_roles']->is_role( $role ) ) {
				// Silence is gold.
			} else {
				$error->add( 405, esc_html__( 'Role field is not valid. Check your User Roles from WordPress Dashboard.', 'cariera' ), [ 'status' => 400 ] );
				return $error;
			}
		}

		// Register user.
		$user_id = wp_create_user( $username, $password, $email );
		if ( ! is_wp_error( $user_id ) ) {
			// Ger User Meta Data.
			$user = get_user_by( 'id', $user_id );
			$user->set_role( $role );

			$response['code']    = 200;
			$response['message'] = esc_html__( 'User has been successful registered!', 'cariera' );

			// Email notification when user has been successfully registered.
			$mail_args = [
				'email'        => $user->user_email,
				'display_name' => $user->user_login,
				'password'     => $password,
			];
			do_action( 'cariera_new_user_notification', $mail_args );
		} else {
			return $user_id;
		}

		return new WP_REST_Response( $response, 123 );
	}

	/**
	 * Add user role to the valid JWT API response
	 *
	 * @since 1.6.2
	 **/
	public function jwt_user_response( $response, $user ) {
		$response['data']['role'] = $user->roles[0];

		$response['data']['user_avatar'] = get_avatar_url( $user );

		return $response;
	}

	/**
	 * Add additional data to User REST API
	 *
	 * @since 1.6.2
	 **/
	public function user_rest_data( $response, $user, $request ) {
		$response->data['cariera']['jobs_published']      = cariera_count_user_posts_by_status( $user->ID, 'job_listing', 'publish' );
		$response->data['cariera']['jobs_pending']        = cariera_count_user_posts_by_status( $user->ID, 'job_listing', 'pending' );
		$response->data['cariera']['jobs_expired']        = cariera_count_user_posts_by_status( $user->ID, 'job_listing', 'expired' );
		$response->data['cariera']['companies_published'] = cariera_count_user_posts_by_status( $user->ID, 'company', 'publish' );
		$response->data['cariera']['companies_pending']   = cariera_count_user_posts_by_status( $user->ID, 'company', 'pending' );
		$response->data['cariera']['resumes_published']   = cariera_count_user_posts_by_status( $user->ID, 'resume', 'publish' );
		$response->data['cariera']['resumes_pending']     = cariera_count_user_posts_by_status( $user->ID, 'resume', 'pending' );
		$response->data['cariera']['resumes_expired']     = cariera_count_user_posts_by_status( $user->ID, 'resume', 'expired' );
		$response->data['cariera']['monthly_views']       = cariera_user_monthly_views( $user->ID );

		return $response;
	}

	/**
	 * Add additional data to Job Listing REST API
	 *
	 * @since 1.6.2
	 **/
	public function job_listing_rest_data( $data, $post, $context ) {
		$company_manager_id = $data->data['meta']['_company_manager_id'];

		// Adding empty rest data.
		$data->data['company_manager_name']                  = '';
		$data->data['company_manager_logo']                  = '';
		$data->data['meta']['geolocation_lat']               = '';
		$data->data['meta']['geolocation_long']              = '';
		$data->data['meta']['geolocation_city']              = '';
		$data->data['meta']['geolocation_country_long']      = '';
		$data->data['meta']['geolocation_country_short']     = '';
		$data->data['meta']['geolocation_formatted_address'] = '';
		$data->data['meta']['geolocation_state_long']        = '';
		$data->data['meta']['geolocation_state_short']       = '';

		// Company Name.
		if ( $company_manager_id && 'publish' === get_post_status( $company_manager_id ) ) {
			$data->data['company_manager_name'] = get_the_title( $company_manager_id );
			$data->data['company_manager_logo'] = get_the_company_logo( $company_manager_id, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
		}

		// Listing Geo Latitude.
		if ( ! empty( $post->geolocation_lat ) ) {
			$data->data['meta']['geolocation_lat'] = $post->geolocation_lat;
		}

		// Listing Geo Longitude.
		if ( ! empty( $post->geolocation_long ) ) {
			$data->data['meta']['geolocation_long'] = $post->geolocation_long;
		}

		// Listing Geo City.
		if ( ! empty( $post->geolocation_city ) ) {
			$data->data['meta']['geolocation_city'] = $post->geolocation_city;
		}

		// Listing Geo Country Long.
		if ( ! empty( $post->geolocation_country_long ) ) {
			$data->data['meta']['geolocation_country_long'] = $post->geolocation_country_long;
		}

		// Listing Geo Country Short.
		if ( ! empty( $post->geolocation_country_short ) ) {
			$data->data['meta']['geolocation_country_short'] = $post->geolocation_country_short;
		}

		// Listing Geo Formatted Address.
		if ( ! empty( $post->geolocation_formatted_address ) ) {
			$data->data['meta']['geolocation_formatted_address'] = $post->geolocation_formatted_address;
		}

		// Listing Geo State Long.
		if ( ! empty( $post->geolocation_state_long ) ) {
			$data->data['meta']['geolocation_state_long'] = $post->geolocation_state_long;
		}

		// Listing Geo State Short.
		if ( ! empty( $post->geolocation_state_short ) ) {
			$data->data['meta']['geolocation_state_short'] = $post->geolocation_state_short;
		}

		return $data;
	}

	/**
	 * Add additional data to Company REST API
	 *
	 * @since 1.6.2
	 **/
	public function company_rest_data( $data, $post, $context ) {
		$active_job_listings = cariera_get_the_company_job_listing_active_count( $data->data['id'] );

		// Adding empty rest data.
		$data->data['posted_jobs']                           = '';
		$data->data['meta']['geolocation_lat']               = '';
		$data->data['meta']['geolocation_long']              = '';
		$data->data['meta']['geolocation_city']              = '';
		$data->data['meta']['geolocation_country_long']      = '';
		$data->data['meta']['geolocation_country_short']     = '';
		$data->data['meta']['geolocation_formatted_address'] = '';
		$data->data['meta']['geolocation_state_long']        = '';
		$data->data['meta']['geolocation_state_short']       = '';

		// Posted jobs by the company.
		if ( ! empty( $active_job_listings ) ) {
			$data->data['posted_jobs'] = $active_job_listings;
		}

		// Listing Geo Latitude.
		if ( ! empty( $post->geolocation_lat ) ) {
			$data->data['meta']['geolocation_lat'] = $post->geolocation_lat;
		}

		// Listing Geo Longitude.
		if ( ! empty( $post->geolocation_long ) ) {
			$data->data['meta']['geolocation_long'] = $post->geolocation_long;
		}

		// Listing Geo City.
		if ( ! empty( $post->geolocation_city ) ) {
			$data->data['meta']['geolocation_city'] = $post->geolocation_city;
		}

		// Listing Geo Country Long.
		if ( ! empty( $post->geolocation_country_long ) ) {
			$data->data['meta']['geolocation_country_long'] = $post->geolocation_country_long;
		}

		// Listing Geo Country Short.
		if ( ! empty( $post->geolocation_country_short ) ) {
			$data->data['meta']['geolocation_country_short'] = $post->geolocation_country_short;
		}

		// Listing Geo Formatted Address.
		if ( ! empty( $post->geolocation_formatted_address ) ) {
			$data->data['meta']['geolocation_formatted_address'] = $post->geolocation_formatted_address;
		}

		// Listing Geo State Long.
		if ( ! empty( $post->geolocation_state_long ) ) {
			$data->data['meta']['geolocation_state_long'] = $post->geolocation_state_long;
		}

		// Listing Geo State Short.
		if ( ! empty( $post->geolocation_state_short ) ) {
			$data->data['meta']['geolocation_state_short'] = $post->geolocation_state_short;
		}

		return $data;
	}

	/**
	 * Add additional data to Resume REST API
	 *
	 * @since 1.6.2
	 **/
	public function resume_rest_data( $data, $post, $context ) {

		// Adding empty rest data.
		$data->data['meta']['geolocation_lat']               = '';
		$data->data['meta']['geolocation_long']              = '';
		$data->data['meta']['geolocation_city']              = '';
		$data->data['meta']['geolocation_country_long']      = '';
		$data->data['meta']['geolocation_country_short']     = '';
		$data->data['meta']['geolocation_formatted_address'] = '';
		$data->data['meta']['geolocation_state_long']        = '';
		$data->data['meta']['geolocation_state_short']       = '';

		// Listing Geo Latitude.
		if ( ! empty( $post->geolocation_lat ) ) {
			$data->data['meta']['geolocation_lat'] = $post->geolocation_lat;
		}

		// Listing Geo Longitude.
		if ( ! empty( $post->geolocation_long ) ) {
			$data->data['meta']['geolocation_long'] = $post->geolocation_long;
		}

		// Listing Geo City.
		if ( ! empty( $post->geolocation_city ) ) {
			$data->data['meta']['geolocation_city'] = $post->geolocation_city;
		}

		// Listing Geo Country Long.
		if ( ! empty( $post->geolocation_country_long ) ) {
			$data->data['meta']['geolocation_country_long'] = $post->geolocation_country_long;
		}

		// Listing Geo Country Short.
		if ( ! empty( $post->geolocation_country_short ) ) {
			$data->data['meta']['geolocation_country_short'] = $post->geolocation_country_short;
		}

		// Listing Geo Formatted Address.
		if ( ! empty( $post->geolocation_formatted_address ) ) {
			$data->data['meta']['geolocation_formatted_address'] = $post->geolocation_formatted_address;
		}

		// Listing Geo State Long.
		if ( ! empty( $post->geolocation_state_long ) ) {
			$data->data['meta']['geolocation_state_long'] = $post->geolocation_state_long;
		}

		// Listing Geo State Short.
		if ( ! empty( $post->geolocation_state_short ) ) {
			$data->data['meta']['geolocation_state_short'] = $post->geolocation_state_short;
		}

		return $data;
	}

	/**
	 * Additional search query to Job Listing REST
	 *
	 * @since 1.6.2
	 **/
	public function job_listing_meta_request_params( $args, $request ) {
		$location = $request->get_param( 'location' );
		$featured = $request->get_param( 'featured' );

		if ( $location ) {
			$args['meta_key']   = [ '_job_location', 'geolocation_city', 'geolocation_country_long', 'geolocation_country_short', 'geolocation_formatted_address', 'geolocation_state_long', 'geolocation_state_short' ];
			$args['meta_value'] = $location;
		}

		// Featured Param.
		if ( $featured ) {
			$args['meta_key']   = '_featured';
			$args['meta_value'] = $featured;
		}

		return $args;
	}

	/**
	 * Additional search query to Resume REST
	 *
	 * @since 1.6.2
	 **/
	public function resume_meta_request_params( $args, $request ) {
		$location = $request->get_param( 'location' );
		$featured = $request->get_param( 'featured' );

		if ( $location ) {
			$args['meta_key']   = [ '_candidate_location', 'geolocation_city', 'geolocation_country_long', 'geolocation_country_short', 'geolocation_formatted_address', 'geolocation_state_long', 'geolocation_state_short' ];
			$args['meta_value'] = $location;
		}

		// Featured Param.
		if ( $featured ) {
			$args['meta_key']   = '_featured';
			$args['meta_value'] = $featured;
		}

		return $args;
	}

	/**
	 * Additional search query to Company REST
	 *
	 * @since 1.6.2
	 **/
	public function company_meta_request_params( $args, $request ) {
		$location = $request->get_param( 'location' );
		$featured = $request->get_param( 'featured' );

		if ( $location ) {
			$args['meta_key']   = [ '_company_location', 'geolocation_city', 'geolocation_country_long', 'geolocation_country_short', 'geolocation_formatted_address', 'geolocation_state_long', 'geolocation_state_short' ];
			$args['meta_value'] = $location;
		}

		// Featured Param.
		if ( $featured ) {
			$args['meta_key']   = '_featured';
			$args['meta_value'] = $featured;
		}

		return $args;
	}

	/**
	 * Author support for Resumes
	 *
	 * @since 1.6.2
	 **/
	public function resume_author_support() {
		if ( defined( 'RESUME_MANAGER_VERSION' ) && version_compare( get_option( 'wp_resume_manager_version', RESUME_MANAGER_VERSION ), '1.18.6', '>' ) ) {
			return;
		}

		add_post_type_support( 'resume', 'author' );
	}

	/**
	 * Unset author column from resume cpt
	 *
	 * @since 1.6.2
	 **/
	public function resume_columns( $columns ) {
		unset( $columns['author'] );

		return $columns;
	}
}
