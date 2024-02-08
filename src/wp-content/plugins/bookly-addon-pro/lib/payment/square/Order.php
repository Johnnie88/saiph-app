<?php
namespace BooklyPro\Lib\Payment\Square;

class Order
{
    /** @var array */
    public $metadata;
    /** @var LineItem[] */
    public $line_items;
    /** @var string */
    public $location_id;

    /**
     * @param $metadata
     * @return $this
     */
    public function setMetadata( $metadata )
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @param string $location_id
     * @return $this
     */
    public function setLocationId( $location_id )
    {
        $this->location_id = $location_id;

        return $this;
    }

    /**
     * @param LineItem $line_item
     * @return $this
     */
    public function addLineItem( LineItem $line_item )
    {
        $this->line_items[] = $line_item;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $line_items = array();
        foreach ( $this->line_items as $line_item ) {
            $line_items[] = $line_item->toArray();
        }

        return array(
            'metadata' => $this->metadata,
            'line_items' => $line_items,
            'location_id' => $this->location_id
        );
    }
}