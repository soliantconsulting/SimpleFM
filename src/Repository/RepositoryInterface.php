<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository;

use Soliant\SimpleFM\Authentication\Identity;
use Soliant\SimpleFM\Collection\CollectionInterface;
use Soliant\SimpleFM\Repository\Query\FindQuery;

interface RepositoryInterface
{
    public function withIdentity(Identity $identity) : self;

    public function find(int $recordId);

    public function findOneBy(array $search, bool $autoQuoteSearch = true);

    public function findOneByQuery(FindQuery $query);

    public function findAll(array $sort = [], int $limit = null, int $offset = null) : CollectionInterface;

    public function findBy(
        array $search,
        array $sort = [],
        int $limit = null,
        int $offset =
        null,
        bool $autoQuoteSearch = true
    ) : CollectionInterface;

    public function findByQuery(
        FindQuery $findQuery,
        array $sort = [],
        int $limit = null,
        int $offset = null
    ) : CollectionInterface;

    public function insert($entity);

    public function update($entity, bool $force = false);

    public function delete($entity, bool $force = false);

    public function quoteString(string $string) : string;

    public function createEntity(array $record);
}
