<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Assert\Assertion;
use Litipk\BigNumbers\Decimal;

final class FloatType implements TypeInterface
{
    public function fromFileMakerValue($value)
    {
        Assertion::isInstanceOf($value, Decimal::class);
        return $value->asFloat();
    }

    public function toFileMakerValue($value)
    {
        Assertion::float($value);
        return Decimal::fromFloat($value);
    }
}
