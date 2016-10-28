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
     * @var bool
     */
    private $readOnly;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $targetPropertyName;

    /**
     * @var string
     */
    private $targetFieldName;

    public function __construct(
        string $propertyName,
        string $targetTable,
        string $targetEntity,
        string $targetFieldName,
        bool $isOwningSide,
        bool $readOnly,
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
        $this->targetFieldName = $targetFieldName;
        $this->isOwningSide = $isOwningSide;
        $this->readOnly = $isOwningSide ? $readOnly : true;
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

    public function getTargetFieldName() : string
    {
        return $this->targetFieldName;
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

    public function isReadOnly() : bool
    {
        return $this->readOnly;
    }
}
