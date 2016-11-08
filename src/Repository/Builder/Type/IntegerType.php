<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Assert\Assertion;
use Litipk\BigNumbers\Decimal;

final class IntegerType implements TypeInterface
{
    public function fromFileMakerValue($value)
    {
        if (null === $value) {
            return null;
        }

        Assertion::isInstanceOf($value, Decimal::class);
        return $value->asInteger();
    }

    public function toFileMakerValue($value)
    {
        if (null === $value) {
            return null;
        }

        Assertion::integer($value);
        return Decimal::fromInteger($value);
    }
}
