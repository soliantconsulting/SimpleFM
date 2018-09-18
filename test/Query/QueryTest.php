<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Query;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Query\Conditions;
use Soliant\SimpleFM\Query\Field;
use Soliant\SimpleFM\Query\Query;

final class QueryTest extends TestCase
{
    public function testQueryCreation() : void
    {
        $conditions = [
            new Conditions(false, new Field('foo', 'bar')),
            new Conditions(true, new Field('foo', 'bar')),
        ];
        $query = new Query(...$conditions);
        $this->assertSame($conditions, $query->getOrConditions());
    }

    public function testQueryWithAdditionalConditions() : void
    {
        $conditions = [
            new Conditions(false, new Field('foo', 'bar')),
            new Conditions(true, new Field('foo', 'bar')),
        ];
        $query = new Query(...$conditions);
        $additionalConditions = new Conditions(false, new Field('baz', 'bat'));
        $newQuery = $query->orWith($additionalConditions);

        $this->assertNotSame($newQuery, $query);
        $this->assertSame($conditions, $query->getOrConditions());
        $this->assertSame(array_merge($conditions, [$additionalConditions]), $newQuery->getOrConditions());
    }
}
