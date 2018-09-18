<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;

final class NullableStringType implements TypeInterface
{
    public function fromFileMakerValue($value, ClientInterface $client)
    {
        if (! is_string($value)) {
            throw ConversionException::fromInvalidType($value, 'string');
        }

        return '' === $value ? null : $value;
    }

    public function toFileMakerValue($value, ClientInterface $client)
    {
        if (null === $value) {
            return '';
        }

        if (! is_string($value)) {
            throw ConversionException::fromInvalidType($value, 'string');
        }

        return $value;
    }
}
