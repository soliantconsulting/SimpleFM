<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Assert\Assertion;
use Psr\Http\Message\StreamInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\DomainException;

final class StreamType implements TypeInterface
{
    public function fromFileMakerValue($value)
    {
        if (null === $value) {
            return null;
        }

        Assertion::isInstanceOf($value, StreamInterface::class);
        return $value;
    }

    public function toFileMakerValue($value)
    {
        throw DomainException::fromAttemptedStreamConversionToFileMakerValue();
    }
}
