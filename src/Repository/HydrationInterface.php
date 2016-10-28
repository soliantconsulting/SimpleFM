<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository;

interface HydrationInterface
{
    public function hydrateNewEntity(array $data);

    public function hydrateExistingEntity(array $data, $entity);
}
