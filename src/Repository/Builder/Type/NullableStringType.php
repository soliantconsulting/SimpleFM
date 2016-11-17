<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Assert\Assertion;

final class NullableStringType implements TypeInterface
{
    public function fromFileMakerValue($value)
    {
        Assertion::string($value);
        return '' === $value ? null : $value;
    }

    public function toFileMakerValue($value)
    {
        if (null === $value) {
            $value = '';
        }

        Assertion::string($value);
        return $value;
    }
}
