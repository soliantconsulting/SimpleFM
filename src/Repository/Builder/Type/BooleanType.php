<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;

final class BooleanType implements TypeInterface
{
    public function fromFileMakerValue($value, ClientInterface $client)
    {
        if (null === $value) {
            return false;
        }

        if (is_int($value)) {
            return 0 !== $value;
        }

        if (is_string($value)) {
            return $value !== '0' && $value !== '';
        }

        return true;
    }

    public function toFileMakerValue($value, ClientInterface $client)
    {
        if (! is_bool($value)) {
            throw ConversionException::fromInvalidType($value, 'bool');
        }

        return $value ? 1 : 0;
    }
}
