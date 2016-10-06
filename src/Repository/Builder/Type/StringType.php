<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Assert\Assertion;

final class StringType implements TypeInterface
{
    public function fromFileMakerValue($value)
    {
        Assertion::string($value);
        return $value;
    }

    public function toFileMakerValue($value)
    {
        Assertion::string($value);
        return $value;
    }
}
