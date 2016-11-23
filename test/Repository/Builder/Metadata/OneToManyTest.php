<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToMany;

final class OneToManyTest extends TestCase
{
    public function testGenericGetters()
    {
        $metadata = new OneToMany('propertyName', 'targetTable', 'targetEntity', 'targetFieldName');
        $this->assertSame('propertyName', $metadata->getPropertyName());
        $this->assertSame('targetTable', $metadata->getTargetTable());
        $this->assertSame('targetEntity', $metadata->getTargetEntity());
        $this->assertSame('targetFieldName', $metadata->getTargetFieldName());
        $this->assertFalse($metadata->hasEagerHydration());
    }

    public function testSettingEagerHydration()
    {
        $metadata = new OneToMany('', '', '', '', true);
        $this->assertTrue($metadata->hasEagerHydration());
    }
}
