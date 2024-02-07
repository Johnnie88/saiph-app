<?php
namespace BooklyPro\Lib\DataHolders\Details;

use Bookly\Lib\DataHolders\Details\Base;
use Bookly\Lib\Entities\Payment;

class Adjustment extends Base
{
    protected $type = Payment::ITEM_ADJUSTMENT;

    protected $fields = array(
        'reason',
        'amount',
        'tax',
    );

    public function getAmount()
    {
        return $this->getValue( 'amount', 0 );
    }

    public function getTax()
    {
        return $this->getValue( 'tax', 0 );
    }
}