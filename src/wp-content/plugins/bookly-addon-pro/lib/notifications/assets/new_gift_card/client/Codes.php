<?php
namespace BooklyPro\Lib\Notifications\Assets\NewGiftCard\Client;

use Bookly\Lib\Entities\Customer;
use Bookly\Lib\Notifications\Assets\ClientBirthday;
use Bookly\Lib\Utils\Price;
use BooklyPro\Lib\Entities\GiftCard;
use Bookly\Lib\Utils;
use BooklyPro\Lib\Entities\GiftCardType;

class Codes extends ClientBirthday\Codes
{
    /** @var GiftCard */
    protected $gift_card;
    /** @var GiftCard */
    protected $gift_card_type;

    /**
     * @param GiftCard $gift_card
     * @param int $customer_id
     */
    public function __construct( GiftCard $gift_card, $customer_id = null )
    {
        $customer = Customer::find( $gift_card->getCustomerId() ?: $customer_id );
        parent::__construct( $customer ?: new Customer() );
        $this->gift_card = $gift_card;
        $this->gift_card_type = GiftCardType::find( $gift_card->getGiftCardTypeId() );
    }

    /**
     * @inheritDoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );

        // Add replace codes.
        $replace_codes += array(
            'gift_card' => $this->gift_card->getCode(),
            'gift_card_amount' => Price::format( $this->gift_card_type->getAmount() ),
            'gift_card_note' => $this->gift_card->getNotes(),
            'gift_card_date_limit_from' => $this->gift_card_type->getStartDate() ? Utils\DateTime::formatDate( $this->gift_card_type->getStartDate() ) : '',
            'gift_card_date_limit_to' => $this->gift_card_type->getEndDate() ? Utils\DateTime::formatDate( $this->gift_card_type->getEndDate() ) : '',
            'site_address' => site_url(),
            'service_name' => $this->gift_card_type->getTranslatedTitle(),
            'service_info' => $this->gift_card_type->getTranslatedInfo(),
            'service_price' => Price::format( $this->gift_card_type->getAmount() ),
        );

        return $replace_codes;
    }
}