<?php

class Resqee_Persistence_SearchResults implements ArrayAccess, Countable
{
    /**
     * Total number of results when not bounded by pagination params
     *
     * @var int
     */
    public $total = 0;

    /**
     * The resulting Resqee_Persistence_Item objects
     *
     * @var ArrayObject
     */
    public $items = null;

    /**
     * Constructor
     *
     * @param int   $total Total @ of results w/o pagination bounds
     * @param array $items An array of Resqee_Persistence_Item items
     */
    public function __construct($total = 0, array $items = array())
    {
        $this->items = new ArrayObject($items);
        $this->total = $total;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $i
     * @return unknown
     */
    public function offsetGet($i)
    {
        return $this->items->offsetExists($i);
    }

}
?>