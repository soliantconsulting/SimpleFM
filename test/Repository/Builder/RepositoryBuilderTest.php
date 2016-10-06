<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Client\ResultSet\ResultSetClientInterface;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\MetadataBuilderInterface;
use Soliant\SimpleFM\Repository\Builder\MetadataExtraction;
use Soliant\SimpleFM\Repository\Builder\MetadataHydration;
use Soliant\SimpleFM\Repository\Builder\RepositoryBuilder;

final class RepositoryBuilderTest extends TestCase
{
    public function testRepositoryCaching()
    {
        $metadataBuilder = $this->prophesize(MetadataBuilderInterface::class);
        $metadataBuilder->getMetadata('foo')->willReturn(new Entity('bar', 'foo', [], [], [], []));

        $builder = new RepositoryBuilder(
            $this->prophesize(ResultSetClientInterface::class)->reveal(),
            $metadataBuilder->reveal()
        );

        $this->assertSame($builder->buildRepository('foo'), $builder->buildRepository('foo'));
    }

    public function testMetadataInjection()
    {
        $metadata = new Entity('bar', 'foo', [], [], [], []);

        $metadataBuilder = $this->prophesize(MetadataBuilderInterface::class);
        $metadataBuilder->getMetadata('foo')->willReturn($metadata);

        $builder = new RepositoryBuilder(
            $this->prophesize(ResultSetClientInterface::class)->reveal(),
            $metadataBuilder->reveal()
        );

        $repository = $builder->buildRepository('foo');
        $this->assertAttributeSame('bar', 'layout', $repository);

        $hydration = self::getObjectAttribute($repository, 'hydration');
        $this->assertInstanceOf(MetadataHydration::class, $hydration);
        $this->assertAttributeSame($builder, 'repositoryBuilder', $hydration);
        $this->assertAttributeSame($metadata, 'entityMetadata', $hydration);

        $extraction = self::getObjectAttribute($repository, 'extraction');
        $this->assertInstanceOf(MetadataExtraction::class, $extraction);
        $this->assertAttributeSame($metadata, 'entityMetadata', $extraction);
    }
}
