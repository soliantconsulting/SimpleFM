<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToOne;

final class OneToOneTest extends TestCase
{
    public function testGenericGetters()
    {
        $metadata = new OneToOne(
            'propertyName',
            'targetTable',
            'targetEntity',
            'targetFieldName',
            true,
            true,
            'fieldName',
            'targetPropertyName'
        );
        $this->assertSame('propertyName', $metadata->getPropertyName());
        $this->assertSame('targetTable', $metadata->getTargetTable());
        $this->assertSame('targetEntity', $metadata->getTargetEntity());
        $this->assertTrue($metadata->isOwningSide());
        $this->assertTrue($metadata->isReadOnly());
        $this->assertSame('fieldName', $metadata->getFieldName());
        $this->assertSame('targetPropertyName', $metadata->getTargetPropertyName());
        $this->assertSame('targetFieldName', $metadata->getTargetFieldName());
    }

    public function testExceptionOnMissingProperties()
    {
        $this->expectException(InvalidArgumentException::class);
        new OneToOne('propertyName', 'targetTable', 'targetEntity', 'targetFieldName', true, false);
    }

    public function testOptionalPropertiesAreSetToNullOnInverseSide()
    {
        $metadata = new OneToOne(
            'propertyName',
            'targetTable',
            'targetEntity',
            'targetFieldName',
            false,
            false,
            'foo',
            'bar'
        );

        $this->assertTrue($metadata->isReadOnly());

        $this->expectException(InvalidArgumentException::class);
        $metadata->getTargetPropertyName();

        $this->expectException(InvalidArgumentException::class);
        $metadata->getPropertyName();
    }
}
