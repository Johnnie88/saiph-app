<?php
namespace BooklyPro\Frontend\Modules\WooCommerce;

use Bookly\Lib as BooklyLib;
use Bookly\Frontend\Modules\Booking\Lib\Errors;

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
     * Add product to cart
     *
     * return string JSON
     */
    public static function addToWoocommerceCart()
    {
        $userData = new BooklyLib\UserBookingData( self::parameter( 'form_id' ) );

        if ( $userData->load() ) {
            $failed_cart_key = Controller::addToCart( $userData );
            $failed_cart_key === null
                ? wp_send_json_success( array( 'target_url' => wc_get_cart_url() ) )
                : wp_send_json_error( array( 'error' => Errors::CART_ITEM_NOT_AVAILABLE, 'failed_cart_key' => $failed_cart_key, ) );
        }

        Errors::sendSessionError();
    }

}