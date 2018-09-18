<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository;

use Soliant\SimpleFM\Client\ClientInterface;

interface ExtractionInterface
{
    public function extract(object $entity, ClientInterface $client) : array;
}
