<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Soliant\SimpleFM\Authentication\Identity;
use Soliant\SimpleFM\Authentication\IdentityHandlerInterface;
use Soliant\SimpleFM\Client\ResultSet\ResultSetClientInterface;
use Soliant\SimpleFM\Collection\ItemCollection;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyInterface;
use Soliant\SimpleFM\Repository\Exception\DomainException;
use Soliant\SimpleFM\Repository\Exception\InvalidResultException;
use Soliant\SimpleFM\Repository\ExtractionInterface;
use Soliant\SimpleFM\Repository\HydrationInterface;
use Soliant\SimpleFM\Repository\Query\FindQuery;
use Soliant\SimpleFM\Repository\Query\Query;
use Soliant\SimpleFM\Repository\Repository;
use stdClass;

final class RepositoryTest extends TestCase
{
    public function testQuoteStringUsesClientMethod()
    {
        $resultSetClient = $this->prophesize(ResultSetClientInterface::class);
        $resultSetClient->quoteString('foo')->willReturn('bar');

        $repository = new Repository(
            $resultSetClient->reveal(),
            'foo',
            $this->prophesize(HydrationInterface::class)->reveal(),
            $this->prophesize(ExtractionInterface::class)->reveal()
        );

        $this->assertSame('bar', $repository->quoteString('foo'));
    }

    public function testWithIdentityCreatesNewRepository()
    {
        $repository = new Repository(
            $this->prophesize(ResultSetClientInterface::class)->reveal(),
            'foo',
            $this->prophesize(HydrationInterface::class)->reveal(),
            $this->prophesize(ExtractionInterface::class)->reveal(),
            $this->prophesize(IdentityHandlerInterface::class)->reveal()
        );

        $this->assertNotSame($repository, $repository->withIdentity(new Identity('foo', 'bar')));
    }

    public function testWithIdentityPassesIdentityToNewCommands()
    {
        $identity = new Identity('foo', 'bar');

        $repository = $this->createAssertiveRepository(function (Command $command) use ($identity) {
            $this->assertSame($identity, $command->getIdentity());
            return new ItemCollection([], 0);
        }, null, null)->withIdentity($identity);

        $repository->find(1);
    }

    public function testFindWithResult()
    {
        $entity = new stdClass();

        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame('-lay=foo&-recid=1&-find&-max=1', (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal());

        $this->assertSame($entity, $repository->find(1));
    }

    public function testFindWithoutResult()
    {
        $repository = $this->createAssertiveRepository(function () {
            return new ItemCollection([], 0);
        });

        $this->assertNull($repository->find(1));
    }

    public function testEntityCaching()
    {
        $entity = new stdClass();

        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal());

        $this->assertSame($repository->find(1), $repository->find(1));
    }

    public function testFindOneByWithResult()
    {
        $entity = new stdClass();

        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame('-lay=foo&foo=bar&-find&-max=1', (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal());

        $this->assertSame($entity, $repository->findOneBy(['foo' => 'bar']));
    }

    public function testFindOneByWithAutoQuoteDisabled()
    {
        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame('-lay=foo&foo=%3E%3D5&-find&-max=1', (string) $command);
            return new ItemCollection([], 0);
        });

        $repository->findOneBy(['foo' => '>=5'], false);
    }

    public function testFindOneByWithoutResult()
    {
        $repository = $this->createAssertiveRepository(function () {
            return new ItemCollection([], 0);
        });

        $this->assertNull($repository->findOneBy([]));
    }

    public function testFindOneByQueryWithResult()
    {
        $entity = new stdClass();

        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame('-lay=foo&-query=%28q1%29&-q1=foo&-q1.value=bar&-findquery&-max=1', (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal());

        $query = new FindQuery();
        $query->addOrQueries(new Query('foo', 'bar'));
        $this->assertSame($entity, $repository->findOneByQuery($query));
    }

    public function testFindOneByQueryWithoutResult()
    {
        $repository = $this->createAssertiveRepository(function () {
            return new ItemCollection([], 0);
        });

        $query = new FindQuery();
        $query->addOrQueries(new Query('foo', 'bar'));
        $this->assertNull($repository->findOneByQuery($query));
    }

    public function testFindAllWithoutArguments()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame('-lay=foo&-findall', (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal());

        $this->assertSame([$entity], iterator_to_array($repository->findAll()));
    }

    public function testFindAllWithParameters()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame('-lay=foo&-sortfield.1=foo&-sortorder.1=ascend&-max=1&-skip=2&-findall', (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal());

        $this->assertSame([$entity], iterator_to_array($repository->findAll(['foo' => 'ascend'], 1, 2)));
    }

    public function testFindByWithoutArguments()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame('-lay=foo&foo=bar&-find', (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal());

        $this->assertSame([$entity], iterator_to_array($repository->findBy(['foo' => 'bar'])));
    }

    public function testFindByWithParameters()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame(
                '-lay=foo&foo=bar&-sortfield.1=foo&-sortorder.1=ascend&-max=1&-skip=2&-find',
                (string) $command
            );
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal());

        $this->assertSame(
            [$entity],
            iterator_to_array($repository->findBy(['foo' => 'bar'], ['foo' => 'ascend'], 1, 2))
        );
    }

    public function testFindByWithAutoQuoteDisabled()
    {
        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame('-lay=foo&foo=%3E%3D5&-find', (string) $command);
            return new ItemCollection([], 0);
        });

        $repository->findBy(['foo' => '>=5'], [], null, null, false);
    }

    public function testFindByQueryWithoutArguments()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame('-lay=foo&-query=%28q1%29&-q1=foo&-q1.value=bar&-findquery', (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal());

        $query = new FindQuery();
        $query->addOrQueries(new Query('foo', 'bar'));
        $this->assertSame([$entity], iterator_to_array($repository->findByQuery($query)));
    }

    public function testFindByQueryWithParameters()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame(
                (
                    '-lay=foo&-query=%28q1%29&-q1=foo&-q1.value=bar&-sortfield.1=foo&-sortorder.1=ascend&-max=1&-skip=2'
                    . '&-findquery'
                ),
                (string) $command
            );
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal());

        $query = new FindQuery();
        $query->addOrQueries(new Query('foo', 'bar'));
        $this->assertSame([$entity], iterator_to_array($repository->findByQuery($query, ['foo' => 'ascend'], 1, 2)));
    }

    public function testInsert()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateExistingEntity(
            ['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'],
            $entity
        )->willReturn($entity);
        $extraction = $this->prophesize(ExtractionInterface::class);
        $extraction->extract($entity)->willReturn(['foo' => 'bar']);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame('-lay=foo&foo=bar&-new', (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal(), $extraction->reveal());

        $repository->insert($entity);
    }

    public function testUpdateWithManagedEntity()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);
        $hydration->hydrateExistingEntity(
            ['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'],
            $entity
        )->willReturn($entity);
        $extraction = $this->prophesize(ExtractionInterface::class);
        $extraction->extract($entity)->willReturn(['foo' => 'bar']);

        $index = -1;
        $repository = $this->createAssertiveRepository(function (Command $command) use (&$index) {
            $this->assertSame([
                '-lay=foo&-recid=1&-find&-max=1',
                '-lay=foo&foo=bar&-recid=1&-modid=1&-edit',
            ][++$index], (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal(), $extraction->reveal());

        $foundEntity = $repository->find(1);
        $repository->update($foundEntity);
    }

    public function testForceUpdateWithManagedEntity()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);
        $hydration->hydrateExistingEntity(
            ['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'],
            $entity
        )->willReturn($entity);
        $extraction = $this->prophesize(ExtractionInterface::class);
        $extraction->extract($entity)->willReturn(['foo' => 'bar']);

        $index = -1;
        $repository = $this->createAssertiveRepository(function (Command $command) use (&$index) {
            $this->assertSame([
                '-lay=foo&-recid=1&-find&-max=1',
                '-lay=foo&foo=bar&-recid=1&-edit',
            ][++$index], (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal(), $extraction->reveal());

        $foundEntity = $repository->find(1);
        $repository->update($foundEntity, true);
    }

    public function testUpdateWithUnmanagedEntity()
    {
        $repository = $this->createAssertiveRepository();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('is not managed');
        $repository->update(new stdClass());
    }

    public function testUpdateWithManagedProxyEntity()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);
        $hydration->hydrateExistingEntity(
            ['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'],
            $entity
        )->willReturn($entity);
        $extraction = $this->prophesize(ExtractionInterface::class);
        $extraction->extract($entity)->willReturn(['foo' => 'bar']);

        $index = -1;
        $repository = $this->createAssertiveRepository(function (Command $command) use (&$index) {
            $this->assertSame([
                '-lay=foo&-recid=1&-find&-max=1',
                '-lay=foo&foo=bar&-recid=1&-modid=1&-edit',
            ][++$index], (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal(), $extraction->reveal());

        $foundEntity = $repository->find(1);
        $repository->update($this->createMockProxy($foundEntity, 1));
    }

    public function testDeleteWithManagedEntity()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);
        $extraction = $this->prophesize(ExtractionInterface::class);
        $extraction->extract($entity)->willReturn(['foo' => 'bar']);

        $index = -1;
        $repository = $this->createAssertiveRepository(function (Command $command) use (&$index) {
            $this->assertSame([
                '-lay=foo&-recid=1&-find&-max=1',
                '-lay=foo&-recid=1&-delete&-modid=1',
            ][++$index], (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal(), $extraction->reveal());

        $foundEntity = $repository->find(1);
        $repository->delete($foundEntity);
    }

    public function testForceDeleteWithManagedEntity()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);
        $extraction = $this->prophesize(ExtractionInterface::class);
        $extraction->extract($entity)->willReturn(['foo' => 'bar']);

        $index = -1;
        $repository = $this->createAssertiveRepository(function (Command $command) use (&$index) {
            $this->assertSame([
                '-lay=foo&-recid=1&-find&-max=1',
                '-lay=foo&-recid=1&-delete',
            ][++$index], (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal(), $extraction->reveal());

        $foundEntity = $repository->find(1);
        $repository->delete($foundEntity, true);
    }

    public function testDeleteWithUnmanagedEntity()
    {
        $repository = $this->createAssertiveRepository();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('is not managed');
        $repository->delete(new stdClass());
    }

    public function testDeleteWithManagedProxyEntity()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateNewEntity(['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'])->willReturn($entity);
        $extraction = $this->prophesize(ExtractionInterface::class);
        $extraction->extract($entity)->willReturn(['foo' => 'bar']);

        $index = -1;
        $repository = $this->createAssertiveRepository(function (Command $command) use (&$index) {
            $this->assertSame([
                '-lay=foo&-recid=1&-find&-max=1',
                '-lay=foo&-recid=1&-delete&-modid=1',
            ][++$index], (string) $command);
            return new ItemCollection([['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar']], 1);
        }, $hydration->reveal(), $extraction->reveal());

        $foundEntity = $repository->find(1);
        $repository->delete($this->createMockProxy($foundEntity, 1));
    }

    public function testFindAllWithTooManySortArgs()
    {
        $repository = $this->createAssertiveRepository();

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

    public function testInsertWithEmptyResult()
    {
        $entity = new stdClass();
        $hydration = $this->prophesize(HydrationInterface::class);
        $hydration->hydrateExistingEntity(
            ['record-id' => 1, 'mod-id' => 1, 'foo' => 'bar'],
            $entity
        )->willReturn($entity);
        $extraction = $this->prophesize(ExtractionInterface::class);
        $extraction->extract($entity)->willReturn(['foo' => 'bar']);

        $repository = $this->createAssertiveRepository(function (Command $command) {
            $this->assertSame('-lay=foo&foo=bar&-new', (string) $command);
            return new ItemCollection([], 0);
        }, $hydration->reveal(), $extraction->reveal());

        $this->expectException(InvalidResultException::class);
        $this->expectExceptionMessage('Empty result set received');

        $repository->insert($entity);
    }

    private function createAssertiveRepository(
        callable $resultSetCallback = null,
        HydrationInterface $hydration = null,
        ExtractionInterface $extraction = null,
        IdentityHandlerInterface $identityHandler = null
    ) : Repository {
        $resultSetClient = $this->prophesize(ResultSetClientInterface::class);
        $resultSetClient->quoteString(Argument::any())->will(function (array $parameters) {
            return $parameters[0];
        });

        if (null !== $resultSetCallback) {
            $resultSetClient->execute(Argument::any())->will(function (array $parameters) use ($resultSetCallback) {
                return $resultSetCallback($parameters[0]);
            });
        }

        return new Repository(
            $resultSetClient->reveal(),
            'foo',
            $hydration ?: $this->prophesize(HydrationInterface::class)->reveal(),
            $extraction ?: $this->prophesize(ExtractionInterface::class)->reveal(),
            $identityHandler
        );
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
}
