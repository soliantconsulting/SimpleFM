<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\ResultSet\Exception;

use DomainException;
use Exception;

final class UnknownFieldException extends DomainException implements ExceptionInterface
{
    public static function fromUnknownField()
    {
        return new self(
            'A field definition result is "unknown". This is normally due to a field on the layout having being '
            . 'deleted from the table or the authenticating user not having permission to view it.'
        );
    }

    public static function fromConcreteException(
        string $database,
        string $table,
        string $layout,
        Exception $previousException
    ) : self {
        return new self(sprintf(
            'Unknown field in database "%s" with table "%s" and layout "%s". Reason: %s',
            $database,
            $table,
            $layout,
            $previousException->getMessage()
        ), 0, $previousException);
    }
}
