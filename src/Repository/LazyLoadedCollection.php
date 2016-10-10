<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Soliant\SimpleFM\Repository\Query\FindQuery;
use Soliant\SimpleFM\Repository\Query\Query;
use Traversable;

final class LazyLoadedCollection implements IteratorAggregate, Countable
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var string
     */
    private $idFieldName;

    /**
     * @var array
     */
    private $ids;

    /**
     * @var ArrayIterator
     */
    private $iterator;

    public function __construct(RepositoryInterface $repository, string $idFieldName, array $ids)
    {
        $this->repository = $repository;
        $this->idFieldName = $idFieldName;
        $this->ids = $ids;
    }

    public function getIterator() : Traversable
    {
        if (null !== $this->iterator) {
            return $this->iterator;
        }

        if (empty($this->ids)) {
            return $this->iterator = new ArrayIterator();
        }

        $findQuery = new FindQuery();
        $findQuery->addOrQueries(...array_map(function ($id) {
            return new Query($this->idFieldName, (string) $id);
        }, $this->ids));

        return $this->iterator = new ArrayIterator($this->repository->findByQuery($findQuery));
    }

    public function first()
    {
        $iterator = $this->getIterator();
        return reset($iterator) ?: null;
    }

    public function count() : int
    {
        return count($this->ids);
    }
}
