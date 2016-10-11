<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Soliant\SimpleFM\Repository\Builder\Exception\HydrationException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Embeddable;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\Field;
use Soliant\SimpleFM\Repository\Builder\Metadata\ManyToOne;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToMany;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToOne;
use Soliant\SimpleFM\Repository\Builder\Metadata\RecordId;
use Soliant\SimpleFM\Repository\Builder\MetadataHydration;
use Soliant\SimpleFM\Repository\Builder\RepositoryBuilderInterface;
use Soliant\SimpleFM\Repository\Builder\Type\StringType;
use Soliant\SimpleFM\Repository\Query\FindQuery;
use Soliant\SimpleFM\Repository\RepositoryInterface;

final class MetadataHydrationTest extends TestCase
{
    public function testSimpleFieldHydration()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [
            new Field('bar', 'baz', new StringType(), false, false),
        ], [], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => 'bat']);
        $this->assertSame('bat', $entity->baz);
    }

    public function testRepeatableFieldHydration()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [
            new Field('bar', 'baz', new StringType(), true, false),
        ], [], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => ['bat1', 'bat2']]);
        $this->assertSame(['bat1', 'bat2'], $entity->baz);
    }

    public function testRepeatableFieldHydrationWithoutArray()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [
            new Field('bar', 'baz', new StringType(), true, false),
        ], [], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('is not an array');
        $hydration->hydrateNewEntity(['bar' => 'bat']);
    }

    public function testReadOnlyFieldHydration()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [
            new Field('bar', 'baz', new StringType(), false, true),
        ], [], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => 'bat']);
        $this->assertSame('bat', $entity->baz);
    }

    public function testRecordIdHydration()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [], [], new RecordId('baz'));

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['record-id' => 1]);
        $this->assertSame(1, $entity->baz);
    }

    public function testEmbeddableHydrationWithoutPrefix()
    {
        $embeddablePrototype = new class {
            public $foo;
        };
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [
            new Embeddable('baz', '', new Entity('', get_class($embeddablePrototype), [
                new Field('fooField', 'foo', new StringType(), false, false),
            ], [], [], [], [])),
        ], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['fooField' => 'bar']);
        $this->assertInstanceOf(get_class($embeddablePrototype), $entity->baz);
        $this->assertSame('bar', $entity->baz->foo);
    }

    public function testEmbeddableHydrationWithPrefix()
    {
        $embeddablePrototype = new class {
            public $foo;
        };
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [
            new Embeddable('baz', 'bazPrefix', new Entity('', get_class($embeddablePrototype), [
                new Field('fooField', 'foo', new StringType(), false, false),
            ], [], [], [], [])),
        ], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bazPrefixfooField' => 'bar', 'fooField' => 'bat']);
        $this->assertInstanceOf(get_class($embeddablePrototype), $entity->baz);
        $this->assertSame('bar', $entity->baz->foo);
    }

    public function testManyToOneHydrationWithChild()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [
            new ManyToOne('bat', 'baz', 'bar', 'child', 'id', 'ID', false),
        ], []);

        $repository = $this->prophesize(RepositoryInterface::class);
        $testCase = $this;
        $repository->findByQuery(Argument::any())->will(function (array $parameters) use ($testCase) : array {
            $testCase->assertSame('5', $parameters[0]->toParameters()['-q1.value']);
            return ['child-entity'];
        });

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('child')->willReturn(
            $repository->reveal()
        );

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => [['ID' => 5]]]);
        $this->assertSame('child-entity', $entity->baz);
    }

    public function testManyToOneHydrationWithoutEntity()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [
            new ManyToOne('bat', 'baz', 'bar', 'child', 'id', 'ID', false),
        ], []);

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('child')->willReturn(
            $this->prophesize(RepositoryInterface::class)->reveal()
        );

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => []]);
        $this->assertNull($entity->baz);
    }

    public function testOneToOneOwningHydrationWithEntity()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [], [
            new OneToOne('baz', 'bar', 'child', 'ID', true, false, 'id', 'child-id'),
        ]);

        $repository = $this->prophesize(RepositoryInterface::class);
        $testCase = $this;
        $repository->findByQuery(Argument::any())->will(function (array $parameters) use ($testCase) : array {
            $testCase->assertSame('5', $parameters[0]->toParameters()['-q1.value']);
            return ['child-entity'];
        });

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('child')->willReturn(
            $repository->reveal()
        );

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => [['ID' => 5]]]);
        $this->assertSame('child-entity', $entity->baz);
    }

    public function testOneToOneOwningHydrationWithoutEntity()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [], [
            new OneToOne('baz', 'bar', 'child', 'ID', true, false, 'id', 'child-id'),
        ]);

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('child')->willReturn(
            $this->prophesize(RepositoryInterface::class)->reveal()
        );

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => []]);
        $this->assertNull($entity->baz);
    }

    public function testOneToOneInverseHydrationWithEntity()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [], [
            new OneToOne('baz', 'bar', 'parent', 'ID', false, false),
        ]);

        $repository = $this->prophesize(RepositoryInterface::class);
        $testCase = $this;
        $repository->findByQuery(Argument::any())->will(function (array $parameters) use ($testCase) : array {
            $testCase->assertSame('5', $parameters[0]->toParameters()['-q1.value']);
            return ['parent-entity'];
        });

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('parent')->willReturn(
            $repository->reveal()
        );

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => [['ID' => 5]]]);
        $this->assertSame('parent-entity', $entity->baz);
    }

    public function testOneToOneInverseHydrationWithoutEntity()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [], [
            new OneToOne('baz', 'bar', 'parent', 'ID', false, false),
        ]);

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('parent')->willReturn(
            $this->prophesize(RepositoryInterface::class)->reveal()
        );

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => []]);
        $this->assertNull($entity->baz);
    }

    public function testOneToManyHydrationWithEntity()
    {
        $entityPrototype = new class {
            public $bar;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [
            new OneToMany('bar', 'baz', 'child', 'ID'),
        ], [], []);

        $repository = $this->prophesize(RepositoryInterface::class);
        $testCase = $this;
        $repository->findByQuery(Argument::any())->will(function (array $parameters) use ($testCase) : array {
            $testCase->assertSame('5', $parameters[0]->toParameters()['-q1.value']);
            $testCase->assertSame('6', $parameters[0]->toParameters()['-q2.value']);
            return ['child-entity-1', 'child-entity-2'];
        });

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('child')->willReturn(
            $repository->reveal()
        );

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['baz' => [['ID' => 5], ['ID' => 6]]]);
        $this->assertSame(['child-entity-1', 'child-entity-2'], iterator_to_array($entity->bar));
    }
}
