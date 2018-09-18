<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Type\Exception;

use DomainException;

final class ConversionException extends DomainException implements ExceptionInterface
{
    public static function fromAttemptedStreamConversionToFileMakerValue() : self
    {
        return new self('Attempted conversion to FileMaker value was discovered, but is disallowed');
    }

    public static function fromInvalidType($value, string $expectedType) : self
    {
        return new self(sprintf(
            'Value of type "%s" cannot be converted, expected type was "%s"',
            is_object($value) ? get_class($value) : gettype($value),
            $expectedType
        ));
    }

    public static function fromUnexpectedValue($value) : self
    {
        return new self(sprintf(
            'Value "%s" was not expected',
            $value
        ));
    }
}
