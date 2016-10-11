<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

final class ManyToOne
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
    private $targetTable;

    /**
     * @var string
     */
    private $targetEntity;

    /**
     * @var string
     */
    private $targetPropertyName;

    /**
     * @var string
     */
    private $targetFieldName;

    /**
     * @var bool
     */
    private $readOnly;

    public function __construct(
        string $fieldName,
        string $propertyName,
        string $targetTable,
        string $targetEntity,
        string $targetPropertyName,
        string $targetFieldName,
        bool $readOnly
    ) {
        $this->fieldName = $fieldName;
        $this->propertyName = $propertyName;
        $this->targetTable = $targetTable;
        $this->targetEntity = $targetEntity;
        $this->targetPropertyName = $targetPropertyName;
        $this->targetFieldName = $targetFieldName;
        $this->readOnly = $readOnly;
    }

    public function getFieldName() : string
    {
        return $this->fieldName;
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

    public function getTargetPropertyName() : string
    {
        return $this->targetPropertyName;
    }

    public function getTargetFieldName() : string
    {
        return $this->targetFieldName;
    }

    public function isReadOnly() : bool
    {
        return $this->readOnly;
    }
}
