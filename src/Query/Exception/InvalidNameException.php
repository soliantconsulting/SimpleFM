<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Query\Exception;

use RuntimeException;

final class InvalidNameException extends RuntimeException implements ExceptionInterface
{
    public static function fromReservedKeyword(string $keyword) : self
    {
        return new self(sprintf('Keyword "%s" is reserved and cannot be searched upon', $keyword));
    }
}
