<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Query;

final class Query
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $value;

    /**
     * @var bool
     */
    private $exclude;

    public function __construct(string $fieldName, string $value, bool $exclude = false)
    {
        $this->fieldName = $fieldName;
        $this->value = $value;
        $this->exclude = $exclude;
    }

    public function getFieldName() : string
    {
        return $this->fieldName;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function isExclude() : bool
    {
        return $this->exclude;
    }
}
