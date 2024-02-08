<?php
namespace BooklyPro\Backend\Components\Dialogs\GiftCard\Card;

use Bookly\Lib as BooklyLib;

class Dialog extends BooklyLib\Base\Component
{
    /**
     * Render Gift card dialog.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'backend' => array( 'css/fontawesome-all.min.css' => array( 'bookly-backend-globals' ), ),
        ) );

        self::enqueueScripts( array(
            'module' => array( 'js/gift-card-dialog.js' => array( 'bookly-customer.js', 'bookly-queue-dialog.js', 'bookly-payment-details-dialog.js', 'bookly-attach-payment.js' ) ),
        ) );

        if ( BooklyLib\Entities\Customer::query()->count() < BooklyLib\Entities\Customer::REMOTE_LIMIT ) {
            $remote = false;
            $customers = BooklyLib\Entities\Customer::query()
                ->select( 'id, full_name AS text, email, phone' )->sortBy( 'full_name' )->fetchArray();
            foreach ( $customers as &$customer ) {
                $name = $customer['text'];
                if ( $customer['email'] != '' || $customer['phone'] != '' ) {
                    $name .= ' (' . trim( $customer['email'] . ', ' . $customer['phone'], ', ' ) . ')';
                }
                $customer['name'] = $name;
            }
        } else {
            $customers = array();
            $remote = true;
        }

        wp_localize_script( 'bookly-gift-card-dialog.js', 'BooklyL10nGiftCardDialog', array(
            'send_notifications' => (int) get_user_meta( get_current_user_id(), 'bookly_gift_card_form_send_notifications', true ),
            'customers' => array(
                'collection' => $customers,
                'remote' => $remote,
            ),
            'l10n' => array(
                'save' => __( 'Save', 'bookly' ),
                'cancel' => __( 'Cancel', 'bookly' ),
                'customer' => __( 'Customer', 'bookly' ),
                'entity' => array(
                    'new' => __( 'New gift card', 'bookly' ),
                    'edit' => __( 'Edit gift card', 'bookly' ),
                ),
                'code' => __( 'Code', 'bookly' ),
                'type' => __( 'Type', 'bookly' ),
                'no_result_found' => __( 'No result found', 'bookly' ),
                'searching' => __( 'Searching', 'bookly' ) . '…',
                'search_customer' => __( '-- Search customers --', 'bookly' ),
                'generate' => __( 'Generate', 'bookly' ),
                'code_info' => __( 'You can enter a mask containing asterisks "*" for variables here and click Generate.', 'bookly' ),
                'balance' => __( 'Balance', 'bookly' ),
                'send_notifications' => __( 'Send notifications', 'bookly' ),
                'new_customer' => __( 'New customer', 'bookly' ),
                'payment' => __( 'Payment', 'bookly' ),
                'attach_payment' => __( 'Attach payment', 'bookly' ),
                'remove_customer' => __( 'Remove customer', 'bookly' ),
                'note' => __( 'Note', 'bookly' ),
                'note_info' => __( 'This text can be inserted into notifications with {gift_card_note} code', 'bookly' ),
            ),
        ) );

        print '<div id="bookly-gift-card-dialog"></div>';
    }
}