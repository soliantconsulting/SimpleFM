<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Query\Exception;

use InvalidArgumentException as PhpInvalidArgumentException;

final class InvalidArgumentException extends PhpInvalidArgumentException implements ExceptionInterface
{
    public static function fromEmptyQueryParameters() : self
    {
        return new self('Query parameters cannot be empty');
    }
}
