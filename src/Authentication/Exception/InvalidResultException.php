<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Authentication\Exception;

use RuntimeException;

final class InvalidResultException extends RuntimeException implements ExceptionInterface
{
    public static function fromEmptyResultSet() : self
    {
        return new self('Empty result set received');
    }
}
