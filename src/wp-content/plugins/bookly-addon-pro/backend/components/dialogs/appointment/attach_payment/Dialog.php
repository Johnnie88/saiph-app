<?php
namespace BooklyPro\Backend\Components\Dialogs\Appointment\AttachPayment;

use Bookly\Lib as BooklyLib;

class Dialog extends BooklyLib\Base\Component
{
    public static function render()
    {
        self::enqueueScripts( array(
            'module' => array( 'js/attach-payment.js' => array( 'bookly-backend-globals' ), ),
        ) );

        wp_localize_script( 'bookly-attach-payment.js', 'BooklyL10nAttachPaymentDialog', array(
            'taxes_included' => get_option( 'bookly_taxes_in_price' ) == 'included',
            'payment_for' => get_user_meta( get_current_user_id(), 'bookly_attach_payment_for', true ) ?: 'current',
            'l10n' => array(
                'attach_payment' => __( 'Attach payment', 'bookly' ),
                'create_payment' => __( 'Create payment', 'bookly' ),
                'search_payment' => __( 'Search payment', 'bookly' ),
                'total_price' => __( 'Total price', 'bookly' ),
                'payment_id' => __( 'Payment ID', 'bookly' ),
                'tax' => __( 'Tax', 'bookly' ),
                'apply' => __( 'Apply', 'bookly' ),
                'cancel' => __( 'Cancel', 'bookly' ),
                'payment_for' => __( 'Payment for', 'bookly' ),
                'payment_for_series' => __( 'Payment for whole series', 'bookly' ),
                'payment_for_current' => __( 'Payment only for current appointment', 'bookly' ),
            )
        ));

        echo '<div id="bookly-attach-payment-dialog"></div>';
    }
}