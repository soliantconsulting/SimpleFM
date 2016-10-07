<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

final class Embeddable
{
    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $fieldNamePrefix;

    /**
     * @var Entity
     */
    private $metadata;

    public function __construct(
        string $property,
        string $fieldNamePrefix,
        Entity $metadata
    ) {
        $this->property = $property;
        $this->fieldNamePrefix = $fieldNamePrefix;
        $this->metadata = $metadata;
    }

    public function getProperty() : string
    {
        return $this->property;
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
