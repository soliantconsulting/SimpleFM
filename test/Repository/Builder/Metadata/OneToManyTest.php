<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToMany;

final class OneToManyTest extends TestCase
{
    public function testGenericGetters()
    {
        $metadata = new OneToMany('fieldName', 'propertyName', 'targetEntity');
        $this->assertSame('fieldName', $metadata->getFieldName());
        $this->assertSame('propertyName', $metadata->getPropertyName());
        $this->assertSame('targetEntity', $metadata->getTargetEntity());
    }
}
