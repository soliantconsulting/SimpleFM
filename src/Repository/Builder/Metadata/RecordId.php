<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

final class RecordId
{
    /**
     * @var string
     */
    private $propertyName;

    public function __construct(string $propertyName)
    {
        $this->propertyName = $propertyName;
    }

    public function getPropertyName() : string
    {
        return $this->propertyName;
    }
}
