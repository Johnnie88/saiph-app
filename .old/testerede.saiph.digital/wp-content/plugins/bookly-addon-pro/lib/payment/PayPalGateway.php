<?php
namespace BooklyPro\Lib\Payment;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Config;
use Bookly\Lib\Entities\Payment;

class PayPalGateway extends BooklyLib\Base\Gateway
{
    const TYPE_EXPRESS_CHECKOUT = 'ec';
    const TYPE_PAYMENTS_STANDARD = 'ps';
    const TYPE_CHECKOUT = 'checkout';

    protected $type = Payment::TYPE_PAYPAL;

    /**
     * @inerhitDoc
     */
    protected function getCheckoutUrl( array $intent_data )
    {
        return $intent_data['checkout_url'];
    }

    /**
     * @inerhitDoc
     */
    protected function getInternalMetaData()
    {
        return array();
    }

    /**
     * @inerhitDoc
     */
    protected function createGatewayIntent()
    {
        $data = array(
            'BRANDNAME' => get_option( 'bookly_co_name' ),
            'SOLUTIONTYPE' => 'Sole',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_CURRENCYCODE' => Config::getCurrency(),
            'NOSHIPPING' => 1,
            'RETURNURL' => $this->getResponseUrl( self::EVENT_RETRIEVE ),
            'CANCELURL' => $this->getResponseUrl( self::EVENT_CANCEL ),
        );
        $data['L_PAYMENTREQUEST_0_NAME0'] = $this->request->getUserData()->cart->getItemsTitle( 126 );
        $data['L_PAYMENTREQUEST_0_AMT0'] = $this->getGatewayAmount();
        $data['L_PAYMENTREQUEST_0_QTY0'] = 1;

        $total = $this->getGatewayAmount();
        $data['PAYMENTREQUEST_0_ITEMAMT'] = $total;
        $data['PAYMENTREQUEST_0_AMT'] = $total + $this->getGatewayTax();
        if ( get_option( 'bookly_paypal_send_tax' ) ) {
            $data['PAYMENTREQUEST_0_TAXAMT'] = $this->getGatewayTax();
        }
        $data['PAYMENTREQUEST_0_NOTIFYURL'] = $this->getWebHookUrl();

        $response = $this->sendNvpRequest( 'SetExpressCheckout', $data );

        // Respond according to message we receive from PayPal
        $ack = strtoupper( $response['ACK'] );
        if ( $ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING' ) {
            return array(
                'ref_id' => $response['TOKEN'],
                'checkout_url' => sprintf( 'https://www%s.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=%s', get_option( 'bookly_paypal_sandbox' ) ? '.sandbox' : '', urlencode( $response['TOKEN'] ) ),
            );
        }

        throw new \Exception( $response['L_LONGMESSAGE0'] );
    }

    /**
     * @inerhitDoc
     */
    public function retrieveStatus()
    {
        $data = array( 'TOKEN' => $this->getPayment()->getRefId() );
        // Send the request to PayPal.
        $response = $this->sendNvpRequest( 'GetExpressCheckoutDetails', $data );
        if ( ! array_key_exists( 'PAYERID', $response ) ) {
            // Customer close window with PayPal page
            return self::STATUS_FAILED;
        }

        if ( ( strtoupper( $response['ACK'] ) == 'SUCCESS' )
            && ( $this->validatePaymentData( $response['AMT'], $response['CURRENCYCODE'] ) ) )
        {
            $data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';

            foreach (
                array(
                    'L_PAYMENTREQUEST_0_AMT0',
                    'L_PAYMENTREQUEST_0_NAME0',
                    'L_PAYMENTREQUEST_0_QTY0',
                    'PAYMENTREQUEST_0_AMT',
                    'PAYMENTREQUEST_0_CURRENCYCODE',
                    'PAYMENTREQUEST_0_ITEMAMT',
                    'PAYMENTREQUEST_0_TAXAMT',
                    'PAYMENTREQUEST_0_NOTIFYURL',
                    'PAYERID',
                ) as $parameter
            ) {
                if ( array_key_exists( $parameter, $response ) ) {
                    $data[ $parameter ] = $response[ $parameter ];
                }
            }

            // We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
            $response = $this->sendNvpRequest( 'DoExpressCheckoutPayment', $data );
            if ( 'SUCCESS' == strtoupper( $response['ACK'] ) || 'SUCCESSWITHWARNING' == strtoupper( $response['ACK'] ) ) {
                // Get transaction info
                $response = $this->sendNvpRequest( 'GetTransactionDetails', array( 'TRANSACTIONID' => $response['PAYMENTINFO_0_TRANSACTIONID'] ) );
                if ( 'SUCCESS' == strtoupper( $response['ACK'] ) || 'SUCCESSWITHWARNING' == strtoupper( $response['ACK'] ) ) {
                    return self::STATUS_COMPLETED;
                }
            }

            $path = explode( '\\', get_class( $this ) );
            BooklyLib\Utils\Log::put( BooklyLib\Utils\Log::ACTION_ERROR, array_pop( $path ), null, json_encode( $response ), $this->getPayment()->getRefId(), 'retrieve' );

            throw new \Exception( $response['L_LONGMESSAGE0'] );
        }


        return self::STATUS_PROCESSING;
    }

    /**
     * @param string $method
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function sendNvpRequest( $method, array $data )
    {
        $paypal_response = array();
        $url = 'https://api-3t' . ( get_option( 'bookly_paypal_sandbox' ) ? '.sandbox' : '' ) . '.paypal.com/nvp';

        $data['METHOD'] = $method;
        $data['VERSION'] = '216.0';
        $data['USER'] = get_option( 'bookly_paypal_api_username' );
        $data['PWD'] = get_option( 'bookly_paypal_api_password' );
        $data['SIGNATURE'] = get_option( 'bookly_paypal_api_signature' );

        $response = wp_remote_post( $url, array(
            'sslverify' => false,
            'body' => $data,
            'timeout' => 25,
        ) );
        if ( $response instanceof \WP_Error ) {
            throw new \Exception( 'Invalid HTTP Response for POST request to ' . $url );
        }

        parse_str( $response['body'], $paypal_response );

        if ( ! array_key_exists( 'ACK', $paypal_response ) ) {
            throw new \Exception( 'Invalid HTTP Response for POST request to ' . $url );
        }

        return $paypal_response;
    }
}