<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

use Assert\Assertion;

final class OneToOne
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var string
     */
    private $targetTable;

    /**
     * @var string
     */
    private $targetEntity;

    /**
     * @var bool
     */
    private $isOwningSide;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $targetPropertyName;

    public function __construct(
        string $propertyName,
        string $targetTable,
        string $targetEntity,
        bool $isOwningSide,
        string $fieldName = null,
        string $targetPropertyName = null
    ) {
        if ($isOwningSide) {
            Assertion::notNull($fieldName);
            Assertion::notNull($targetPropertyName);
        }

        $this->propertyName = $propertyName;
        $this->targetTable = $targetTable;
        $this->targetEntity = $targetEntity;
        $this->isOwningSide = $isOwningSide;
        $this->fieldName = $isOwningSide ? $fieldName : null;
        $this->targetPropertyName = $isOwningSide ? $targetPropertyName : null;
    }

    public function getPropertyName() : string
    {
        return $this->propertyName;
    }

    public function getTargetTable() : string
    {
        return $this->targetTable;
    }

    public function getTargetEntity() : string
    {
        return $this->targetEntity;
    }

    public function isOwningSide() : bool
    {
        return $this->isOwningSide;
    }

    public function getFieldName() : string
    {
        Assertion::notNull($this->fieldName);
        return $this->fieldName;
    }

    public function getTargetPropertyName() : string
    {
        Assertion::notNull($this->targetPropertyName);
        return $this->targetPropertyName;
    }
}
