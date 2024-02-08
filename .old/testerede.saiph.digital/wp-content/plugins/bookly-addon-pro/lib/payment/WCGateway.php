<?php
namespace BooklyPro\Lib\Payment;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Payment;

class WCGateway extends BooklyLib\Base\Gateway
{
    protected $type = Payment::TYPE_WOOCOMMERCE;

    public function retrieveStatus()
    {
    }

    protected function createGatewayIntent()
    {
    }

    protected function getCheckoutUrl( array $intent_data )
    {
    }

    protected function getInternalMetaData()
    {
    }
}