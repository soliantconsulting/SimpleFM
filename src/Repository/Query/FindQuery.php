<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Query;

use Soliant\SimpleFM\Repository\Query\Exception\EmptyQueryException;
use Soliant\SimpleFM\Repository\Query\Exception\InvalidArgumentException;

final class FindQuery
{
    /**
     * @var Query|Query[]
     */
    private $queries = [];

    public function addOrQueries(Query ...$queries)
    {
        if (empty($queries)) {
            throw InvalidArgumentException::fromEmptyQueryParameters();
        }

        $this->queries += $queries;
    }

    public function addAndQueries(Query ...$queries)
    {
        if (empty($queries)) {
            throw InvalidArgumentException::fromEmptyQueryParameters();
        }

        $this->queries[] = $queries;
    }

    public function toParameters() : array
    {
        if (empty($this->queries)) {
            throw EmptyQueryException::fromEmptyQueryArray();
        }

        $parameters = [
            '-query' => $this->buildQueryParameter(),
        ];

        $index = 0;

        foreach ($this->queries as $query) {
            if ($query instanceof Query) {
                $parameters[sprintf('q%d', ++$index)] = $query->getFieldName();
                $parameters[sprintf('q%d.value', $index)] = $query->getValue();
                continue;
            }

            foreach ($query as $andQuery) {
                $parameters[sprintf('q%d', ++$index)] = $andQuery->getFieldName();
                $parameters[sprintf('q%d.value', $index)] = $andQuery->getValue();
                continue;
            }
        }

        return $parameters;
    }

    private function buildQueryParameter() : string
    {
        $index = 0;
        $orQueries = [];

        foreach ($this->queries as $query) {
            if ($query instanceof Query) {
                $orQueries[] = sprintf('%sq%d', $query->isExclude() ? '!' : '', ++$index);
                continue;
            }

            $andQueries = [];

            foreach ($query as $andQuery) {
                $andQueries[] = sprintf('%sq%d', $andQuery->isExclude() ? '!' : '', ++$index);
            }

            $orQueries[] = implode(',', $andQueries);
        }

        return '(' . implode(');(', $orQueries) . ')';
    }
}
