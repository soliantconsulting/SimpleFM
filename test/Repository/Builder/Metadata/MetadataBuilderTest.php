<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Metadata;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\InvalidFileException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Exception\InvalidTypeException;
use Soliant\SimpleFM\Repository\Builder\Metadata\MetadataBuilder;
use Soliant\SimpleFM\Repository\Builder\Type\DateTimeType;
use Soliant\SimpleFM\Repository\Builder\Type\DecimalType;
use Soliant\SimpleFM\Repository\Builder\Type\FloatType;
use Soliant\SimpleFM\Repository\Builder\Type\IntegerType;
use Soliant\SimpleFM\Repository\Builder\Type\StringType;
use Soliant\SimpleFM\Repository\Builder\Type\TypeInterface;

final class MetadataBuilderTest extends TestCase
{
    public function testInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('not an instance of %s', TypeInterface::class));
        new MetadataBuilder('', ['foo']);
    }

    public function testNonExistentFile()
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage(sprintf(
            '"%s" for entity "Non\Existent" does not exist',
            __DIR__ . '/TestAssets/Non.Existent.xml'
        ));
        $builder->getMetadata('Non\Existent');
    }

    public function testNonXmlFile()
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage(sprintf(
            '"%s" is not valid',
            __DIR__ . '/TestAssets/Non.Xml.xml'
        ));
        $builder->getMetadata('Non\Xml');
    }

    public function testInvalidXmlFile()
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');

        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage(sprintf(
            '"%s" is not valid',
            __DIR__ . '/TestAssets/Invalid.Xml.xml'
        ));
        $builder->getMetadata('Invalid\Xml');
    }

    public function testEmptyEntity()
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('Empty');
        $this->assertSame('Empty', $metadata->getClassName());
        $this->assertSame('empty-layout', $metadata->getLayout());
    }

    public function testBuiltInTypes()
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('BuiltInTypes');
        $fieldTypes = [];

        foreach ($metadata->getFields() as $field) {
            $fieldTypes[$field->getFieldName()] = $field->getType();
        }

        $this->assertInstanceOf(DateTimeType::class, $fieldTypes['date-time']);
        $this->assertInstanceOf(DecimalType::class, $fieldTypes['decimal']);
        $this->assertInstanceOf(FloatType::class, $fieldTypes['float']);
        $this->assertInstanceOf(IntegerType::class, $fieldTypes['integer']);
        $this->assertInstanceOf(StringType::class, $fieldTypes['string']);
    }

    public function testCustomType()
    {
        $customType = $this->prophesize(TypeInterface::class)->reveal();

        $builder = new MetadataBuilder(__DIR__ . '/TestAssets', ['custom-type' => $customType]);
        $metadata = $builder->getMetadata('CustomType');

        $this->assertSame($customType, $metadata->getFields()[0]->getType());
    }

    public function testNonExistentType()
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');

        $this->expectException(InvalidTypeException::class);
        $builder->getMetadata('CustomType');
    }

    public function testRepeatable()
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('Repeatable');

        $this->assertFalse($metadata->getFields()[0]->isRepeatable());
        $this->assertTrue($metadata->getFields()[1]->isRepeatable());
        $this->assertFalse($metadata->getFields()[2]->isRepeatable());
    }

    public function testOneToMany()
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('OneToMany');
        $relation = $metadata->getOneToMany()[0];

        $this->assertSame('foo', $relation->getFieldName());
        $this->assertSame('bar', $relation->getPropertyName());
        $this->assertSame('baz', $relation->getTargetEntity());
    }

    public function testManyToOne()
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('ManyToOne');
        $relation = $metadata->getManyToOne()[0];

        $this->assertSame('foo', $relation->getFieldName());
        $this->assertSame('bar', $relation->getPropertyName());
        $this->assertSame('baz', $relation->getTargetEntity());
        $this->assertSame('bat', $relation->getTargetPropertyName());
    }

    public function testOneToOneOwning()
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('OneToOneOwning');
        $relation = $metadata->getOneToOne()[0];

        $this->assertSame('foo', $relation->getFieldName());
        $this->assertSame('bar', $relation->getPropertyName());
        $this->assertSame('baz', $relation->getTargetEntity());
        $this->assertTrue($relation->isOwningSide());
        $this->assertSame('bat', $relation->getTargetPropertyName());
    }

    public function testOneToOneInverse()
    {
        $builder = new MetadataBuilder(__DIR__ . '/TestAssets');
        $metadata = $builder->getMetadata('OneToOneInverse');
        $relation = $metadata->getOneToOne()[0];

        $this->assertSame('foo', $relation->getFieldName());
        $this->assertSame('bar', $relation->getPropertyName());
        $this->assertSame('baz', $relation->getTargetEntity());
        $this->assertFalse($relation->isOwningSide());
    }
}
