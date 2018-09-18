<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Query;

final class Query
{
    /**
     * @var Conditions[]
     */
    private $orConditions;

    public function __construct(Conditions $firstConditions, Conditions ...$orConditions)
    {
        $this->orConditions = array_merge([$firstConditions], $orConditions);
    }

    public function orWith(Conditions $conditions) : self
    {
        return new self(...array_merge($this->orConditions, [$conditions]));
    }

    /**
     * @return Conditions[]
     */
    public function getOrConditions() : array
    {
        return $this->orConditions;
    }
}
