<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata;

use Soliant\SimpleFM\Repository\Builder\Type\TypeInterface;

final class Field
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
     * @var TypeInterface
     */
    private $type;

    /**
     * @var bool
     */
    private $repeatable;

    public function __construct(string $fieldName, string $propertyName, TypeInterface $type, bool $repeatable)
    {
        $this->fieldName = $fieldName;
        $this->propertyName = $propertyName;
        $this->type = $type;
        $this->repeatable = $repeatable;
    }

    public function getFieldName() : string
    {
        return $this->fieldName;
    }

    public function getPropertyName() : string
    {
        return $this->propertyName;
    }

    public function getType() : TypeInterface
    {
        return $this->type;
    }

    public function isRepeatable() : bool
    {
        return $this->repeatable;
    }
}
