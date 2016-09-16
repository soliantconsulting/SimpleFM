<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository;

interface ExtractionInterface
{
    public function extract($entity) : array;
}
