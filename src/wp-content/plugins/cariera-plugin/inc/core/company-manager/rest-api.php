<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_API {

	/**
	 * Sets up initial hooks.
	 *
	 * @static
	 */
	public static function init() {
		add_filter( 'rest_prepare_company', [ __CLASS__, 'prepare_company' ], 10, 2 );
	}

	/**
	 * Filters the company data for a REST API response.
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_Post          $post     Post object.
	 * @return WP_REST_Response
	 */
	public static function prepare_company( $response, $post ) {
		$current_user = wp_get_current_user();
		$fields       = \Cariera_Core\Core\Company_Manager\CPT::get_company_fields();
		$data         = $response->get_data();

		foreach ( $data['meta'] as $meta_key => $meta_value ) {
			if ( isset( $fields[ $meta_key ] ) && is_callable( $fields[ $meta_key ]['auth_view_callback'] ) ) {
				$is_viewable = call_user_func( $fields[ $meta_key ]['auth_view_callback'], false, $meta_key, $post->ID, $current_user->ID );
				if ( ! $is_viewable ) {
					unset( $data['meta'][ $meta_key ] );
				}
			}
		}

		$response->set_data( $data );

		return $response;
	}
}
