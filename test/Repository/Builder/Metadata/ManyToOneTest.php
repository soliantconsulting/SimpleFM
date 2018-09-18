<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\ManyToOne;

final class ManyToOneTest extends TestCase
{
    public function testGenericGetters() : void
    {
        $metadata = new ManyToOne(
            'fieldName',
            'propertyName',
            'targetTable',
            'targetEntity',
            'targetPropertyName',
            'targetFieldName',
            'targetInterfaceName',
            true
        );
        $this->assertSame('fieldName', $metadata->getFieldName());
        $this->assertSame('propertyName', $metadata->getPropertyName());
        $this->assertSame('targetTable', $metadata->getTargetTable());
        $this->assertSame('targetEntity', $metadata->getTargetEntity());
        $this->assertSame('targetPropertyName', $metadata->getTargetPropertyName());
        $this->assertSame('targetFieldName', $metadata->getTargetFieldName());
        $this->assertSame('targetInterfaceName', $metadata->getTargetInterfaceName());
        $this->assertTrue($metadata->isReadOnly());
        $this->assertFalse($metadata->hasEagerHydration());
    }

    public function testSettingEagerHydration() : void
    {
        $metadata = new ManyToOne('', '', '', '', '', '', '', false, true);
        $this->assertTrue($metadata->hasEagerHydration());
    }
}
