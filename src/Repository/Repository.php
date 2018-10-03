<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository;

use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Client\Exception\FileMakerException;
use Soliant\SimpleFM\Collection\CollectionInterface;
use Soliant\SimpleFM\Collection\ItemCollection;
use Soliant\SimpleFM\Query\Conditions;
use Soliant\SimpleFM\Query\Field;
use Soliant\SimpleFM\Query\Query;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyInterface;
use Soliant\SimpleFM\Repository\Exception\DomainException;
use Soliant\SimpleFM\Sort\Sort;
use SplObjectStorage;

final class Repository implements RepositoryInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

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
     * @var SplObjectStorage
     */
    private $managedEntities;

    /**
     * @var array
     */
    private $entitiesByRecordId = [];

    public function __construct(
        ClientInterface $client,
        string $layout,
        HydrationInterface $hydration,
        ExtractionInterface $extraction
    ) {
        $this->client = $client;
        $this->layout = $layout;
        $this->hydration = $hydration;
        $this->extraction = $extraction;
        $this->managedEntities = new SplObjectStorage();
    }

    public function find(int $recordId) : ?object
    {
        try {
            $record = $this->client->getRecord($this->layout, $recordId);
        } catch (FileMakerException $e) {
            if (101 === $e->getCode()) {
                return null;
            }

            throw $e;
        }

        return $this->createEntity($record);
    }

    public function findOneBy(array $search, bool $autoQuoteSearch = true) : ?object
    {
        $records = $this->client->find($this->layout, $this->createQuery($search, $autoQuoteSearch), 0, 1);

        if (empty($records)) {
            return null;
        }

        return $this->createEntity($records[0]);
    }

    public function findOneByQuery(Query $query) : ?object
    {
        $records = $this->client->find($this->layout, $query, 0, 1);

        if (empty($records)) {
            return null;
        }

        return $this->createEntity($records[0]);
    }

    public function findAll(array $sort = [], int $limit = null, int $offset = null) : CollectionInterface
    {
        return $this->createCollection(
            $this->client->find(
                $this->layout,
                null,
                $limit,
                $offset,
                ...$this->createSortArguments($sort)
            )
        );
    }

    public function findBy(
        array $search,
        array $sort = [],
        int $limit = null,
        int $offset = null,
        bool $autoQuoteSearch = true
    ) : CollectionInterface {
        return $this->createCollection(
            $this->client->find(
                $this->layout,
                $this->createQuery($search, $autoQuoteSearch),
                $limit,
                $offset,
                ...$this->createSortArguments($sort)
            )
        );
    }

    public function findByQuery(
        Query $query,
        array $sort = [],
        int $limit = null,
        int $offset = null
    ) : CollectionInterface {
        return $this->createCollection(
            $this->client->find(
                $this->layout,
                $query,
                $limit,
                $offset,
                ...$this->createSortArguments($sort)
            )
        );
    }

    public function insert(object $entity)
    {
        $result = $this->client->createRecord($this->layout, $this->extraction->extract($entity, $this->client));
        $this->addOrUpdateManagedEntity((int) $result['recordId'], (int) $result['modId'], $entity);
    }

    public function update(object $entity)
    {
        if ($entity instanceof ProxyInterface) {
            $entity = $entity->__getRealEntity();
        }

        if (! isset($this->managedEntities[$entity])) {
            throw DomainException::fromUnmanagedEntity($entity);
        }

        $recordId = $this->managedEntities[$entity]['recordId'];
        $result = $this->client->updateRecord(
            $this->layout,
            $recordId,
            $this->extraction->extract($entity, $this->client)
        );
        $this->addOrUpdateManagedEntity($recordId, (int) $result['modId'], $entity);
    }

    public function delete(object $entity)
    {
        if ($entity instanceof ProxyInterface) {
            $entity = $entity->__getRealEntity();
        }

        if (! isset($this->managedEntities[$entity])) {
            throw DomainException::fromUnmanagedEntity($entity);
        }

        $this->client->deleteRecord($this->layout, $this->managedEntities[$entity]['recordId']);
        unset($this->managedEntities[$entity]);
    }

    public function createEntity(array $record) : object
    {
        $recordId = (int) $record['recordId'];

        if (array_key_exists($recordId, $this->entitiesByRecordId)) {
            $entity = $this->entitiesByRecordId[$recordId];
        } else {
            $entity = $this->hydration->hydrateNewEntity($record, $this->client);
        }

        $this->addOrUpdateManagedEntity($recordId, (int) $record['modId'], $entity);
        return $entity;
    }

    private function addOrUpdateManagedEntity(int $recordId, int $modId, $entity) : void
    {
        $this->managedEntities[$entity] = [
            'recordId' => $recordId,
            'modId' => $modId,
        ];
        $this->entitiesByRecordId[$recordId] = $entity;
    }

    private function createCollection(array $records) : CollectionInterface
    {
        $entities = [];

        foreach ($records as $record) {
            $entities[] = $this->createEntity($record);
        }

        return new ItemCollection($entities);
    }

    private function createQuery(array $search, bool $autoQuoteSearch) : Query
    {
        $fields = [];

        foreach ($search as $field => $value) {
            $fields[] = new Field($field, $value, $autoQuoteSearch);
        }

        return new Query(
            new Conditions(false, ...$fields)
        );
    }

    private function createSortArguments(array $sort) : array
    {
        if (count($sort) > 9) {
            throw DomainException::fromTooManySortParameters(9, $sort);
        }

        $arguments = [];

        foreach ($sort as $field => $order) {
            $arguments[] = new Sort($field, 0 === stripos($order, 'a'));
        }

        return $arguments;
    }
}
