<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository;

use Soliant\SimpleFM\Client\ClientInterface;

interface HydrationInterface
{
    public function hydrateNewEntity(array $data, ClientInterface $client) : object;

    public function hydrateExistingEntity(array $data, object $entity, ClientInterface $client) : object;
}
