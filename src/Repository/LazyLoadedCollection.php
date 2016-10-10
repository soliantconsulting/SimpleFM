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
    private $sparseRecords;

    /**
     * @var ArrayIterator
     */
    private $iterator;

    public function __construct(RepositoryInterface $repository, string $idFieldName, array $sparseRecords)
    {
        $this->repository = $repository;
        $this->idFieldName = $idFieldName;
        $this->sparseRecords = $sparseRecords;
    }

    public function getIterator() : Traversable
    {
        if (null !== $this->iterator) {
            return $this->iterator;
        }

        if (empty($this->sparseRecords)) {
            return $this->iterator = new ArrayIterator();
        }

        $findQuery = new FindQuery();
        $findQuery->addOrQueries(...array_map(function ($sparseRecord) {
            return new Query($this->idFieldName, (string) $sparseRecord[$this->idFieldName]);
        }, $this->sparseRecords));

        return $this->iterator = new ArrayIterator($this->repository->findByQuery($findQuery));
    }

    public function first()
    {
        $iterator = $this->getIterator();
        return reset($iterator) ?: null;
    }

    public function count() : int
    {
        return count($this->sparseRecords);
    }
}
