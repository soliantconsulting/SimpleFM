<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Collection;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

final class ItemCollection implements IteratorAggregate, CollectionInterface
{
    /**
     * @var ArrayIterator
     */
    private $items;

    /**
     * @var int
     */
    private $totalCount;

    public function __construct(array $items, int $totalCount)
    {
        $this->items = new ArrayIterator($items);
        $this->totalCount = $totalCount;
    }

    public function count() : int
    {
        return count($this->items);
    }

    public function getTotalCount() : int
    {
        return $this->totalCount;
    }

    public function isEmpty() : bool
    {
        return 0 === count($this->items);
    }

    public function first()
    {
        if ($this->isEmpty()) {
            return null;
        }

        $this->items->rewind();
        return $this->items->current();
    }

    public function getIterator() : Traversable
    {
        return $this->items;
    }
}
