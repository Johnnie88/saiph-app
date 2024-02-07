<?php
namespace BooklyPro\Frontend\Modules\Square;

use Bookly\Lib as BooklyLib;
use Bookly\Frontend\Modules\Payment;
use BooklyPro\Lib\Payment\SquareGateway;

class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'anonymous' );
    }

    /**
     * Processing Square webhooks
     */
    public static function squareWebhooks()
    {
        $data = json_decode( file_get_contents( 'php://input' ), true );
        $response_code = 200;
        if ( isset( $data['type'] ) && $data['type'] === 'order.updated' ) {
            try {
                /** @var BooklyLib\Entities\Payment $payment */
                $payment = BooklyLib\Entities\Payment::query()->where( 'ref_id', $data['data']['id'] )->findOne();
                if ( $payment && $payment->getStatus() === BooklyLib\Entities\Payment::STATUS_PENDING ) {
                    $square = new SquareGateway( Payment\Request::getInstance() );
                    $square->setPayment( $payment )->retrieve();
                }
            } catch ( \Exception $e ) {
                $response_code = 400;
            }
        }
        BooklyLib\Utils\Common::emptyResponse( $response_code );
    }

    /**
     * @inheritDoc
     */
    protected static function csrfTokenValid( $action = null )
    {
        return true;
    }
}