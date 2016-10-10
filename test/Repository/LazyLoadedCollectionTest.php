<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Soliant\SimpleFM\Repository\LazyLoadedCollection;
use Soliant\SimpleFM\Repository\RepositoryInterface;
use stdClass;

final class LazyLoadedCollectionTest extends TestCase
{
    public function testGetIterator()
    {
        $first = new stdClass();
        $second = new stdClass();
        $third = new stdClass();

        $testCase = $this;
        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findByQuery(Argument::any())->will(function (array $parameters) use (
            $testCase,
            $first,
            $second,
            $third
        ) {
            $testCase->assertSame([
                '-query' => '(q1);(q2);(q3)',
                '-q1' => 'foo',
                '-q1.value' => '1',
                '-q2' => 'foo',
                '-q2.value' => '2',
                '-q3' => 'foo',
                '-q3.value' => '3',
            ], $parameters[0]->toParameters());

            return [
                $first,
                $second,
                $third,
            ];
        });

        $collection = new LazyLoadedCollection($repository->reveal(), 'foo', [
            ['foo' => 1],
            ['foo' => 2],
            ['foo' => 3],
        ]);
        $entities = [];

        foreach ($collection as $entity) {
            $entities[] = $entity;
        }

        $this->assertSame($first, $entities[0]);
        $this->assertSame($second, $entities[1]);
        $this->assertSame($third, $entities[2]);
    }

    public function testEmptyCollection()
    {
        $collection = new LazyLoadedCollection($this->prophesize(RepositoryInterface::class)->reveal(), 'foo', []);
        $this->assertNull($collection->first());
    }

    public function testIteratorCaching()
    {
        $collection = new LazyLoadedCollection($this->prophesize(RepositoryInterface::class)->reveal(), 'foo', []);
        $this->assertSame($collection->getIterator(), $collection->getIterator());
    }

    public function testFirst()
    {
        $first = new stdClass();
        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findByQuery(Argument::any())->will(function () use ($first) {
            return [
                $first,
                new stdClass(),
                new stdClass(),
            ];
        });

        $collection = new LazyLoadedCollection($repository->reveal(), 'foo', [
            ['foo' => 1],
            ['foo' => 2],
            ['foo' => 3],
        ]);
        $this->assertSame($first, $collection->first());
    }

    public function testCount()
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
