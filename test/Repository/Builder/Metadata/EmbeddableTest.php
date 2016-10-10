<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\Embeddable;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;

final class EmbeddableTest extends TestCase
{
    public function testGenericGetters()
    {
        $entityMetadata = new Entity('', '', [], [], [], [], []);

        $metadata = new Embeddable('propertyName', 'fieldNamePrefix', $entityMetadata);
        $this->assertSame('propertyName', $metadata->getPropertyName());
        $this->assertSame('fieldNamePrefix', $metadata->getFieldNamePrefix());
        $this->assertSame($entityMetadata, $metadata->getMetadata());
    }
}
