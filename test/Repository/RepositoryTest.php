<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Client\Exception\FileMakerException;
use Soliant\SimpleFM\Query\Conditions;
use Soliant\SimpleFM\Query\Field;
use Soliant\SimpleFM\Query\Query;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyInterface;
use Soliant\SimpleFM\Repository\Exception\DomainException;
use Soliant\SimpleFM\Repository\ExtractionInterface;
use Soliant\SimpleFM\Repository\HydrationInterface;
use Soliant\SimpleFM\Repository\Repository;
use Soliant\SimpleFM\Sort\Sort;
use stdClass;

final class RepositoryTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $client;

    /**
     * @var array
     */
    private $defaultRecord;

    /**
     * @var stdClass
     */
    private $defaultEntity;

    public function setUp() : void
    {
        $this->client = $this->prophesize(ClientInterface::class);
        $this->defaultRecord = [
            'recordId' => '1',
            'modId' => '1',
            'fieldData' => [
                'foo' => 'bar',
            ],
        ];
        $this->defaultEntity = new stdClass();
    }

    public function testFindWithResult() : void
    {
        $this->client->getRecord('foo', 1)->willReturn($this->defaultRecord);
        $repository = $this->createRepository();

        $this->assertSame($this->defaultEntity, $repository->find(1));
    }

    public function testFindWithoutResult() : void
    {
        $this->client->getRecord('foo', 1)->willThrow(new FileMakerException('', 101));
        $repository = $this->createRepository();

        $this->assertNull($repository->find(1));
    }

    public function testEntityCaching() : void
    {
        $this->client->getRecord('foo', 1)->willReturn($this->defaultRecord);
        $repository = $this->createRepository();

        $this->assertSame($repository->find(1), $repository->find(1));
    }

    public function testFindOneByWithResult() : void
    {
        $this->client->find('foo', Argument::that(function (Query $query) : bool {
            $this->assertQuerySame($query, [['foo' => 'bar']]);
            return true;
        }), 0, 1)->willReturn([$this->defaultRecord]);
        $repository = $this->createRepository();

        $this->assertSame($this->defaultEntity, $repository->findOneBy(['foo' => 'bar']));
    }

    public function testFindOneByWithAutoQuoteDisabled() : void
    {
        $this->client->find('foo', Argument::that(function (Query $query) : bool {
            $this->assertQuerySame($query, [['foo' => '>=5']]);
            return true;
        }), 0, 1)->willReturn([$this->defaultRecord]);
        $repository = $this->createRepository();

        $repository->findOneBy(['foo' => '>=5'], false);
    }

    public function testFindOneByWithoutResult() : void
    {
        $this->client->find('foo', Argument::that(function (Query $query) : bool {
            $this->assertQuerySame($query, [['foo' => 'baz']]);
            return true;
        }), 0, 1)->willReturn([]);
        $repository = $this->createRepository();

        $this->assertNull($repository->findOneBy(['foo' => 'baz']));
    }

    public function testFindOneByQueryWithResult() : void
    {
        $this->client->find('foo', Argument::that(function (Query $query) : bool {
            $this->assertQuerySame($query, [['foo' => 'bar']]);
            return true;
        }), 0, 1)->willReturn([$this->defaultRecord]);
        $repository = $this->createRepository();

        $this->assertSame($this->defaultEntity, $repository->findOneByQuery(
            new Query(new Conditions(false, new Field('foo', 'bar')))
        ));
    }

    public function testFindOneByQueryWithoutResult() : void
    {
        $this->client->find('foo', Argument::that(function (Query $query) : bool {
            $this->assertQuerySame($query, [['foo' => 'baz']]);
            return true;
        }), 0, 1)->willReturn([]);
        $repository = $this->createRepository();

        $this->assertNull($repository->findOneByQuery(
            new Query(new Conditions(false, new Field('foo', 'baz')))
        ));
    }

    public function testFindAllWithoutArguments() : void
    {
        $this->client->find('foo', null, null, null)->willReturn([$this->defaultRecord]);
        $repository = $this->createRepository();

        $this->assertSame([$this->defaultEntity], iterator_to_array($repository->findAll()));
    }

    public function testFindAllWithParameters() : void
    {
        $this->client->find('foo', null, 1, 2, new Sort('foo', true))->willReturn([$this->defaultRecord]);
        $repository = $this->createRepository();

        $this->assertSame([$this->defaultEntity], iterator_to_array($repository->findAll(['foo' => 'ascend'], 1, 2)));
    }

    public function testFindAllWithTooManySortArgs() : void
    {
        $repository = $this->createRepository();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('There cannot be more than 9 sort parameters, 10 supplied');
        $repository->findAll(
            [
                'field1' => 'asc',
                'field2' => 'asc',
                'field3' => 'asc',
                'field4' => 'asc',
                'field5' => 'asc',
                'field6' => 'asc',
                'field7' => 'asc',
                'field8' => 'asc',
                'field9' => 'asc',
                'field10' => 'asc',
            ]
        );
    }

    public function testFindByWithoutArguments() : void
    {
        $this->client->find('foo', Argument::that(function (Query $query) : bool {
            $this->assertQuerySame($query, [['foo' => 'bar']]);
            return true;
        }), null, null)->willReturn([$this->defaultRecord]);
        $repository = $this->createRepository();

        $this->assertSame([$this->defaultEntity], iterator_to_array($repository->findBy(['foo' => 'bar'])));
    }

    public function testFindByWithParameters() : void
    {
        $this->client->find('foo', Argument::that(function (Query $query) : bool {
            $this->assertQuerySame($query, [['foo' => 'bar']]);
            return true;
        }), 1, 2, new Sort('foo', true))->willReturn([$this->defaultRecord]);
        $repository = $this->createRepository();

        $this->assertSame([$this->defaultEntity], iterator_to_array($repository->findBy(
            ['foo' => 'bar'],
            ['foo' => 'ascend'],
            1,
            2
        )));
    }

    public function testFindByWithAutoQuoteDisabled() : void
    {
        $this->client->find('foo', Argument::that(function (Query $query) : bool {
            $this->assertQuerySame($query, [['foo' => '>=5']]);
            return true;
        }), null, null)->willReturn([$this->defaultRecord]);
        $repository = $this->createRepository();

        $this->assertSame([$this->defaultEntity], iterator_to_array($repository->findBy(
            ['foo' => '>=5'],
            [],
            null,
            null,
            false
        )));
    }

    public function testFindByQueryWithoutArguments() : void
    {
        $this->client->find('foo', Argument::that(function (Query $query) : bool {
            $this->assertQuerySame($query, [['foo' => 'bar']]);
            return true;
        }), null, null)->willReturn([$this->defaultRecord]);
        $repository = $this->createRepository();

        $this->assertSame([$this->defaultEntity], iterator_to_array($repository->findByQuery(
            new Query(new Conditions(false, new Field('foo', 'bar')))
        )));
    }

    public function testFindByQueryWithParameters() : void
    {
        $this->client->find('foo', Argument::that(function (Query $query) : bool {
            $this->assertQuerySame($query, [['foo' => 'bar']]);
            return true;
        }), 1, 2, new Sort('foo', true))->willReturn([$this->defaultRecord]);
        $repository = $this->createRepository();

        $this->assertSame([$this->defaultEntity], iterator_to_array($repository->findByQuery(
            new Query(new Conditions(false, new Field('foo', 'bar'))),
            ['foo' => 'ascend'],
            1,
            2
        )));
    }

    public function testInsert() : void
    {
        $this->client->createRecord('foo', ['foo' => 'bar'])->willReturn([
            'recordId' => '1',
            'modId' => '1',
        ])->shouldBeCalled();
        $repository = $this->createRepository();
        $repository->insert($this->defaultEntity);
    }

    public function testUpdateWithManagedEntity() : void
    {
        $this->client->getRecord('foo', 1)->willReturn($this->defaultRecord);
        $this->client->updateRecord('foo', 1, ['foo' => 'bar'])->willReturn([
            'modId' => '1',
        ])->shouldBeCalled();
        $repository = $this->createRepository();

        $foundEntity = $repository->find(1);
        $repository->update($foundEntity);
    }

    public function testUpdateWithUnmanagedEntity() : void
    {
        $repository = $this->createRepository();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('is not managed');
        $repository->update(new stdClass());
    }

    public function testUpdateWithManagedProxyEntity() : void
    {
        $this->client->getRecord('foo', 1)->willReturn($this->defaultRecord);
        $this->client->updateRecord('foo', 1, ['foo' => 'bar'])->willReturn([
            'modId' => '1',
        ])->shouldBeCalled();
        $repository = $this->createRepository();

        $foundEntity = $repository->find(1);
        $repository->update($this->createMockProxy($foundEntity, 1));
    }

    public function testDeleteWithManagedEntity() : void
    {
        $this->client->getRecord('foo', 1)->willReturn($this->defaultRecord);
        $this->client->deleteRecord('foo', 1)->shouldBeCalled();
        $repository = $this->createRepository();

        $foundEntity = $repository->find(1);
        $repository->delete($foundEntity);
    }

    public function testDeleteWithUnmanagedEntity() : void
    {
        $repository = $this->createRepository();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('is not managed');
        $repository->delete(new stdClass());
    }

    public function testDeleteWithManagedProxyEntity() : void
    {
        $this->client->getRecord('foo', 1)->willReturn($this->defaultRecord);
        $this->client->deleteRecord('foo', 1)->shouldBeCalled();
        $repository = $this->createRepository();

        $foundEntity = $repository->find(1);
        $repository->delete($this->createMockProxy($foundEntity, 1));
    }

    private function createRepository() : Repository
    {
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(
            $this->defaultRecord,
            $this->client->reveal()
        )->willReturn($this->defaultEntity);
        $hydration->hydrateExistingEntity(
            $this->defaultRecord,
            $this->defaultEntity,
            $this->client->reveal()
        )->willReturn($this->defaultEntity);

        $extraction = $this->prophesize(ExtractionInterface::class);
        $extraction->extract(
            $this->defaultEntity,
            $this->client->reveal()
        )->willReturn($this->defaultRecord['fieldData']);

        return new Repository($this->client->reveal(), 'foo', $hydration->reveal(), $extraction->reveal());
    }

    private function createMockProxy($entity, $relationId) : ProxyInterface
    {
        return new class($entity, $relationId) implements ProxyInterface {
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

    private function assertQuerySame(Query $query, array $expected) : void
    {
        $actual = array_map(function (Conditions $conditions) : array {
            $result = [];

            if ($conditions->isOmit()) {
                $result['omit'] = true;
            }

            foreach ($conditions->getFields() as $field) {
                $result[$field->getName()] = $field->getValue();
            }

            return $result;
        }, $query->getOrConditions());

        $this->assertSame($expected, $actual);
    }
}
