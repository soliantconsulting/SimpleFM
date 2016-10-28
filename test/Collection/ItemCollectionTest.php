<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Collection;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Collection\ItemCollection;

final class ItemCollectionTest extends TestCase
{
    public function testGetIterator()
    {
        $collection = new ItemCollection(['foo', 'bar'], 2);
        $this->assertFalse($collection->isEmpty());
        $this->assertSame(['foo', 'bar'], iterator_to_array($collection->getIterator()));
    }

    public function testEmptyCollection()
    {
        $collection = new ItemCollection([], 0);
        $this->assertTrue($collection->isEmpty());
        $this->assertNull($collection->first());
    }

    public function testFirst()
    {
        $collection = new ItemCollection(['foo', 'bar'], 2);
        $this->assertSame('foo', $collection->first());
    }

    public function testCount()
    {
        $collection = new ItemCollection(['foo', 'bar'], 4);
        $this->assertSame(2, count($collection));
    }

    public function testGetTotalCount()
    {
        $collection = new ItemCollection(['foo', 'bar'], 4);
        $this->assertSame(4, $collection->getTotalCount());
    }
}
