<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Type\Exception;

use DomainException as PhpDomainException;

final class DomainException extends PhpDomainException implements ExceptionInterface
{
    public static function fromAttemptedStreamConversionToFileMakerValue() : self
    {
        return new self('Attempted conversion to file maker value was discovered, but is disallowed');
    }
}
