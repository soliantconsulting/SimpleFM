<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Client\Layout;

use Assert\Assertion;

final class Field
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var ValueList|null
     */
    private $valueList;

    public function __construct(string $name, string $type, ValueList $valueList = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->valueList = $valueList;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function hasValueList() : bool
    {
        return null !== $this->valueList;
    }

    public function getValueList() : ValueList
    {
        Assertion::notNull($this->valueList);
        return $this->valueList;
    }
}
