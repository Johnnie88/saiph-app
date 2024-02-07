<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * sMyles_Updater_v2_Key_API
 */
class sMyles_Updater_v2_Key_API {
	private static $endpoint = 'http://plugins.smyl.es/?wc-api=am-software-api';

	/**
	 * Attempt to activate a plugin licence
	 *
	 * @since 2.0
	 *
	 * @return string JSON response
	 */
	public static function activate( $args ) {
		$defaults = array(
			'request'  => 'activation',
			'platform' => site_url(),
		);

		$args    = wp_parse_args( $defaults, $args );
		$request = wp_remote_get( self::$endpoint . '&' . http_build_query( $args, '', '&' ), array( 'timeout' => 10 ) );

		if ( is_wp_error( $request ) ) {
			return json_encode( array( 'error_code' => $request->get_error_code(), 'error' => $request->get_error_message() ) );
		}

		if ( wp_remote_retrieve_response_code( $request ) != 200 ) {
			return json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $request ), 'error' => 'Error code: ' . wp_remote_retrieve_response_code( $request ) ) );
		}

		return wp_remote_retrieve_body( $request );
	}

	/**
	 * Attempt to deactivate a licence
	 *
	 * @since 2.0
	 *
	 * @return string JSON response
	 */
	public static function deactivate( $args ) {
		$defaults = array(
			'request'  => 'deactivation',
			'platform' => site_url(),
		);

		$args    = wp_parse_args( $defaults, $args );
		$request = wp_remote_get( self::$endpoint . '&' . http_build_query( $args, '', '&' ), array( 'timeout' => 10 ) );

		if( is_wp_error( $request ) ) {
			return json_encode( array('error_code' => $request->get_error_code(), 'error' => $request->get_error_message()) );
		}

		if( wp_remote_retrieve_response_code( $request ) != 200 ) {
			return json_encode( array('error_code' => wp_remote_retrieve_response_code( $request ), 'error' => 'Error code: ' . wp_remote_retrieve_response_code( $request )) );
		}

		return wp_remote_retrieve_body( $request );
	}
}
