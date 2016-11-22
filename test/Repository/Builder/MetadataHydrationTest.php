<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Soliant\SimpleFM\Collection\ItemCollection;
use Soliant\SimpleFM\Repository\Builder\Exception\HydrationException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Embeddable;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\Field;
use Soliant\SimpleFM\Repository\Builder\Metadata\ManyToOne;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToMany;
use Soliant\SimpleFM\Repository\Builder\Metadata\OneToOne;
use Soliant\SimpleFM\Repository\Builder\Metadata\RecordId;
use Soliant\SimpleFM\Repository\Builder\MetadataHydration;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyBuilderInterface;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyInterface;
use Soliant\SimpleFM\Repository\Builder\RepositoryBuilderInterface;
use Soliant\SimpleFM\Repository\Builder\Type\StringType;
use Soliant\SimpleFM\Repository\RepositoryInterface;
use SoliantTest\SimpleFM\Repository\Builder\TestAssets\EmptyEntityInterface;
use SoliantTest\SimpleFM\Repository\Builder\TestAssets\EmptyProxyEntityInterface;

final class MetadataHydrationTest extends TestCase
{
    public function testSimpleFieldHydration()
    {
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [
            new Field('bar', 'baz', new StringType(), false, false),
        ], [], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $this->prophesize(ProxyBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => 'bat']);
        $this->assertSame('bat', $entity->baz);
    }

    public function testRepeatableFieldHydration()
    {
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [
            new Field('bar', 'baz', new StringType(), true, false),
        ], [], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $this->prophesize(ProxyBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => ['bat1', 'bat2']]);
        $this->assertSame(['bat1', 'bat2'], $entity->baz);
    }

    public function testRepeatableFieldHydrationWithoutArray()
    {
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [
            new Field('bar', 'baz', new StringType(), true, false),
        ], [], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $this->prophesize(ProxyBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('is not an array');
        $hydration->hydrateNewEntity(['bar' => 'bat']);
    }

    public function testReadOnlyFieldHydration()
    {
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [
            new Field('bar', 'baz', new StringType(), false, true),
        ], [], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $this->prophesize(ProxyBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => 'bat']);
        $this->assertSame('bat', $entity->baz);
    }

    public function testRecordIdHydration()
    {
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [], [], new RecordId('baz'));

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $this->prophesize(ProxyBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['record-id' => 1]);
        $this->assertSame(1, $entity->baz);
    }

    public function testEmbeddableHydrationWithoutPrefix()
    {
        $embeddablePrototype = new class
        {
            public $foo;
        };
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [
            new Embeddable('baz', '', new Entity('', get_class($embeddablePrototype), [
                new Field('fooField', 'foo', new StringType(), false, false),
            ], [], [], [], [])),
        ], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $this->prophesize(ProxyBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['fooField' => 'bar']);
        $this->assertInstanceOf(get_class($embeddablePrototype), $entity->baz);
        $this->assertSame('bar', $entity->baz->foo);
    }

    public function testEmbeddableHydrationWithPrefix()
    {
        $embeddablePrototype = new class
        {
            public $foo;
        };
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [
            new Embeddable('baz', 'bazPrefix', new Entity('', get_class($embeddablePrototype), [
                new Field('fooField', 'foo', new StringType(), false, false),
            ], [], [], [], [])),
        ], [], [], []);

        $hydration = new MetadataHydration(
            $this->prophesize(RepositoryBuilderInterface::class)->reveal(),
            $this->prophesize(ProxyBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bazPrefixfooField' => 'bar', 'fooField' => 'bat']);
        $this->assertInstanceOf(get_class($embeddablePrototype), $entity->baz);
        $this->assertSame('bar', $entity->baz->foo);
    }

    public function eagerHydrationSwitchProvider()
    {
        return [
            'eager-hydration-enabled' => [true],
            'eager-hydration-disabled' => [false],
        ];
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testManyToOneHydrationWithChild(bool $eagerHydration)
    {
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [
            new ManyToOne(
                'bat',
                'baz',
                'bar',
                'child',
                'id',
                'ID',
                EmptyProxyEntityInterface::class,
                false,
                $eagerHydration
            ),
        ], [], null, EmptyEntityInterface::class);

        $repository = $this->prophesize(RepositoryInterface::class);

        if ($eagerHydration) {
            $repository->createEntity(['ID' => '5'])->willReturn('child-entity');
        } else {
            $repository->findOneBy(['ID' => '5'])->willReturn('child-entity');
            $repository->quoteString('5')->willReturn('5');
        }

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('child')->willReturn(
            $repository->reveal()
        );

        $proxyBuilder = $this->prophesize(ProxyBuilderInterface::class);
        $proxyBuilder->createProxy(
            EmptyProxyEntityInterface::class,
            Argument::type('closure'),
            '5'
        )->will($this->createMockProxy());

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $proxyBuilder->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => [['ID' => 5]]]);

        if ($eagerHydration) {
            $this->assertSame('child-entity', $entity->baz);
        } else {
            $this->assertInstanceOf(ProxyInterface::class, $entity->baz);
            $this->assertSame('child-entity', $entity->baz->__getRealEntity());
        }
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testManyToOneHydrationWithoutEntity(bool $eagerHydration)
    {
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [
            new ManyToOne(
                'bat',
                'baz',
                'bar',
                'child',
                'id',
                'ID',
                EmptyProxyEntityInterface::class,
                false,
                $eagerHydration
            ),
        ], [], null, EmptyEntityInterface::class);

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('child')->willReturn(
            $this->prophesize(RepositoryInterface::class)->reveal()
        );

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $this->prophesize(ProxyBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => []]);
        $this->assertNull($entity->baz);
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testOneToOneOwningHydrationWithEntity(bool $eagerHydration)
    {
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [], [
            new OneToOne(
                'baz',
                'bar',
                'child',
                'ID',
                EmptyProxyEntityInterface::class,
                true,
                false,
                'id',
                'child-id',
                $eagerHydration
            ),
        ], null, EmptyEntityInterface::class);

        $repository = $this->prophesize(RepositoryInterface::class);

        if ($eagerHydration) {
            $repository->createEntity(['ID' => '5'])->willReturn('child-entity');
        } else {
            $repository->findOneBy(['ID' => '5'])->willReturn('child-entity');
            $repository->quoteString('5')->willReturn('5');
        }

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('child')->willReturn(
            $repository->reveal()
        );

        $proxyBuilder = $this->prophesize(ProxyBuilderInterface::class);
        $proxyBuilder->createProxy(
            EmptyProxyEntityInterface::class,
            Argument::type('closure'),
            '5'
        )->will($this->createMockProxy());

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $proxyBuilder->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => [['ID' => 5]]]);

        if ($eagerHydration) {
            $this->assertSame('child-entity', $entity->baz);
        } else {
            $this->assertInstanceOf(ProxyInterface::class, $entity->baz);
            $this->assertSame('child-entity', $entity->baz->__getRealEntity());
        }
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testOneToOneOwningHydrationWithoutEntity(bool $eagerHydration)
    {
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [], [
            new OneToOne(
                'baz',
                'bar',
                'child',
                'ID',
                EmptyProxyEntityInterface::class,
                true,
                false,
                'id',
                'child-id',
                $eagerHydration
            ),
        ], null, EmptyEntityInterface::class);

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('child')->willReturn(
            $this->prophesize(RepositoryInterface::class)->reveal()
        );

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $this->prophesize(ProxyBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => []]);
        $this->assertNull($entity->baz);
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testOneToOneInverseHydrationWithEntity(bool $eagerHydration)
    {
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [], [
            new OneToOne(
                'baz',
                'bar',
                'parent',
                'ID',
                EmptyProxyEntityInterface::class,
                false,
                false,
                null,
                null,
                $eagerHydration
            ),
        ], null, EmptyEntityInterface::class);

        $repository = $this->prophesize(RepositoryInterface::class);

        if ($eagerHydration) {
            $repository->createEntity(['ID' => '5'])->willReturn('parent-entity');
        } else {
            $repository->findOneBy(['ID' => '5'])->willReturn('parent-entity');
            $repository->quoteString('5')->willReturn('5');
        }

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('parent')->willReturn(
            $repository->reveal()
        );

        $proxyBuilder = $this->prophesize(ProxyBuilderInterface::class);
        $proxyBuilder->createProxy(
            EmptyProxyEntityInterface::class,
            Argument::type('closure'),
            '5'
        )->will($this->createMockProxy());

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $proxyBuilder->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => [['ID' => 5]]]);

        if ($eagerHydration) {
            $this->assertSame('parent-entity', $entity->baz);
        } else {
            $this->assertInstanceOf(ProxyInterface::class, $entity->baz);
            $this->assertSame('parent-entity', $entity->baz->__getRealEntity());
        }
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testOneToOneInverseHydrationWithoutEntity(bool $eagerHydration)
    {
        $entityPrototype = new class
        {
            public $baz;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [], [], [
            new OneToOne(
                'baz',
                'bar',
                'parent',
                'ID',
                EmptyProxyEntityInterface::class,
                false,
                false,
                null,
                null,
                $eagerHydration
            ),
        ], null, EmptyEntityInterface::class);

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('parent')->willReturn(
            $this->prophesize(RepositoryInterface::class)->reveal()
        );

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $this->prophesize(ProxyBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['bar' => []]);
        $this->assertNull($entity->baz);
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testOneToManyHydrationWithEntity(bool $eagerHydration)
    {
        $entityPrototype = new class
        {
            public $bar;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [
            new OneToMany('bar', 'baz', 'child', 'ID', $eagerHydration),
        ], [], []);

        $repository = $this->prophesize(RepositoryInterface::class);

        if ($eagerHydration) {
            $repository->createEntity(['ID' => 5])->will(function () {
                return 'child-entity-1';
            });
            $repository->createEntity(['ID' => 6])->will(function () {
                return 'child-entity-2';
            });
        } else {
            $testCase = $this;
            $repository->findByQuery(Argument::any())->will(function (array $parameters) use ($testCase) {
                $testCase->assertSame('5', $parameters[0]->toParameters()['-q1.value']);
                $testCase->assertSame('6', $parameters[0]->toParameters()['-q2.value']);
                return new ItemCollection(['child-entity-1', 'child-entity-2'], 2);
            });
        }

        $repositoryBuilder = $this->prophesize(RepositoryBuilderInterface::class);
        $repositoryBuilder->buildRepository('child')->willReturn(
            $repository->reveal()
        );

        $hydration = new MetadataHydration(
            $repositoryBuilder->reveal(),
            $this->prophesize(ProxyBuilderInterface::class)->reveal(),
            $entityMetadata
        );
        $entity = $hydration->hydrateNewEntity(['baz' => [['ID' => 5], ['ID' => 6]]]);
        $this->assertSame(['child-entity-1', 'child-entity-2'], iterator_to_array($entity->bar));
    }

    private function createMockProxy() : callable
    {
        return function (array $parameters) {
            return new class($parameters[1](), $parameters[2]) implements ProxyInterface
            {
                private $realEntity;

                private $relationId;

                public function __construct($realEntity, $relationId)
                {
                    $this->realEntity = $realEntity;
                    $this->relationId = $relationId;
                }

                public function __getRealEntity()
                {
                    return $this->realEntity;
                }

                public function __getRelationId()
                {
                    return $this->relationId;
                }
            };
        };
    }
}
