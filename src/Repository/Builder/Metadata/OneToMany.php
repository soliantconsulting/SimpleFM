<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

final class OneToMany
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var string
     */
    private $targetEntity;

    public function __construct(string $fieldName, string $propertyName, string $targetEntity)
    {
        $this->fieldName = $fieldName;
        $this->propertyName = $propertyName;
        $this->targetEntity = $targetEntity;
    }

    public function getFieldName() : string
    {
        return $this->fieldName;
    }

    public function getPropertyName() : string
    {
        return $this->propertyName;
    }

    public function getTargetEntity() : string
    {
        return $this->targetEntity;
    }
}
