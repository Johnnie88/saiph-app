<?php
namespace BooklyPro\Lib\Payment;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Payment;
use BooklyPro\Lib\Payment\Square\LineItem;
use BooklyPro\Lib\Payment\Square\Money;
use BooklyPro\Lib\Payment\Square\Order;

class SquareGateway extends BooklyLib\Base\Gateway
{
    protected $type = Payment::TYPE_CLOUD_SQUARE;

    /**
     * @inerhitDoc
     */
    protected function getCheckoutUrl( array $intent_data )
    {
        return $intent_data['target_url'];
    }

    /**
     * @inerhitDoc
     */
    protected function getInternalMetaData()
    {
        return array(
            'payment_id' => (string) $this->getPayment()->getId(),
        );
    }

    /**
     * @inerhitDoc
     */
    protected function createGatewayIntent()
    {
        $api = $this->getApiClient();

        $line_item = new LineItem();
        $line_item
            ->setQuantity( 1 )
            ->setName( $this->request->getUserData()->cart->getItemsTitle() )
            ->setBasePriceMoney( new Money( $this->getGatewayAmount() ) );

        $sq_order = new Order();
        $sq_order
            ->addLineItem( $line_item )
            ->setLocationId( $api->getLocationId() )
            ->setMetadata( $this->getMetaData() );

        $body = array(
            'idempotency_key' => wp_generate_password( 32, false ),
            'order' => array(
                'idempotency_key' => wp_generate_password( 32, false ),
                'order' => $sq_order->toArray(),
            ),
            'pre_populate_buyer_email' => $this->request->getUserData()->getEmail(),
            'redirect_url' => $this->getResponseUrl( self::EVENT_RETRIEVE ),
            'ask_for_shipping_address' => false,
        );

        $response = $api->sendCheckoutRequest( $body );

        if ( isset( $response['checkout']['checkout_page_url'] ) ) {
            $target_url = $response['checkout']['checkout_page_url'];
            if ( strncmp( $target_url, 'http', 4 ) !== 0 ) {
                $target_url = $api->getSiteUrl() . $target_url;
            }
            $data = compact( 'target_url' );
            if ( isset( $response['checkout']['order']['id'] ) ) {
                $data['ref_id'] = $response['checkout']['order']['id'];
            }

            return $data;
        }

        throw new \Exception( 'Invalid response' );
    }

    /**
     * @inerhitDoc
     */
    public function retrieveStatus()
    {
        $data = $this->getApiClient()->retrieveOrder( $this->getPayment()->getRefId() );
        switch ( $data['status'] ) {
            case 'CANCELED':
                return self::STATUS_FAILED;
            case 'COMPLETED':
                return self::STATUS_COMPLETED;
            case 'DRAFT':
            case 'OPEN':
            default:
                return self::STATUS_PROCESSING;
        }
    }

    /**
     * @return Square\Api
     */
    private function getApiClient()
    {
        return new Square\Api(
            get_option( 'bookly_cloud_square_api_access_token' ),
            get_option( 'bookly_cloud_square_api_location_id' ),
            get_option( 'bookly_cloud_square_sandbox' )
        );
    }
}