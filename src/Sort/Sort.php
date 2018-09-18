<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Sort;

final class Sort
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var bool
     */
    private $ascending;

    public function __construct(string $fieldName, bool $ascending)
    {
        $this->fieldName = $fieldName;
        $this->ascending = $ascending;
    }

    public function getFieldName() : string
    {
        return $this->fieldName;
    }

    public function isAscending() : bool
    {
        return $this->ascending;
    }
}
