<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\ResultSet\Exception;

use DomainException;
use Exception;

final class ParseException extends DomainException implements ExceptionInterface
{
    public static function fromInvalidFieldType(string $name, string $type) : self
    {
        return new self(sprintf('Invalid field type "%s" for field "%s" discovered', $type, $name));
    }

    public static function fromDeletedField()
    {
        return new self('A field has been deleted from the table, but remained in the layout');
    }

    public static function fromConcreteException(
        string $database,
        string $table,
        string $layout,
        Exception $previousException
    ) : self {
        return new self(sprintf(
            'Could not parse response from database "%s" with table "%s" and layout "%s", reason: %s',
            $database,
            $table,
            $layout,
            $previousException->getMessage()
        ), 0, $previousException);
    }
}
