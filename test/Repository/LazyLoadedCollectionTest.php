<?php
declare(strict_types=1);

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
                'q1' => 'record-id',
                'q1.value' => '1',
                'q2' => 'record-id',
                'q2.value' => '2',
                'q3' => 'record-id',
                'q3.value' => '3',
            ], $parameters[0]->toParameters());

            return [
                $first,
                $second,
                $third,
            ];
        });

        $collection = new LazyLoadedCollection($repository->reveal(), [1, 2, 3]);
        $entities = [];

        foreach ($collection as $entity) {
            $entities[] = $entity;
        }

        $this->assertSame($first, $entities[0]);
        $this->assertSame($second, $entities[1]);
        $this->assertSame($third, $entities[2]);
    }

    public function testFirst()
    {
        $first = new stdClass();
        $repository = $this->prophesize(RepositoryInterface::class);
        $repository->findByQuery(Argument::any())->will(function (array $parameters) use ($first) {
            return [
                $first,
                new stdClass(),
                new stdClass(),
            ];
        });

        $collection = new LazyLoadedCollection($repository->reveal(), [1, 2, 3]);
        $this->assertSame($first, $collection->first());
    }

    public function testCount()
    {
        $collection = new LazyLoadedCollection($this->prophesize(RepositoryInterface::class)->reveal(), [1, 2, 3]);
        $this->assertSame(3, count($collection));
    }
}
