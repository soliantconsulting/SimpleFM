<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Assert\Assertion;
use Litipk\BigNumbers\Decimal;

final class BooleanType implements TypeInterface
{
    public function fromFileMakerValue($value)
    {
        if (null === $value) {
            return false;
        }

        if ($value instanceof Decimal) {
            return 0 !== $value->comp(Decimal::fromInteger(0));
        }

        if (is_string($value)) {
            return $value !== '0' && $value !== '';
        }

        return true;
    }

    public function toFileMakerValue($value)
    {
        Assertion::boolean($value);
        return Decimal::fromInteger($value ? 1 : 0);
    }
}
