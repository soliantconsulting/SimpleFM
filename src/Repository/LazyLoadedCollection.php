<?php
declare(strict_types=1);

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
     * @var array
     */
    private $recordIds;

    /**
     * @var ArrayIterator
     */
    private $iterator;

    public function __construct(RepositoryInterface $repository, array $recordIds)
    {
        $this->repository = $repository;
        $this->recordIds = $recordIds;
    }

    public function getIterator() : Traversable
    {
        if (null === $this->iterator) {
            $findQuery = new FindQuery();
            $findQuery->addOrQueries(...array_map(function (int $recordId) {
                return new Query('record-id', (string) $recordId);
            }, $this->recordIds));

            $this->iterator = new ArrayIterator($this->repository->findByQuery($findQuery));
        }

        return $this->iterator;
    }

    public function first()
    {
        $iterator = $this->getIterator();
        return reset($iterator);
    }

    public function count() : int
    {
        return count($this->recordIds);
    }
}
