<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\RelationException;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToOne;

final class OneToOneTest extends TestCase
{
    public function testGenericGetters() : void
    {
        $metadata = new OneToOne(
            'propertyName',
            'targetTable',
            'targetEntity',
            'targetFieldName',
            'targetInterfaceName',
            true,
            true,
            'fieldName',
            'targetPropertyName'
        );
        $this->assertSame('propertyName', $metadata->getPropertyName());
        $this->assertSame('targetTable', $metadata->getTargetTable());
        $this->assertSame('targetEntity', $metadata->getTargetEntity());
        $this->assertSame('targetInterfaceName', $metadata->getTargetInterfaceName());
        $this->assertTrue($metadata->isOwningSide());
        $this->assertTrue($metadata->isReadOnly());
        $this->assertSame('fieldName', $metadata->getFieldName());
        $this->assertSame('targetPropertyName', $metadata->getTargetPropertyName());
        $this->assertSame('targetFieldName', $metadata->getTargetFieldName());
        $this->assertFalse($metadata->hasEagerHydration());
    }

    public function testSettingEagerHydration() : void
    {
        $metadata = new OneToOne('', '', '', '', '', true, true, '', '', true);
        $this->assertTrue($metadata->hasEagerHydration());
    }

    public function testExceptionOnMissingProperties() : void
    {
        $this->expectException(RelationException::class);
        new OneToOne(
            'propertyName',
            'targetTable',
            'targetEntity',
            'targetFieldName',
            'targetInterfaceName',
            true,
            false
        );
    }

    public function testOptionalPropertiesAreSetToNullOnInverseSide() : void
    {
        $metadata = new OneToOne(
            'propertyName',
            'targetTable',
            'targetEntity',
            'targetFieldName',
            'targetInterfaceName',
            false,
            false,
            'foo',
            'bar'
        );

        $this->assertTrue($metadata->isReadOnly());

        $this->expectException(RelationException::class);
        $metadata->getTargetPropertyName();

        $this->expectException(RelationException::class);
        $metadata->getPropertyName();
    }
}
