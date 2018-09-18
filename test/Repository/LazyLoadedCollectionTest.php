<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Soliant\SimpleFM\Collection\ItemCollection;
use Soliant\SimpleFM\Query\Conditions;
use Soliant\SimpleFM\Query\Field;
use Soliant\SimpleFM\Query\Query;
use Soliant\SimpleFM\Repository\LazyLoadedCollection;
use Soliant\SimpleFM\Repository\RepositoryInterface;
use stdClass;

final class LazyLoadedCollectionTest extends TestCase
{
    public function testGetIterator() : void
    {
        $first = new stdClass();
        $second = new stdClass();
        $third = new stdClass();

        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findByQuery(new Query(
            new Conditions(false, new Field('foo', '1')),
            new Conditions(false, new Field('foo', '2')),
            new Conditions(false, new Field('foo', '3'))
        ))->willReturn(new ItemCollection([
            $first,
            $second,
            $third,
        ], 3));

        $collection = new LazyLoadedCollection($repository->reveal(), 'foo', [
            ['foo' => 1],
            ['foo' => 2],
            ['foo' => 3],
        ]);
        $entities = [];

        $this->assertFalse($collection->isEmpty());

        foreach ($collection as $entity) {
            $entities[] = $entity;
        }

        $this->assertSame($first, $entities[0]);
        $this->assertSame($second, $entities[1]);
        $this->assertSame($third, $entities[2]);
    }

    public function testEmptyCollection() : void
    {
        $collection = new LazyLoadedCollection($this->prophesize(RepositoryInterface::class)->reveal(), 'foo', []);
        $this->assertTrue($collection->isEmpty());
        $this->assertNull($collection->first());
    }

    public function testIteratorCaching() : void
    {
        $collection = new LazyLoadedCollection($this->prophesize(RepositoryInterface::class)->reveal(), 'foo', []);
        $this->assertSame($collection->getIterator(), $collection->getIterator());
    }

    public function testFirst() : void
    {
        $first = new stdClass();
        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findByQuery(Argument::any())->will(function () use ($first) {
            return new ItemCollection([
                $first,
                new stdClass(),
                new stdClass(),
            ], 3);
        });

        $collection = new LazyLoadedCollection($repository->reveal(), 'foo', [
            ['foo' => 1],
            ['foo' => 2],
            ['foo' => 3],
        ]);
        $this->assertSame($first, $collection->first());
    }

    public function testCount() : void
    {
        $collection = new LazyLoadedCollection(
            $this->prophesize(RepositoryInterface::class)->reveal(),
            'foo',
            [
                ['foo' => 1],
                ['foo' => 2],
                ['foo' => 3],
            ]
        );
        $this->assertSame(3, count($collection));
    }
}
