<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Client\ResultSet\Exception;

use DomainException;

final class ParseException extends DomainException implements ExceptionInterface
{
    public static function fromInvalidFieldType(string $type) : self
    {
        return new self(sprintf('Invalid field type "%s" discovered', $type));
    }
}
