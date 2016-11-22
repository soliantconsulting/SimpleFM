<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder;

use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Exception\ExtractionException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Embeddable;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\Field;
use Soliant\SimpleFM\Repository\Builder\Metadata\ManyToOne;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToMany;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToOne;
use Soliant\SimpleFM\Repository\Builder\MetadataExtraction;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyInterface;
use Soliant\SimpleFM\Repository\Builder\Type\StringType;
use SoliantTest\SimpleFM\Repository\Builder\TestAssets\EmptyEntityInterface;
use SoliantTest\SimpleFM\Repository\Builder\TestAssets\EmptyProxyEntityInterface;

final class MetadataExtractionTest extends TestCase
{
    public function testSimpleFieldExtraction()
    {
        $entity = new class
        {
            private $baz = 'bat';
        };

        $entityMetadata = new Entity('foo', get_class($entity), [
            new Field('bar', 'baz', new StringType(), false, false),
        ], [], [], [], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bar' => 'bat'], $extraction->extract($entity));
    }

    public function testProxyExtraction()
    {
        $entity = new class
        {
            private $baz = 'bat';
        };

        $proxyEntity = new class($entity, 1) implements ProxyInterface, EmptyEntityInterface
        {
            private $entity;

            private $relationId;

            public function __construct($entity, $relationId)
            {
                $this->entity = $entity;
                $this->relationId = $relationId;
            }

            public function __getRealEntity()
            {
                return $this->entity;
            }

            public function __getRelationId()
            {
                return $this->relationId;
            }
        };

        $entityMetadata = new Entity('foo', get_class($entity), [
            new Field('bar', 'baz', new StringType(), false, false),
        ], [], [], [], [], null, EmptyEntityInterface::class);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bar' => 'bat'], $extraction->extract($proxyEntity));
    }

    public function testRepeatableFieldExtraction()
    {
        $entity = new class
        {
            private $baz = ['bat1', 'bat2'];
        };

        $entityMetadata = new Entity('foo', get_class($entity), [
            new Field('bar', 'baz', new StringType(), true, false),
        ], [], [], [], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bar(1)' => 'bat1', 'bar(2)' => 'bat2'], $extraction->extract($entity));
    }

    public function testReadOnlyFieldExtraction()
    {
        $entity = new class
        {
            private $baz = 'bat';
        };

        $entityMetadata = new Entity('foo', get_class($entity), [
            new Field('bar', 'baz', new StringType(), false, true),
        ], [], [], [], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame([], $extraction->extract($entity));
    }

    public function testRepeatableFieldExtractionWithoutArray()
    {
        $entity = new class
        {
            private $baz = 'bat';
        };

        $entityMetadata = new Entity('foo', get_class($entity), [
            new Field('bar', 'baz', new StringType(), true, false),
        ], [], [], [], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->expectException(ExtractionException::class);
        $this->expectExceptionMessage('is not an array');
        $extraction->extract($entity);
    }

    public function testEmbeddableExtraction()
    {
        $embeddable = new class
        {
            private $foo = 'bar';
        };
        $entity = new class
        {
            public $baz;
        };
        $entity->baz = $embeddable;

        $entityMetadata = new Entity('foo', get_class($entity), [], [
            new Embeddable('baz', 'bazPrefix', new Entity('', get_class($embeddable), [
                new Field('fooField', 'foo', new StringType(), false, false),
            ], [], [], [], [])),
        ], [], [], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bazPrefixfooField' => 'bar'], $extraction->extract($entity));
    }

    public function testManyToOneExtractionWithEntity()
    {
        $childEntity = new class implements EmptyProxyEntityInterface
        {
            private $id = 5;
        };
        $entity = new class
        {
            public $baz;
        };
        $entity->baz = $childEntity;

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [
            new ManyToOne(
                'bat',
                'baz',
                'bar',
                get_class($childEntity),
                'id',
                'ID',
                EmptyProxyEntityInterface::class,
                false
            ),
        ], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bat' => 5], $extraction->extract($entity));
    }

    public function testManyToOneExtractionWithProxyEntity()
    {
        $childEntity = new class implements EmptyProxyEntityInterface
        {
            private $id = 5;
        };
        $entity = new class
        {
            public $baz;
        };
        $entity->baz = $this->createMockProxy($childEntity, 5);

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [
            new ManyToOne(
                'bat',
                'baz',
                'bar',
                get_class($childEntity),
                'id',
                'ID',
                EmptyProxyEntityInterface::class,
                false
            ),
        ], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bat' => 5], $extraction->extract($entity));
    }

    public function testManyToOneReadOnlyExtractionWithEntity()
    {
        $childEntity = new class implements EmptyProxyEntityInterface
        {
            private $id = 5;
        };
        $entity = new class
        {
            public $baz;
        };
        $entity->baz = $childEntity;

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [
            new ManyToOne(
                'bat',
                'baz',
                'bar',
                get_class($childEntity),
                'id',
                'ID',
                EmptyProxyEntityInterface::class,
                true
            ),
        ], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame([], $extraction->extract($entity));
    }

    public function testManyToOneExtractionWithoutEntity()
    {
        $childEntity = new class implements EmptyProxyEntityInterface
        {
        };
        $entity = new class
        {
            public $baz = null;
        };

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [
            new ManyToOne(
                'bat',
                'baz',
                'bar',
                get_class($childEntity),
                'id',
                'ID',
                EmptyProxyEntityInterface::class,
                false
            ),
        ], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bat' => null], $extraction->extract($entity));
    }

    public function testOneToOneOwningExtractionWithEntity()
    {
        $childEntity = new class implements EmptyProxyEntityInterface
        {
            private $id = 5;
        };
        $entity = new class
        {
            public $baz;
        };
        $entity->baz = $childEntity;

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [], [
            new OneToOne(
                'baz',
                'bar',
                get_class($childEntity),
                'ID',
                EmptyProxyEntityInterface::class,
                true,
                false,
                'bat',
                'id'
            ),
        ]);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bat' => 5], $extraction->extract($entity));
    }

    public function testOneToOneOwningExtractionWithProxyEntity()
    {
        $childEntity = new class implements EmptyProxyEntityInterface
        {
            private $id = 5;
        };
        $entity = new class
        {
            public $baz;
        };
        $entity->baz = $this->createMockProxy($childEntity, 5);

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [], [
            new OneToOne(
                'baz',
                'bar',
                get_class($childEntity),
                'ID',
                EmptyProxyEntityInterface::class,
                true,
                false,
                'bat',
                'id'
            ),
        ]);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bat' => 5], $extraction->extract($entity));
    }

    public function testOneToOneOwningReadOnlyExtractionWithEntity()
    {
        $childEntity = new class implements EmptyProxyEntityInterface
        {
            private $id = 5;
        };
        $entity = new class
        {
            public $baz;
        };
        $entity->baz = $childEntity;

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [], [
            new OneToOne(
                'baz',
                'bar',
                get_class($childEntity),
                'ID',
                EmptyProxyEntityInterface::class,
                true,
                true,
                'bat',
                'id'
            ),
        ]);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame([], $extraction->extract($entity));
    }

    public function testOneToOneOwningExtractionWithoutEntity()
    {
        $childEntity = new class implements EmptyProxyEntityInterface
        {
        };
        $entity = new class
        {
            public $baz = null;
        };

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [], [
            new OneToOne(
                'baz',
                'bar',
                get_class($childEntity),
                'ID',
                EmptyProxyEntityInterface::class,
                true,
                false,
                'bat',
                'id'
            ),
        ]);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame(['bat' => null], $extraction->extract($entity));
    }

    public function testOneToOneInverseExtractionWithEntity()
    {
        $childEntity = new class implements EmptyProxyEntityInterface
        {
            private $id = 5;
        };
        $entity = new class
        {
            public $baz;
        };
        $entity->baz = $childEntity;

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [], [], [
            new OneToOne(
                'baz',
                'bar',
                get_class($childEntity),
                'ID',
                EmptyProxyEntityInterface::class,
                false,
                false,
                'bat',
                'id'
            ),
        ]);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame([], $extraction->extract($entity));
    }

    public function testOneToManyExtractionWithEntity()
    {
        $childEntity = new class
        {
            private $id = 5;
        };
        $entity = new class
        {
            public $baz;
        };
        $entity->baz = [$childEntity];

        $entityMetadata = new Entity('foo', get_class($entity), [], [], [
            new OneToMany('bar', 'baz', get_class($childEntity), 'ID'),
        ], [], []);

        $extraction = new MetadataExtraction($entityMetadata);
        $this->assertSame([], $extraction->extract($entity));
    }

    private function createMockProxy($entity, $relationId) : ProxyInterface
    {
        return new class($entity, $relationId) implements ProxyInterface
        {
            private $entity;

            private $relationId;

            public function __construct($entity, $relationId)
            {
                $this->entity = $entity;
                $this->relationId = $relationId;
            }

            public function __getRealEntity()
            {
                return $this->entity;
            }

            public function __getRelationId()
            {
                return $this->relationId;
            }
        };
    }
}
