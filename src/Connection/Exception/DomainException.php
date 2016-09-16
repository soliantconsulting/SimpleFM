<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Connection\Exception;

use DomainException as PhpDomainException;

final class DomainException extends PhpDomainException implements ExceptionInterface
{
    public static function fromDisallowedParameter(string $parameterName) : self
    {
        return new self(sprintf('The parameter "%s" is not allowed to be included', $parameterName));
    }

    public static function fromInvalidValue($value) : self
    {
        return new self(sprintf(
            'Parameter values must either be scalar, null or implement DateTimeInterface, received %s',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
}
