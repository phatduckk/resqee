<?php

class Resqee_Persistence_SearchResults
implements Countable, ArrayAccess, IteratorAggregate
{
    /**
     * Total number of results when not bounded by pagination params
     *
     * @var int
     */
    private $total = 0;

    /**
     * The resulting Resqee_Persistence_Item objects
     *
     * @var ArrayObject
     */
    private $items = null;

    /**
     * Iterator
     *
     * @var Iterator
     */
    private $iter = null;

    /**
     * Total # of pages of results
     *
     * Indexed by 1
     *
     * @var int
     */
    private $numPages = 0;

    /**
     * Current page
     *
     * Indexed by 1
     *
     * @var int
     */
    private $currentPage = 0;

    /**
     * # of results per page
     *
     * @var it
     */
    private $limit = 0;

    /**
     * Last index in the iterator
     *
     * @var int
     */
    private $lastIndex = 0;

    /**
     * Constructor
     *
     * @param int   $total Total @ of results w/o pagination bounds
     * @param array $items An array of Resqee_Persistence_Item items
     */
    public function __construct(Resqee_Persistence_SearchParams $params,
        $total = 0, array $items = array())
    {
        $this->total       = $total;
        $this->limit       = $params->limit;
        $this->items       = new ArrayObject($items);
        $this->iter        = $this->items->getIterator();
        $this->numPages    = (int) ceil($total / $params->limit);
        $this->currentPage = (int) floor($params->offset / $params->limit) + 1;
        $this->lastIndex   = ($this->count() > 0);
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
     * @return Resqee_Persistence_Item
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

    /**
     * Get the # of pges of results
     *
     * @return int
     */
    public function getNumPages()
    {
        return $this->numPages;
    }

    /**
     * Get total # of results
     *
     * Not bounded by pagination constraints
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Get the current page #
     *
     * Indexed by 1
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get the # of results per page
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Check if the current position in the iterator is even
     *
     * @return bool
     */
    public function isEven()
    {
        return $this->nth(2);
    }

    /**
     * Check is the current position in the iterator is devisable by $n
     *
     * @param int $n
     *
     * @return bool
     */
    public function nth($n)
    {
        $c = $this->iter->key() + 1;
        return($c) ?($c %($n) === 0) : false;
    }

    /**
     * Check if the current position in the iterator is odd
     *
     * @return bool
     */
    public function isOdd()
    {
        return ! $this->isEven();
    }

    public function getOddEven()
    {
        return($this->even()) ? self::EVEN : self::ODD;
    }

    /**
     * Check if we're at the last position in the iterator
     *
     * @return bool
     */
    public function isLast()
    {
        return($this->iter->key() === $this->lastIndex);
    }

    /**
     * Check if we're at the first position in the iterator
     *
     * @return bool
     */
    public function isFirst()
    {
        return($this->iter->key() === 0);
    }

}
?>