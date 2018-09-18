<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository;

use Soliant\SimpleFM\Collection\CollectionInterface;
use Soliant\SimpleFM\Query\Query;

interface RepositoryInterface
{
    public function find(int $recordId) : ?object;

    public function findOneBy(array $search, bool $autoQuoteSearch = true) : ?object;

    public function findOneByQuery(Query $query) : ?object;

    public function findAll(array $sort = [], int $limit = null, int $offset = null) : CollectionInterface;

    public function findBy(
        array $search,
        array $sort = [],
        int $limit = null,
        int $offset = null,
        bool $autoQuoteSearch = true
    ) : CollectionInterface;

    public function findByQuery(
        Query $query,
        array $sort = [],
        int $limit = null,
        int $offset = null
    ) : CollectionInterface;

    public function insert(object $entity);

    public function update(object $entity);

    public function delete(object $entity);

    public function createEntity(array $record) : object;
}
