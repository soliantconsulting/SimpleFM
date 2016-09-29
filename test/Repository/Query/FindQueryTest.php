<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Query;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Query\Exception\EmptyQueryException;
use Soliant\SimpleFM\Repository\Query\Exception\InvalidArgumentException;
use Soliant\SimpleFM\Repository\Query\FindQuery;
use Soliant\SimpleFM\Repository\Query\Query;

final class FindQueryTest extends TestCase
{
    public function testWithoutQueries()
    {
        $findQuery = new FindQuery();

        $this->expectException(EmptyQueryException::class);
        $findQuery->toParameters();
    }

    public function testAddNoOrQueries()
    {
        $findQuery = new FindQuery();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be empty');
        $findQuery->addOrQueries();
    }

    public function testAddNoAndQueries()
    {
        $findQuery = new FindQuery();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be empty');
        $findQuery->addAndQueries();
    }

    public function testAddMultipleAndQueries()
    {
        $findQuery = new FindQuery();
        $findQuery->addAndQueries(new Query('foo', 'bar'), new Query('baz', 'bat', true));
        $this->assertSame([
            '-query' => '(q1,!q2)',
            'q1' => 'foo',
            'q1.value' => 'bar',
            'q2' => 'baz',
            'q2.value' => 'bat',
        ], $findQuery->toParameters());
    }

    public function testAddMultipleOrQueries()
    {
        $findQuery = new FindQuery();
        $findQuery->addOrQueries(new Query('foo', 'bar'), new Query('baz', 'bat', true));
        $this->assertSame([
            '-query' => '(q1);(!q2)',
            'q1' => 'foo',
            'q1.value' => 'bar',
            'q2' => 'baz',
            'q2.value' => 'bat',
        ], $findQuery->toParameters());
    }
}
