<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\Field;
use Soliant\SimpleFM\Repository\Builder\Type\TypeInterface;

final class FieldTest extends TestCase
{
    public function testGenericGetters()
    {
        $type = $this->prophesize(TypeInterface::class)->reveal();

        $metadata = new Field('fieldName', 'propertyName', $type, true);
        $this->assertSame('fieldName', $metadata->getFieldName());
        $this->assertSame('propertyName', $metadata->getPropertyName());
        $this->assertSame($type, $metadata->getType());
        $this->assertTrue($metadata->isRepeatable());
    }
}
