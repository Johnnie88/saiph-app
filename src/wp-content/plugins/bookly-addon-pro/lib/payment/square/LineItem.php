<?php
namespace BooklyPro\Lib\Payment\Square;

class LineItem
{
    /** @var int */
    protected $quantity;
    /** @var string */
    protected $name;
    /** @var Money */
    protected $base_price_money;

    /**
     * @param int $quantity
     * @return LineItem
     */
    public function setQuantity( $quantity )
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @param mixed $name
     * @return LineItem
     */
    public function setName( $name )
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param Money $base_price_money
     * @return LineItem
     */
    public function setBasePriceMoney( $base_price_money )
    {
        $this->base_price_money = $base_price_money;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'quantity' => (string) $this->quantity,
            'name' => $this->name,
            'base_price_money' => $this->base_price_money->toArray(),
        );
    }
}