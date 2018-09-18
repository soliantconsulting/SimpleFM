<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Query;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Query\Conditions;
use Soliant\SimpleFM\Query\Field;

final class ConditionsTest extends TestCase
{
    public function testConditionsCreation() : void
    {
        $fields = [new Field('foo', 'bar'), new Field('baz', 'bat')];
        $conditions = new Conditions(false, ...$fields);
        $this->assertFalse($conditions->isOmit());
        $this->assertSame($fields, $conditions->getFields());
    }

    public function testConditionsWithAdditionalFields() : void
    {
        $fields = [new Field('foo', 'bar'), new Field('baz', 'bat')];
        $conditions = new Conditions(false, ...$fields);
        $additionalField = new Field('a', 'b');
        $newConditions = $conditions->withAdditionalField($additionalField);

        $this->assertNotSame($newConditions, $conditions);
        $this->assertSame($fields, $conditions->getFields());
        $this->assertSame(array_merge($fields, [$additionalField]), $newConditions->getFields());
    }
}
