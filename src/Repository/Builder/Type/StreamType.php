<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;

final class StreamType implements TypeInterface
{
    public function fromFileMakerValue($value, ClientInterface $client)
    {
        if ('' === $value) {
            return null;
        }

        if (! is_string($value)) {
            throw ConversionException::fromInvalidType($value, 'string');
        }

        return new StreamProxy($client, $value);
    }

    public function toFileMakerValue($value, ClientInterface $client)
    {
        throw ConversionException::fromAttemptedStreamConversionToFileMakerValue();
    }
}
