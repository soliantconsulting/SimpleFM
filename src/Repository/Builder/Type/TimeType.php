<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Assert\Assertion;
use DateTimeInterface;

final class TimeType implements TypeInterface
{
    public function fromFileMakerValue($value)
    {
        if (null === $value) {
            return null;
        }

        Assertion::isInstanceOf($value, DateTimeInterface::class);
        return $value;
    }

    public function toFileMakerValue($value)
    {
        if (null === $value) {
            return null;
        }

        Assertion::isInstanceOf($value, DateTimeInterface::class);
        return $value->format('H:i:s');
    }
}
