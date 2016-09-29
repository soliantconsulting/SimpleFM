<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\ResultSet\Transformer\Exception;

use DomainException;

final class DecimalException extends DomainException implements ExceptionInterface
{
    public static function fromInvalidString(string $string) : self
    {
        return new self(sprintf('"%s" must be a string that represents uniquely a float point number.', $string));
    }
}
