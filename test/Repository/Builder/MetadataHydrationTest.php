<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\Field;
use Soliant\SimpleFM\Repository\Builder\Metadata\ManyToOne;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToMany;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToOne;
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
            new Field('bar', 'baz', new StringType(), false)
        ], [], [], []);

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
            new Field('bar', 'baz', new StringType(), true)
        ], [], [], []);

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
            new Field('bar', 'baz', new StringType(), true)
        ], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is not an array');
        $hydration->hydrateNewEntity(['bar' => 'bat']);
    }

    public function testManyToOneOwningHydrationWithChild()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [
            new ManyToOne('bar', 'baz', 'child', 'id')
        ], []);

        $repository = $this->prophesize(RepositoryInterface::class);
        $testCase = $this;
        $repository->findByQuery(Argument::any())->will(function (array $parameters) use ($testCase) : array {
            $testCase->assertSame('5', $parameters[0]->toParameters()['q1.value']);
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
        $entity = $hydration->hydrateNewEntity(['bar' => [5]]);
        $this->assertSame('child-entity', $entity->baz);
    }

    public function testManyToOneOwningHydrationWithoutEntity()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [
            new ManyToOne('bar', 'baz', 'child', 'id')
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

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [
            new OneToOne('bar', 'baz', 'child', true, 'id')
        ]);

        $repository = $this->prophesize(RepositoryInterface::class);
        $testCase = $this;
        $repository->findByQuery(Argument::any())->will(function (array $parameters) use ($testCase) : array {
            $testCase->assertSame('5', $parameters[0]->toParameters()['q1.value']);
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
        $entity = $hydration->hydrateNewEntity(['bar' => [5]]);
        $this->assertSame('child-entity', $entity->baz);
    }

    public function testOneToOneOwningHydrationWithoutEntity()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [
            new OneToOne('bar', 'baz', 'child', true, 'id')
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

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [
            new OneToOne('bar', 'baz', 'parent', false, 'id')
        ]);

        $repository = $this->prophesize(RepositoryInterface::class);
        $testCase = $this;
        $repository->findByQuery(Argument::any())->will(function (array $parameters) use ($testCase) : array {
            $testCase->assertSame('5', $parameters[0]->toParameters()['q1.value']);
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
        $entity = $hydration->hydrateNewEntity(['bar' => [5]]);
        $this->assertSame('parent-entity', $entity->baz);
    }

    public function testOneToOneInverseHydrationWithoutEntity()
    {
        $entityPrototype = new class {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [
            new OneToOne('bar', 'baz', 'parent', false, 'id')
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
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [
            new OneToMany('bar', 'baz', 'child')
        ], [], []);

        $repository = $this->prophesize(RepositoryInterface::class);
        $testCase = $this;
        $repository->findByQuery(Argument::any())->will(function (array $parameters) use ($testCase) : array {
            $testCase->assertSame('5', $parameters[0]->toParameters()['q1.value']);
            $testCase->assertSame('6', $parameters[0]->toParameters()['q2.value']);
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
        $entity = $hydration->hydrateNewEntity(['bar' => [5, 6]]);
        $this->assertSame(['child-entity-1', 'child-entity-2'], iterator_to_array($entity->baz));
    }
}
