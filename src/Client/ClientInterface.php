<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client;

use Psr\Http\Message\StreamInterface;
use Soliant\SimpleFM\Layout\Layout;
use Soliant\SimpleFM\Query\Query;
use Soliant\SimpleFM\Sort\Sort;

interface ClientInterface
{
    public function getConnection() : Connection;

    public function createRecord(string $layout, array $data) : array;

    public function updateRecord(string $layout, int $recordId, array $data) : array;

    public function deleteRecord(string $layout, int $recordId) : void;

    public function getRecord(string $layout, int $recordId) : array;

    public function uploadContainerData(
        string $layout,
        int $recordId,
        string $data,
        string $fieldName,
        int $fieldRepetition = 0
    ) : void;

    public function getContainerData(string $url) : StreamInterface;

    public function find(
        string $layout,
        ?Query $query = null,
        ?int $offset = null,
        ?int $limit = null,
        Sort ...$sorts
    ) : array;

    public function getLayout(string $layout) : Layout;
}
