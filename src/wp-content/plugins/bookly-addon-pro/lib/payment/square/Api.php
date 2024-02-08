<?php
namespace BooklyPro\Lib\Payment\Square;

use Bookly\Lib\Config;

class Api
{
    const API_LIVE = 'https://connect.squareup.com/v2/';
    const API_SANDBOX = 'https://connect.squareupsandbox.com/v2/';
    const SQUARE_VERSION = '2022-09-21';

    /** @var string */
    private $access_token;
    /** @var string */
    private $api_endpoint;
    /** @var string */
    private $location_id;

    /**
     * @param string $access_token
     * @param string $location_id
     * @param bool $sandbox
     */
    public function __construct( $access_token, $location_id, $sandbox )
    {
        $this->access_token = $access_token;
        $this->location_id = $location_id;
        $this->api_endpoint = $sandbox
            ? self::API_SANDBOX
            : self::API_LIVE;
    }

    /**
     * Get order data
     *
     * @param string $order_id
     * @return array
     * @throws \Exception
     */
    public function retrieveOrder( $order_id )
    {
        $response = wp_remote_get( $this->api_endpoint . 'orders/' . $order_id, array(
            'sslverify' => false,
            'timeout' => 25,
            'headers' => array(
                'Square-Version' => self::SQUARE_VERSION,
                'Accept' => 'application/json',
                'Authorization' => $this->getAuthorizationToken(),
            ),
        ) );
        $data = $this->parseResponse( $response );

        if ( isset( $data['order']['state'] )
            && $data['order']['location_id'] === $this->location_id
            && $data['order']['total_money']['currency'] === Config::getCurrency()
        ) {
            return array(
                'status' => $data['order']['state'],
                'metadata' => $data['order']['metadata'],
                'total' => $data['order']['total_money']['amount'],
            );
        }
        throw new \Exception( 'Invalid order' );
    }

    /**
     * @return string
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function sendCheckoutRequest( $data )
    {
        $response = wp_remote_post( $this->api_endpoint . 'locations/' . $this->location_id . '/checkouts', array(
            'sslverify' => false,
            'timeout' => 25,
            'headers' => array(
                'Square-Version' => self::SQUARE_VERSION,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => $this->getAuthorizationToken(),
            ),
            'body' => json_encode( $data ),
        ) );

        return $this->parseResponse( $response );
    }

    /**
     * @return string
     */
    public function getSiteUrl()
    {
        return str_replace( '/v2/', '', $this->api_endpoint );
    }

    /**
     * @param $response
     * @return array
     * @throws \Exception
     */
    private function parseResponse( $response )
    {
        if ( is_wp_error( $response ) ) {
            throw new \Exception( $response->get_error_message( '' ) );
        } elseif ( isset( $response['response'] ) && $response['response']['code'] == 200 ) {
            return json_decode( $response['body'], true );
        } else {
            $json = json_decode( $response['body'], true );
            if ( isset( $json['errors'][0]['detail'] ) ) {
                throw new \Exception( $json['errors'][0]['detail'] );
            }
            throw new \Exception( $response['response']['message'] );
        }
    }

    /**
     * @return string
     */
    protected function getAuthorizationToken()
    {
        return 'Bearer ' . $this->access_token;
    }
}