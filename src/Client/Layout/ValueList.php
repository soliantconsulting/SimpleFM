<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Client\Layout;

final class ValueList
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Value[]
     */
    private $values;

    public function __construct(string$name, Value ...$values)
    {
        $this->name = $name;
        $this->values = $values;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return Value[]
     */
    public function getValues() : array
    {
        return $this->values;
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
