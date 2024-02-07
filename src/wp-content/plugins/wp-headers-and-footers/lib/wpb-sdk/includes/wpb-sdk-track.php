<?php
/**
 * Send tracking data (logs) to WPB server.
 *
 * @package WPB_SDK
 * @since 1.3.1
 */

namespace WPHeaderAndFooter_SDK;

/**
 * Class responsible for sending logs data.
 */
class Track {

	/**
	 * The logger data to be send.
	 *
	 * @var array
	 */
	private $payload = array();

	/**
	 * Logger tracking endpoint.
	 *
	 * @var string
	 */
	private $tracking_endpoint = 'https://app.telemetry.wpbrigade.com/api/logger';

	/**
	 * Class constructor.
	 *
	 * @param Obj $payload variable.
	 *
	 * @return void
	 */
	public function __construct( $payload ) {

		$this->payload = $payload;
	}

	/**
	 * Send the logs to the api endpoint.
	 *
	 * @return object
	 */
	public function send() {

		wp_remote_post(
			$this->tracking_endpoint,
			array(
				'method'  => 'POST',
				'body'    => $this->payload,
				'timeout' => 5,
				'headers' => array(),
			)
		);
	}
}

