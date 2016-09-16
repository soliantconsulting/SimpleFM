<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository;

use Assert\Assertion;
use Soliant\SimpleFM\Authentication\Identity;
use Soliant\SimpleFM\Authentication\IdentityHandlerInterface;
use Soliant\SimpleFM\Client\ResultSet\ResultSetClient;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Repository\Exception\DomainException;
use Soliant\SimpleFM\Repository\Exception\InvalidResultException;
use Soliant\SimpleFM\Repository\Query\FindQuery;
use SplObjectStorage;

final class Repository
{
    /**
     * @var ResultSetClient
     */
    private $resultSetClient;

    /**
     * @var string
     */
    private $layout;

    /**
     * @var HydrationInterface
     */
    private $hydration;

    /**
     * @var ExtractionInterface
     */
    private $extraction;

    /**
     * @var IdentityHandlerInterface|null
     */
    private $identityHandler;

    /**
     * @var Identity|null
     */
    private $identity;

    /**
     * @var SplObjectStorage
     */
    private $managedEntities;

    public function __construct(
        ResultSetClient $resultSetClient,
        string $layout,
        HydrationInterface $hydration,
        ExtractionInterface $extraction,
        IdentityHandlerInterface $identityHandler = null
    ) {
        $this->resultSetClient = $resultSetClient;
        $this->layout = $layout;
        $this->hydration = $hydration;
        $this->extraction = $extraction;
        $this->identityHandler = $identityHandler;
        $this->managedEntities = new SplObjectStorage();
    }

    public function withIdentity(Identity $identity) : self
    {
        if (null === $this->identityHandler) {
            throw DomainException::fromMissingIdentityHandler();
        }

        $gateway = new self();
        $gateway->identity = $identity;

        return $gateway;
    }

    public function find(int $recordId)
    {
        return $this->findOneBy(['-recid' => $recordId]);
    }

    public function findOneBy(array $search)
    {
        $resultSet = $this->execute(new Command(
            $this->layout,
            $this->createSearchParameters($search) + ['-find' =>  null, '-max' => 1]
        ));

        if (empty($resultSet)) {
            return null;
        }

        return $this->createEntity($resultSet[0]);
    }

    public function findOneByQuery(FindQuery $query)
    {
        $resultSet = $this->execute(new Command(
            $this->layout,
            $query->toParameters() + ['-findquery' =>  null, '-max' => 1]
        ));

        if (empty($resultSet)) {
            return null;
        }

        return $this->createEntity($resultSet[0]);
    }

    public function findAll(array $sort = [], int $limit = null, int $offset = null) : array
    {
        $resultSet = $this->execute(new Command(
            $this->layout,
            (
                $this->createSortParameters($sort)
                + $this->createLimitAndOffsetParameters($limit, $offset)
                +  ['-findall' => null]
            )
        ));

        return $this->createCollection($resultSet);
    }

    public function findBy(array $search, array $sort = [], int $limit = null, int $offset = null) : array
    {
        $resultSet = $this->execute(new Command(
            $this->layout,
            (
                $this->createSearchParameters($search)
                + $this->createSortParameters($sort)
                + $this->createLimitAndOffsetParameters($limit, $offset)
                +  ['-find' => null]
            )
        ));

        return $this->createCollection($resultSet);
    }

    public function findByQuery(FindQuery $findQuery, array $sort = [], int $limit = null, int $offset = null) : array
    {
        $resultSet = $this->execute(new Command(
            $this->layout,
            (
                $findQuery->toParameters()
                + $this->createSortParameters($sort)
                + $this->createLimitAndOffsetParameters($limit, $offset)
                +  ['-findquery' => null]
            )
        ));

        return $this->createCollection($resultSet);
    }

    public function insert($entity)
    {
        $this->persist($entity, '-new');
    }

    public function update($entity)
    {
        if (!isset($this->managedEntities[$entity])) {
            throw DomainException::fromUnmanagedEntity($entity);
        }

        $this->persist($entity, '-edit', [
            '-recid' => $this->managedEntities[$entity]['record-id'],
            '-modid' => $this->managedEntities[$entity]['mod-id'],
        ]);
    }

    public function delete($entity)
    {
        if (!isset($this->managedEntities[$entity])) {
            throw DomainException::fromUnmanagedEntity($entity);
        }

        $this->execute(new Command(
            $this->layout,
            [
                '-recid' => $this->managedEntities[$entity]['record-id'],
                '-modid' => $this->managedEntities[$entity]['mod-id'],
                '-delete' => null
            ]
        ));
        unset($this->managedEntities[$entity]);
    }

    public function quoteString(string $string) : string
    {
        return $this->resultSetClient->quoteString($string);
    }

    private function persist($entity, string $mode, array $additionalParameters = [])
    {
        $resultSet = $this->execute(new Command(
            $this->layout,
            $this->extraction->extract($entity) + $additionalParameters + [$mode => null]
        ));

        if (empty($resultSet)) {
            throw InvalidResultException::fromEmptyResultSet();
        }

        $this->hydration->hydrateExistingEntity($resultSet[0], $entity);
        $this->addOrUpdateManagedEntity($resultSet[0]['record-id'], $resultSet[0]['mod-id'], $entity);
    }

    private function addOrUpdateManagedEntity(int $recordId, int $modId, $entity)
    {
        $this->managedEntities[$entity] = [
            'record-id' => $recordId,
            'mod-id' => $modId,
        ];
    }

    private function createCollection(array $resultSet) : array
    {
        $collection = [];

        foreach ($resultSet as $record) {
            $collection[] = $this->createEntity($record);
        }

        return $collection;
    }

    private function createEntity(array $record)
    {
        $entity = $this->hydration->hydrateNewEntity($record);
        $this->addOrUpdateManagedEntity($record['record-id'], $record['mod-id'], $entity);
        return $entity;
    }

    private function createSearchParameters(array $search) : array
    {
        $searchParameters = [];

        foreach ($search as $field => $value) {
            $searchParameters[$field] = $this->quoteString($value);
        }

        return $searchParameters;
    }

    private function createSortParameters(array $sort) : array
    {
        if (count($sort) > 9) {
            throw DomainException::fromTooManySortParameters(9, $sort);
        }

        $index = 1;
        $parameters = [];

        foreach ($sort as $field => $order) {
            $parameters['-sortfield' . $index] = $field;
            $parameters['-sortorder' . $index] = $order;
            ++$index;
        }

        return $parameters;
    }

    private function createLimitAndOffsetParameters(int $limit = null, int $offset = null) : array
    {
        $parameters = [];

        if (null !== $limit) {
            $parameters['-max'] = $limit;
        }

        if (null !== $offset) {
            $parameters['-skip'] = $offset;
        }

        return $parameters;
    }

    private function execute(Command $command) : array
    {
        if (null === $this->identity) {
            return $this->resultSetClient->execute($command);
        }

        Assertion::notNull($this->identityHandler);

        return $this->resultSetClient->execute($command->withCredentials(
            $this->identity->getUsername(),
            $this->identityHandler->decryptPassword($this->identity)
        ));
    }
}
