<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\ManyToOne;

final class ManyToOneTest extends TestCase
{
    public function testGenericGetters()
    {
        $metadata = new ManyToOne('fieldName', 'propertyName', 'targetEntity', 'targetPropertyName');
        $this->assertSame('fieldName', $metadata->getFieldName());
        $this->assertSame('propertyName', $metadata->getPropertyName());
        $this->assertSame('targetEntity', $metadata->getTargetEntity());
        $this->assertSame('targetPropertyName', $metadata->getTargetPropertyName());
    }
}
