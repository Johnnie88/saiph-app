<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * sMyles_Updater_v2_UI
 *
 * @version 2.0
 * @author  Mike Jolley, Myles McNamara
 */
class sMyles_Updater_v2_API {

	private static $api_url = 'http://plugins.smyl.es/?wc-api=upgrade-api';

	/**
	 * Sends and receives data to and from the server API
	 *
	 * @since 2.0
	 *
	 * @return object $response
	 */
	public static function plugin_update_check( $args ) {
		$defaults = array(
			'request'        => 'pluginupdatecheck',
			'plugin_name'    => '',
			'version'        => '',
			'product_id' => '',
			'api_key'    => '',
			'activation_email' => '',
			'instance' => site_url(),
			'domain' => site_url(),
			'software_version' => '',
		);

		$args    = wp_parse_args( $args, $defaults );
		$request = wp_remote_get( self::$api_url . '&' . http_build_query( $args, '', '&' ), array( 'timeout' => 10 ) );

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			return false;
		}

		$response = maybe_unserialize( wp_remote_retrieve_body( $request ) );

		if ( is_object( $response ) ) {
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Sends and receives data to and from the server API
	 *
	 * @since 2.0
	 *
	 * @return object $response
	 */
	public static function plugin_information( $args ) {
		$defaults = array(
			'request'        => 'plugininformation',
			'plugin_name'    => '',
			'version'        => '',
			'product_id' => '',
			'api_key'    => '',
			'activation_email' => '',
			'instance' => site_url(),
			'domain' => site_url(),
			'software_version' => '',
		);

		$args    = wp_parse_args( $args, $defaults );
		$request = wp_remote_get( self::$api_url . '&' . http_build_query( $args, '', '&' ), array( 'timeout' => 10 ) );

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			return false;
		}

		$response = maybe_unserialize( wp_remote_retrieve_body( $request ) );

		if ( is_object( $response ) ) {
			return $response;
		} else {
			return false;
		}
	}
}
