<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Client\Layout;

final class Value
{
    /**
     * @var string
     */
    private $display;

    /**
     * @var string
     */
    private $value;

    public function __construct(string $display, string $value)
    {
        $this->display = $display;
        $this->value = $value;
    }

    public function getDisplay() : string
    {
        return $this->display;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function __toString() : string
    {
        return $this->display;
    }
}
