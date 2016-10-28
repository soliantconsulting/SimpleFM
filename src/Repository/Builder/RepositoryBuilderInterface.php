<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder;

use Soliant\SimpleFM\Repository\RepositoryInterface;

interface RepositoryBuilderInterface
{
    public function buildRepository(string $entityClassName) : RepositoryInterface;
}
