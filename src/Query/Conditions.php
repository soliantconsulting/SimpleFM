<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Query;

final class Conditions
{
    /**
     * @var bool
     */
    private $omit;

    /**
     * @var Field[]
     */
    private $fields;

    public function __construct(bool $omit, Field $firstField, Field ...$additionalFields)
    {
        $this->omit = $omit;
        $this->fields = array_merge([$firstField], $additionalFields);
    }

    public function withAdditionalField(Field $additionalField) : self
    {
        return new self($this->omit, ...array_merge($this->fields, [$additionalField]));
    }

    public function isOmit() : bool
    {
        return $this->omit;
    }

    /**
     * @return Field[]
     */
    public function getFields() : array
    {
        return $this->fields;
    }
}
