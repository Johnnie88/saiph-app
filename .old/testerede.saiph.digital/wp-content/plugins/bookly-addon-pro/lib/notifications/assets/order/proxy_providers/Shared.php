<?php
namespace BooklyPro\Lib\Notifications\Assets\Order\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Notifications\Assets\Order\Codes;
use Bookly\Lib\Notifications\Assets\Order\Proxy;
use BooklyPro\Lib\Entities;

abstract class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function prepareCodes( Codes $codes )
    {
        $customer = $codes->getOrder()->getCustomer();
        if ( $customer && $customer->getBirthday() ) {
            $codes->client_birthday = date_i18n( 'F j', strtotime( $customer->getBirthday() ) );
            $codes->client_full_birthday = BooklyLib\Utils\DateTime::formatDate( $customer->getBirthday() );
        } else {
            $codes->client_birthday = '';
            $codes->client_full_birthday = '';
        }
        if ( $codes->getOrder()->hasPayment() && $codes->getOrder()->getPayment()->getGiftCardId() ) {
            $codes->gift_card = Entities\GiftCard::query()->where( 'id', $codes->getOrder()->getPayment()->getGiftCardId() )->fetchVar( 'code' );
        } else {
            $codes->gift_card = '';
        }
    }

    /**
     * @inheritDoc
     */
    public static function prepareReplaceCodes( array $replace_codes, Codes $codes, $format )
    {
        $replace_codes['client_birthday'] = $codes->client_birthday;
        $replace_codes['client_full_birthday'] = $codes->client_full_birthday;
        $replace_codes['gift_card'] = $codes->gift_card;

        return $replace_codes;
    }
}