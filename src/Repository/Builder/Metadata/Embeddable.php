<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

final class Embeddable
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var string
     */
    private $fieldNamePrefix;

    /**
     * @var Entity
     */
    private $metadata;

    public function __construct(
        string $propertyName,
        string $fieldNamePrefix,
        Entity $metadata
    ) {
        $this->propertyName = $propertyName;
        $this->fieldNamePrefix = $fieldNamePrefix;
        $this->metadata = $metadata;
    }

    public function getPropertyName() : string
    {
        return $this->propertyName;
    }

    public function getFieldNamePrefix() : string
    {
        return $this->fieldNamePrefix;
    }

    public function getMetadata() : Entity
    {
        return $this->metadata;
    }
}
