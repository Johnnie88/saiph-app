<?php
namespace BooklyPro\Backend\Components\Dialogs\Payment;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Payment;
use BooklyPro\Lib\DataHolders\Details;

class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => array( 'staff', 'supervisor' ) );
    }

    /**
     * Create payment adjustment.
     */
    public static function createPaymentAdjustment()
    {
        $payment_id = self::parameter( 'payment_id' );
        $adjustment = self::parameter( 'adjustment' );

        $payment = new Payment();
        $payment->load( $payment_id );

        if ( isset( $adjustment['amount'] ) && is_numeric( $adjustment['amount'] ) ) {
            $adj_details = new Details\Adjustment( $adjustment );
            $payment
                ->setTotal( $payment->getTotal() + $adj_details->getAmount() )
                ->setTax( $payment->getTax() + $adj_details->getTax() )
                ->getDetailsData()
                ->addDetails( $adj_details );

            $payment->save();
        }

        wp_send_json_success();
    }

    /**
     * Update payment adjustment.
     */
    public static function updatePaymentAdjustment()
    {
        $payment_id = self::parameter( 'payment_id' );
        $index = self::parameter( 'index' );
        $adjustment = self::parameter( 'adjustment' );

        $payment = new Payment();
        $payment->load( $payment_id );

        if ( isset( $adjustment['amount'] ) && is_numeric( $adjustment['amount'] ) ) {
            $details = $payment->getDetailsData();
            $adjustments = $details->getValue( 'adjustments', array() );

            if ( isset( $adjustments[ $index ] ) ) {
                $total = $payment->getTotal() - $adjustments[ $index ]['amount'];
                $tax = $payment->getTax() - $adjustments[ $index ]['tax'];
                $adjustments[ $index ] = $adjustment;
                $details->setData( compact( 'adjustments' ) );
                $payment
                    ->setTotal( $total + $adjustment['amount'] )
                    ->setTax( $tax + $adjustment['tax'] )
                    ->save();
            }
        }

        wp_send_json_success();
    }

    /**
     * Delete payment adjustment.
     */
    public static function deletePaymentAdjustment()
    {
        $payment_id = self::parameter( 'payment_id' );
        $index = self::parameter( 'index' );

        $payment = new Payment();
        $payment->load( $payment_id );

        $details = $payment->getDetailsData();
        $adjustments = $details->getValue( 'adjustments', array() );
        if ( isset( $adjustments[ $index ] ) ) {
            $total = $payment->getTotal() - $adjustments[ $index ]['amount'];
            $tax = $payment->getTax() - $adjustments[ $index ]['tax'];
            array_splice( $adjustments, $index, 1 );
            $details->setData( compact( 'adjustments' ) );
            $payment
                ->setTotal( $total )
                ->setTax( $tax )
                ->save();
        }

        wp_send_json_success();
    }
}