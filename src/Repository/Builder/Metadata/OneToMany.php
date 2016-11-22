<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

final class OneToMany
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
        bool $eagerHydration = false
    ) {
        $this->propertyName = $propertyName;
        $this->targetTable = $targetTable;
        $this->targetEntity = $targetEntity;
        $this->targetFieldName = $targetFieldName;
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

    public function hasEagerHydration() : bool
    {
        return $this->eagerHydration;
    }
}
