<?php
declare(strict_types = 1);

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
     * @var string
     */
    private $targetInterfaceName;

    /**
     * @var bool
     */
    private $owningSide;

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

    /**
     * @var bool
     */
    private $eagerHydration;

    public function __construct(
        string $propertyName,
        string $targetTable,
        string $targetEntity,
        string $targetFieldName,
        string $targetInterfaceName,
        bool $owningSide,
        bool $readOnly,
        string $fieldName = null,
        string $targetPropertyName = null,
        bool $eagerHydration = false
    ) {
        if ($owningSide) {
            Assertion::notNull($fieldName);
            Assertion::notNull($targetPropertyName);
        }

        $this->propertyName = $propertyName;
        $this->targetTable = $targetTable;
        $this->targetEntity = $targetEntity;
        $this->targetFieldName = $targetFieldName;
        $this->targetInterfaceName = $targetInterfaceName;
        $this->owningSide = $owningSide;
        $this->readOnly = $owningSide ? $readOnly : true;
        $this->fieldName = $owningSide ? $fieldName : null;
        $this->targetPropertyName = $owningSide ? $targetPropertyName : null;
        $this->eagerHydration = $eagerHydration;
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

    public function getTargetInterfaceName() : string
    {
        return $this->targetInterfaceName;
    }

    public function isOwningSide() : bool
    {
        return $this->owningSide;
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

    public function hasEagerHydration() : bool
    {
        return $this->eagerHydration;
    }
}
