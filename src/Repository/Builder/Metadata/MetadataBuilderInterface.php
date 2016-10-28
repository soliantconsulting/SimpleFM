<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

interface MetadataBuilderInterface
{
    public function getMetadata(string $entityClassName) : Entity;
}
