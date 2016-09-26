<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Client\Layout;

use Assert\Assertion;

final class Layout
{
    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Field[]
     */
    private $fields;

    public function __construct(string $database, string $name, Field ...$fields)
    {
        $this->database = $database;
        $this->name = $name;
        $this->fields = $fields;
    }

    public function getDatabase() : string
    {
        return $this->database;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return Field[]
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    public function hasField(string $name) : bool
    {
        return (bool) array_filter($this->fields, function (Field $field) use ($name) : bool {
            return $field->getName() === $name;
        });
    }

    public function getField(string $name) : Field
    {
        $fields = array_filter($this->fields, function (Field $field) use ($name) : bool {
            return $field->getName() === $name;
        });

        Assertion::notEmpty($fields);
        return reset($fields);
    }
}
