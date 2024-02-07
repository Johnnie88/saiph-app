<?php
namespace BooklyPro\Lib\Payment\ProxyProviders;

use Bookly\Frontend\Modules\Payment;
use Bookly\Lib\CartInfo;
use Bookly\Lib\Payment\Proxy;
use Bookly\Lib\Entities;
use Bookly\Lib\CartItem;
use Bookly\Lib\Config;
use Bookly\Lib\UserBookingData;
use BooklyPro\Lib\Entities\GiftCardType;
use BooklyPro\Lib\Payment\PayPalGateway;
use BooklyPro\Lib\Payment\SquareGateway;
use Bookly\Lib\DataHolders;
use BooklyPro\Lib\DataHolders\Details;

class Shared extends Proxy\Shared
{
    /**
     * @inerhitDoc
     */
    public static function create( $item_key, DataHolders\Booking\Order $order, CartItem $cart_item, UserBookingData $userData )
    {
        if ( $cart_item->getType() === CartItem::TYPE_GIFT_CARD ) {
            $item = new \BooklyPro\Lib\DataHolders\Booking\GiftCard();
            $item->setGiftCardType( $cart_item->getCartTypeId() )
                ->setNotes( $userData->getNotes() );

            $order->addItem( $item_key++, $item );
        }

        return $item_key;
    }

    /**
     * @inerhitDoc
     */
    public static function getGatewayByName( $gateway, Payment\Request $request )
    {
        if ( $gateway === Entities\Payment::TYPE_PAYPAL && get_option( 'bookly_paypal_enabled' ) === PayPalGateway::TYPE_EXPRESS_CHECKOUT ) {
            return new PayPalGateway( $request );
        }
        if ( $gateway === Entities\Payment::TYPE_CLOUD_SQUARE ) {
            return new SquareGateway( $request );
        }

        return $gateway;
    }

    /**
     * @inheritDoc
     */
    public static function applyGateway( CartInfo $cart_info, $gateway )
    {
        if ( $gateway === Entities\Payment::TYPE_PAYPAL && Config::paypalEnabled() ) {
            $cart_info->setGateway( $gateway );
        } elseif ( $gateway === Entities\Payment::TYPE_CLOUD_SQUARE && Config::squareEnabled() ) {
            $cart_info->setGateway( $gateway );
        }

        return $cart_info;
    }

    /**
     * @inheritDoc
     */
    public static function prepareOutdatedUnpaidPayments( $payments )
    {
        $timeout = (int) get_option( 'bookly_paypal_timeout' );
        if ( $timeout && get_option( 'bookly_paypal_enabled' ) ) {
            $payments = array_merge( $payments, Entities\Payment::query()
                ->where( 'type', Entities\Payment::TYPE_PAYPAL )
                ->where( 'status', Entities\Payment::STATUS_PENDING )
                ->whereLt( 'created_at', date_create( current_time( 'mysql' ) )->modify( sprintf( '- %s seconds', $timeout ) )->format( 'Y-m-d H:i:s' ) )
                ->fetchCol( 'id' )
            );
        }

        return $payments;
    }

    /**
     * @inheritDoc
     */
    public static function showPaymentSpecificPrices( $show )
    {
        if ( ! $show ) {
            if ( Config::paypalEnabled() && self::paymentSpecificPriceExists( Entities\Payment::TYPE_PAYPAL ) ) {
                return true;
            }
            if ( Config::squareEnabled() && self::paymentSpecificPriceExists( Entities\Payment::TYPE_CLOUD_SQUARE ) ) {
                return true;
            }
        }

        return $show;
    }

    /**
     * @inerhitDoc
     */
    public static function paymentSpecificPriceExists( $gateway )
    {
        if ( $gateway === Entities\Payment::TYPE_PAYPAL ) {
            return get_option( 'bookly_paypal_increase' ) != 0 || get_option( 'bookly_paypal_addition' ) != 0;
        }
        if ( $gateway === Entities\Payment::TYPE_CLOUD_SQUARE ) {
            return get_option( 'bookly_cloud_square_increase' ) != 0 || get_option( 'bookly_cloud_square_addition' ) != 0;
        }

        return $gateway;
    }

    /**
     * @param string $default
     * @param CartItem $cart_item
     * @return string
     */
    public static function getTranslatedTitle( $default, CartItem $cart_item )
    {
        if ( $cart_item->getType() === CartItem::TYPE_GIFT_CARD ) {
            $default = GiftCardType::find( $cart_item->getCartTypeId() )->getTranslatedTitle();
        }

        return $default;
    }

    /**
     * @inerhitDoc
     */
    public static function paymentCreateDetailsFromItem( $details, DataHolders\Booking\Item $item )
    {
        if ( $item->getType() === DataHolders\Booking\Item::TYPE_GIFT_CARD ) {
            $details = new Details\GiftCard();
        }

        return $details;
    }

    /**
     * @inerhitDoc
     */
    public static function paymentCreateDetailsByType( $details, $type )
    {
        if ( $type === Entities\Payment::ITEM_GIFT_CARD ) {
            $details = new Details\GiftCard();
        }

        return $details;
    }
}