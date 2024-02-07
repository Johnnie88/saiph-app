<?php
namespace BooklyPro\Backend\Components\Dialogs\Payment\ProxyProviders;

use Bookly\Backend\Components\Dialogs\Payment\Proxy;
use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

class Shared extends Proxy\Shared
{
    /**
     * @inerhitDoc
     */
   public static function preparePaymentInfo( $payment_info, $total )
   {
       if ( Lib\Config::needCreateWCOrder( $total ) ) {
           $payment_info['payment_title'] = BooklyLib\Entities\Payment::paymentInfo( $total, $total, BooklyLib\Entities\Payment::TYPE_WOOCOMMERCE, BooklyLib\Entities\Payment::STATUS_COMPLETED );
           $payment_info['payment_type'] = 'full';
       }

       return $payment_info;
   }
}