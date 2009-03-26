<?php

class Resqee_Persistence_SearchResults
implements Countable, ArrayAccess, IteratorAggregate
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
     * Iterator
     *
     * @var Iterator
     */
    private $iter = null;

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
        $this->iter  = $this->items->getIterator();
    }

    /**
     * Get the iterator
     *
     * @return Iterator
     */
    public function getIterator()
    {
        return $this->iter;
    }

    /**
     * Check if an offset exists
     *
     * @param offset The offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->items->offsetExists($offet);
    }

    /**
     * Get item at an offset
     *
     * @param int offset The offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items->offsetGet($offset);
    }

    /**
     * Set the offset
     *
     * @param int   offset The offset
     * @param mixed value  The value for the offset
     */
    public function offsetSet($offset, $value)
    {
        return $this->items->offsetSet($offset, $value);
    }

    /**
     * Unset variable at an offser
     *
     * @param int offset The offset
     */
    public function offsetUnset($offset)
    {
        return $this->items->offsetUnset($offset);
    }

    /**
     * Get the number of results
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }
}
?>