<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Collection\ItemCollection;
use Soliant\SimpleFM\Query\Conditions;
use Soliant\SimpleFM\Query\Query;
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
use Soliant\SimpleFM\Query\Field as QueryField;
use SoliantTest\SimpleFM\Repository\Builder\TestAssets\EmptyEntityInterface;
use SoliantTest\SimpleFM\Repository\Builder\TestAssets\EmptyProxyEntityInterface;
use stdClass;

final class MetadataHydrationTest extends TestCase
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function setUp() : void
    {
        $this->client = $this->prophesize(ClientInterface::class)->reveal();
    }

    public function testSimpleFieldHydration() : void
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
        $entity = $hydration->hydrateNewEntity(['fieldData' => ['bar' => 'bat']], $this->client);
        $this->assertSame('bat', $entity->baz);
    }

    public function testRepeatableFieldHydration() : void
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
        $entity = $hydration->hydrateNewEntity(['fieldData' => ['bar' => ['bat1', 'bat2']]], $this->client);
        $this->assertSame(['bat1', 'bat2'], $entity->baz);
    }

    public function testRepeatableFieldHydrationWithoutArray() : void
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
        $hydration->hydrateNewEntity(['fieldData' => ['bar' => 'bat']], $this->client);
    }

    public function testReadOnlyFieldHydration() : void
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
        $entity = $hydration->hydrateNewEntity(['fieldData' => ['bar' => 'bat']], $this->client);
        $this->assertSame('bat', $entity->baz);
    }

    public function testRecordIdHydration() : void
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
        $entity = $hydration->hydrateNewEntity(['recordId' => 1], $this->client);
        $this->assertSame(1, $entity->baz);
    }

    public function testEmbeddableHydrationWithoutPrefix() : void
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
        $entity = $hydration->hydrateNewEntity(['fieldData' => ['fooField' => 'bar']], $this->client);
        $this->assertInstanceOf(get_class($embeddablePrototype), $entity->baz);
        $this->assertSame('bar', $entity->baz->foo);
    }

    public function testEmbeddableHydrationWithPrefix() : void
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
        $entity = $hydration->hydrateNewEntity(
            ['fieldData' => ['bazPrefixfooField' => 'bar', 'fooField' => 'bat']],
            $this->client
        );
        $this->assertInstanceOf(get_class($embeddablePrototype), $entity->baz);
        $this->assertSame('bar', $entity->baz->foo);
    }

    public function eagerHydrationSwitchProvider() : array
    {
        return [
            'eager-hydration-enabled' => [true],
            'eager-hydration-disabled' => [false],
        ];
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testManyToOneHydrationWithChild(bool $eagerHydration) : void
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
        $child = new stdClass();

        if ($eagerHydration) {
            $repository->createEntity(['ID' => '5'])->willReturn($child);
        } else {
            $repository->findOneByQuery(
                new Query(new Conditions(false, new QueryField('ID', '5')))
            )->willReturn($child);
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
        $entity = $hydration->hydrateNewEntity(['portalData' => ['bar' => [['ID' => 5]]]], $this->client);

        if ($eagerHydration) {
            $this->assertSame($child, $entity->baz);
        } else {
            $this->assertInstanceOf(ProxyInterface::class, $entity->baz);
            $this->assertSame($child, $entity->baz->__getRealEntity());
        }
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testManyToOneHydrationWithoutEntity(bool $eagerHydration) : void
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
        $entity = $hydration->hydrateNewEntity(['portalData' => ['bar' => []]], $this->client);
        $this->assertNull($entity->baz);
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testOneToOneOwningHydrationWithEntity(bool $eagerHydration) : void
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
        $child = new stdClass();

        if ($eagerHydration) {
            $repository->createEntity(['ID' => '5'])->willReturn($child);
        } else {
            $repository->findOneByQuery(
                new Query(new Conditions(false, new QueryField('ID', '5')))
            )->willReturn($child);
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
        $entity = $hydration->hydrateNewEntity(['portalData' => ['bar' => [['ID' => 5]]]], $this->client);

        if ($eagerHydration) {
            $this->assertSame($child, $entity->baz);
        } else {
            $this->assertInstanceOf(ProxyInterface::class, $entity->baz);
            $this->assertSame($child, $entity->baz->__getRealEntity());
        }
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testOneToOneOwningHydrationWithoutEntity(bool $eagerHydration) : void
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
        $entity = $hydration->hydrateNewEntity(['portalData' => ['bar' => []]], $this->client);
        $this->assertNull($entity->baz);
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testOneToOneInverseHydrationWithEntity(bool $eagerHydration) : void
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
        $parent = new stdClass();

        if ($eagerHydration) {
            $repository->createEntity(['ID' => '5'])->willReturn($parent);
        } else {
            $repository->findOneByQuery(
                new Query(new Conditions(false, new QueryField('ID', '5')))
            )->willReturn($parent);
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
        $entity = $hydration->hydrateNewEntity(['portalData' => ['bar' => [['ID' => 5]]]], $this->client);

        if ($eagerHydration) {
            $this->assertSame($parent, $entity->baz);
        } else {
            $this->assertInstanceOf(ProxyInterface::class, $entity->baz);
            $this->assertSame($parent, $entity->baz->__getRealEntity());
        }
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testOneToOneInverseHydrationWithoutEntity(bool $eagerHydration) : void
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
        $entity = $hydration->hydrateNewEntity(['portalData' => ['bar' => []]], $this->client);
        $this->assertNull($entity->baz);
    }

    /**
     * @dataProvider eagerHydrationSwitchProvider
     */
    public function testOneToManyHydrationWithEntity(bool $eagerHydration) : void
    {
        $entityPrototype = new class
        {
            public $bar;
        };

        $entityMetadata = new Entity('foo', get_class($entityPrototype), [], [], [
            new OneToMany('bar', 'baz', 'child', 'ID', $eagerHydration),
        ], [], []);

        $repository = $this->prophesize(RepositoryInterface::class);
        $child1 = new stdClass();
        $child2 = new stdClass();

        if ($eagerHydration) {
            $repository->createEntity(['ID' => 5])->willReturn($child1);
            $repository->createEntity(['ID' => 6])->willReturn($child2);
        } else {
            $repository->findByQuery(
                new Query(
                    new Conditions(false, new QueryField('ID', '5')),
                    new Conditions(false, new QueryField('ID', '6'))
                )
            )->willReturn(new ItemCollection([$child1, $child2], 2));
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
        $entity = $hydration->hydrateNewEntity(['portalData' => ['baz' => [['ID' => 5], ['ID' => 6]]]], $this->client);
        $this->assertSame([$child1, $child2], iterator_to_array($entity->bar));
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
