<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Collection;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Collection\ItemCollection;

final class ItemCollectionTest extends TestCase
{
    public function testGetIterator() : void
    {
        $collection = new ItemCollection(['foo', 'bar']);
        $this->assertFalse($collection->isEmpty());
        $this->assertSame(['foo', 'bar'], iterator_to_array($collection->getIterator()));
    }

    public function testEmptyCollection() : void
    {
        $collection = new ItemCollection([]);
        $this->assertTrue($collection->isEmpty());
        $this->assertNull($collection->first());
    }

    public function testFirst() : void
    {
        $collection = new ItemCollection(['foo', 'bar']);
        $this->assertSame('foo', $collection->first());
    }

    public function testCount() : void
    {
        $collection = new ItemCollection(['foo', 'bar']);
        $this->assertSame(2, count($collection));
    }
}
