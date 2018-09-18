<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\InvalidCollectionException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\InvalidFileException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\InvalidTypeException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\MissingInterfaceException;
use Soliant\SimpleFM\Repository\Builder\Metadata\MetadataBuilder;
use Soliant\SimpleFM\Repository\Builder\Type;
use Soliant\SimpleFM\Repository\Builder\Type\TypeInterface;

final class MetadataBuilderTest extends TestCase
{
    public function testInvalidType() : void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->expectExceptionMessage(sprintf('not an instance of %s', TypeInterface::class));
        new MetadataBuilder('', ['foo']);
    }

    public function testNonExistentFile() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage(sprintf(
            '"%s" for entity "Non\Existent" does not exist',
            __DIR__ . '/TestAssets/Non.Existent.xml'
        ));
        $builder->getMetadata('Non\Existent');
    }

    public function testNonXmlFile() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage(sprintf(
            '"%s" is not valid',
            __DIR__ . '/TestAssets/Non.Xml.xml'
        ));
        $builder->getMetadata('Non\Xml');
    }

    public function testInvalidXmlFile() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage(sprintf(
            '"%s" is not valid',
            __DIR__ . '/TestAssets/Invalid.Xml.xml'
        ));
        $builder->getMetadata('Invalid\Xml');
    }

    public function testEmptyEntity() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('Empty');
        $this->assertSame('Empty', $metadata->getClassName());
        $this->assertSame('empty-layout', $metadata->getLayout());
        $this->assertFalse($metadata->hasInterfaceName());
    }

    public function testOptionalInterfaceName() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('InterfaceName');
        $this->assertTrue($metadata->hasInterfaceName());
        $this->assertSame('foo', $metadata->getInterfaceName());
    }

    public function testInternalMetadataCaching() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $this->assertSame($builder->getMetadata('Empty'), $builder->getMetadata('Empty'));
    }

    public function testExternalMetadataCachingWithHit() : void
    {
        $cachedMetadata = new Entity('', '', [], [], [], [], []);

        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->get()->willReturn($cachedMetadata);

        $cache = $this->prophesize(CacheItemPoolInterface::class);
        $cache->hasItem('simplefm.metadata.ce2c8aed9c2fa0cfbed56cbda4d8bf07')->willReturn(true)->shouldBeCalledTimes(1);
        $cache->getItem('simplefm.metadata.ce2c8aed9c2fa0cfbed56cbda4d8bf07')->willReturn(
            $cacheItem->reveal()
        )->shouldBeCalledTimes(1);
        $cache->save(Argument::any())->shouldNotBeCalled();

        $builder = new MetadataBuilder(__DIR__ . '/TestAssets', [], $cache->reveal());
        $retrievedMetadata = $builder->getMetadata('Empty');
        $this->assertSame($cachedMetadata, $retrievedMetadata);
        $this->assertSame($retrievedMetadata, $builder->getMetadata('Empty'));
    }

    public function testExternalMetadataCachingWithoutHit() : void
    {
        $cache = $this->prophesize(CacheItemPoolInterface::class);
        $cache->hasItem('simplefm.metadata.ce2c8aed9c2fa0cfbed56cbda4d8bf07')->willReturn(false);
        $cache->save(Argument::that(function (CacheItemInterface $cacheItem) {
            return (
                'simplefm.metadata.ce2c8aed9c2fa0cfbed56cbda4d8bf07' === $cacheItem->getKey()
                && $cacheItem->get() instanceof Entity
            );
        }))->shouldBeCalledTimes(1);

        $builder = new MetadataBuilder(__DIR__ . '/TestAssets', [], $cache->reveal());
        $builder->getMetadata('Empty');
    }

    public function testBuiltInTypes() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('BuiltInTypes');
        $fieldTypes = [];

        foreach ($metadata->getFields() as $field) {
            $fieldTypes[$field->getFieldName()] = $field->getType();
        }

        $this->assertInstanceOf(Type\BooleanType::class, $fieldTypes['boolean']);
        $this->assertInstanceOf(Type\DateTimeType::class, $fieldTypes['date-time']);
        $this->assertInstanceOf(Type\DateType::class, $fieldTypes['date']);
        $this->assertInstanceOf(Type\NumberType::class, $fieldTypes['float']);
        $this->assertAttributeSame(false, 'limitToInt', $fieldTypes['float']);
        $this->assertInstanceOf(Type\NumberType::class, $fieldTypes['integer']);
        $this->assertAttributeSame(true, 'limitToInt', $fieldTypes['integer']);
        $this->assertInstanceOf(Type\NullableStringType::class, $fieldTypes['nullable-string']);
        $this->assertInstanceOf(Type\StreamType::class, $fieldTypes['stream']);
        $this->assertInstanceOf(Type\StringType::class, $fieldTypes['string']);
        $this->assertInstanceOf(Type\TimeType::class, $fieldTypes['time']);
    }

    public function testCustomType() : void
    {
        $customType = $this->prophesize(TypeInterface::class)->reveal();

        $builder = new MetadataBuilder(__DIR__ . '/TestAssets', ['custom-type' => $customType]);
        $metadata = $builder->getMetadata('CustomType');

        $this->assertSame($customType, $metadata->getFields()[0]->getType());
    }

    public function testNonExistentType() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');

        $this->expectException(InvalidTypeException::class);
        $builder->getMetadata('CustomType');
    }

    public function testRepeatable() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('Repeatable');

        $this->assertFalse($metadata->getFields()[0]->isRepeatable());
        $this->assertTrue($metadata->getFields()[1]->isRepeatable());
        $this->assertFalse($metadata->getFields()[2]->isRepeatable());
    }

    public function testReadOnlyFields() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('ReadOnly');

        $this->assertFalse($metadata->getFields()[0]->isReadOnly());
        $this->assertTrue($metadata->getFields()[1]->isReadOnly());
        $this->assertFalse($metadata->getFields()[2]->isReadOnly());
    }

    public function testReadOnlyManyToOne() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('ReadOnly');

        $this->assertFalse($metadata->getManyToOne()[0]->isReadOnly());
        $this->assertTrue($metadata->getManyToOne()[1]->isReadOnly());
        $this->assertFalse($metadata->getManyToOne()[2]->isReadOnly());
    }

    public function testReadOnlyOneToOne() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('ReadOnly');

        $this->assertFalse($metadata->getOneToOne()[0]->isReadOnly());
        $this->assertTrue($metadata->getOneToOne()[1]->isReadOnly());
        $this->assertFalse($metadata->getOneToOne()[2]->isReadOnly());
    }

    public function testEagerHydrationOneToMany() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('EagerHydration');

        $this->assertFalse($metadata->getOneToOne()[0]->hasEagerHydration());
        $this->assertTrue($metadata->getOneToOne()[1]->hasEagerHydration());
        $this->assertFalse($metadata->getOneToOne()[2]->hasEagerHydration());
    }

    public function testEagerHydrationManyToOne() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('EagerHydration');

        $this->assertFalse($metadata->getManyToOne()[0]->hasEagerHydration());
        $this->assertTrue($metadata->getManyToOne()[1]->hasEagerHydration());
        $this->assertFalse($metadata->getManyToOne()[2]->hasEagerHydration());
    }

    public function testEagerHydrationOneToOneOwning() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('EagerHydration');

        $this->assertFalse($metadata->getOneToOne()[0]->hasEagerHydration());
        $this->assertTrue($metadata->getOneToOne()[1]->hasEagerHydration());
        $this->assertFalse($metadata->getOneToOne()[2]->hasEagerHydration());
    }

    public function testEagerHydrationOneToOneInverse() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('EagerHydration');

        $this->assertFalse($metadata->getOneToOne()[3]->hasEagerHydration());
        $this->assertTrue($metadata->getOneToOne()[4]->hasEagerHydration());
        $this->assertFalse($metadata->getOneToOne()[5]->hasEagerHydration());
    }

    public function testRecordId() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('RecordId');

        $this->assertSame('recordId', $metadata->getRecordId()->getPropertyName());
    }

    public function testEmbeddable() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('Embeddable');
        $embeddable = $metadata->getEmbeddables()[0];

        $this->assertSame('foo', $embeddable->getPropertyName());
        $this->assertSame('bar', $embeddable->getFieldNamePrefix());
        $this->assertSame('EmbeddedEntity', $embeddable->getMetadata()->getClassName());
    }

    public function testOneToMany() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('OneToMany');
        $relation = $metadata->getOneToMany()[0];

        $this->assertSame('bar', $relation->getPropertyName());
        $this->assertSame('baz', $relation->getTargetTable());
        $this->assertSame('bat', $relation->getTargetEntity());
        $this->assertSame('bau', $relation->getTargetFieldName());
    }

    public function testManyToOne() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('ManyToOne');
        $relation = $metadata->getManyToOne()[0];

        $this->assertSame('foo', $relation->getFieldName());
        $this->assertSame('bar', $relation->getPropertyName());
        $this->assertSame('baz', $relation->getTargetTable());
        $this->assertSame('RelationTarget', $relation->getTargetEntity());
        $this->assertSame('bau', $relation->getTargetPropertyName());
        $this->assertSame('bai', $relation->getTargetFieldName());
        $this->assertSame('RelationTargetInterface', $relation->getTargetInterfaceName());
    }

    public function testManyToOneWithoutInterface() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');

        $this->expectException(MissingInterfaceException::class);
        $this->expectExceptionMessage('entity "ManyToOneWithoutInterface" to entity "RelationTargetWithoutInterface"');
        $builder->getMetadata('ManyToOneWithoutInterface');
    }

    public function testOneToOneOwning() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('OneToOneOwning');
        $relation = $metadata->getOneToOne()[0];

        $this->assertSame('foo', $relation->getFieldName());
        $this->assertSame('bar', $relation->getPropertyName());
        $this->assertSame('baz', $relation->getTargetTable());
        $this->assertSame('RelationTarget', $relation->getTargetEntity());
        $this->assertSame('bai', $relation->getTargetFieldName());
        $this->assertSame('RelationTargetInterface', $relation->getTargetInterfaceName());
        $this->assertTrue($relation->isOwningSide());
        $this->assertSame('bau', $relation->getTargetPropertyName());
    }

    public function testOneToOneInverse() : void
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('OneToOneInverse');
        $relation = $metadata->getOneToOne()[0];

        $this->assertSame('bar', $relation->getPropertyName());
        $this->assertSame('baz', $relation->getTargetTable());
        $this->assertSame('RelationTarget', $relation->getTargetEntity());
        $this->assertSame('bau', $relation->getTargetFieldName());
        $this->assertSame('RelationTargetInterface', $relation->getTargetInterfaceName());
        $this->assertFalse($relation->isOwningSide());
    }
}
