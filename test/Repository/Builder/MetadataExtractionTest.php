<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\Field;
use Soliant\SimpleFM\Repository\Builder\Metadata\ManyToOne;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToMany;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToOne;
use Soliant\SimpleFM\Repository\Builder\MetadataExtraction;
use Soliant\SimpleFM\Repository\Builder\Type\StringType;

final class MetadataExtractionTest extends TestCase
{
    public function testSimpleFieldExtraction()
    {
        $entity = new class {
            private $baz = 'bat';
        };

        $entityMetadata = new Entity('foo', get_class($entity), [
            new Field('bar', 'baz', new StringType(), false)
        ], [], [], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bar' => 'bat'], $extraction->extract($entity));
    }

    public function testRepeatableFieldExtraction()
    {
        $entity = new class {
            private $baz = ['bat1', 'bat2'];
        };

        $entityMetadata = new Entity('foo', get_class($entity), [
            new Field('bar', 'baz', new StringType(), true)
        ], [], [], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bar' => ['bat1', 'bat2']], $extraction->extract($entity));
    }

    public function testRepeatableFieldExtractionWithoutArray()
    {
        $entity = new class {
            private $baz = 'bat';
        };

        $entityMetadata = new Entity('foo', get_class($entity), [
            new Field('bar', 'baz', new StringType(), true)
        ], [], [], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is not an array');
        $extraction->extract($entity);
    }

    public function testManyToOneOwningExtractionWithEntity()
    {
        $childEntity = new class {
            private $id = 5;
        };
        $entity = new class {
            public $baz;
        };
        $entity->baz = $childEntity;

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [
            new ManyToOne('bar', 'baz', get_class($childEntity), 'id')
        ], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bar' => 5], $extraction->extract($entity));
    }

    public function testManyToOneOwningExtractionWithoutEntity()
    {
        $childEntity = new class {
        };
        $entity = new class {
            public $baz = null;
        };

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [
            new ManyToOne('bar', 'baz', get_class($childEntity), 'id')
        ], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bar' => null], $extraction->extract($entity));
    }

    public function testOneToOneOwningExtractionWithEntity()
    {
        $childEntity = new class {
            private $id = 5;
        };
        $entity = new class {
            public $baz;
        };
        $entity->baz = $childEntity;

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [
            new OneToOne('bar', 'baz', get_class($childEntity), true, 'id')
        ]);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bar' => 5], $extraction->extract($entity));
    }

    public function testOneToOneOwningExtractionWithoutEntity()
    {
        $childEntity = new class {
        };
        $entity = new class {
            public $baz = null;
        };

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [
            new OneToOne('bar', 'baz', get_class($childEntity), true, 'id')
        ]);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bar' => null], $extraction->extract($entity));
    }

    public function testOneToOneInverseExtractionWithEntity()
    {
        $childEntity = new class {
            private $id = 5;
        };
        $entity = new class {
            public $baz;
        };
        $entity->baz = $childEntity;

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [
            new OneToOne('bar', 'baz', get_class($childEntity), false, 'id')
        ]);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame([], $extraction->extract($entity));
    }

    public function testOneToManyExtractionWithEntity()
    {
        $childEntity = new class {
            private $id = 5;
        };
        $entity = new class {
            public $baz;
        };
        $entity->baz = [$childEntity];

        $entityMetadata = new Entity('foo', get_class($entity), [], [
            new OneToMany('bar', 'baz', get_class($childEntity))
        ], [], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame([], $extraction->extract($entity));
    }
}
