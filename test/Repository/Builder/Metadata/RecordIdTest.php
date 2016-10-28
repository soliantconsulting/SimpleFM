<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\RecordId;

final class RecordIdTest extends TestCase
{
    public function testGenericGetters()
    {
        $metadata = new RecordId('propertyName');
        $this->assertSame('propertyName', $metadata->getPropertyName());
    }
}
