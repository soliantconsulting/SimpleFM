<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

use Assert\Assertion;

final class OneToOne
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

    /**
     * @var bool
     */
    private $isOwningSide;

    /**
     * @var string
     */
    private $targetPropertyName;

    public function __construct(
        string $fieldName,
        string $propertyName,
        string $targetEntity,
        bool $isOwningSide,
        string $targetPropertyName = null
    ) {
        if ($isOwningSide) {
            Assertion::notNull($targetPropertyName);
        }

        $this->fieldName = $fieldName;
        $this->propertyName = $propertyName;
        $this->targetEntity = $targetEntity;
        $this->isOwningSide = $isOwningSide;
        $this->targetPropertyName = $isOwningSide ? $targetPropertyName : null;
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

    public function isOwningSide() : bool
    {
        return $this->isOwningSide;
    }

    public function getTargetPropertyName() : string
    {
        Assertion::notNull($this->targetPropertyName);
        return $this->targetPropertyName;
    }
}
