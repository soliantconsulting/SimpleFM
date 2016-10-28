<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Query\Exception;

use DomainException;

final class EmptyQueryException extends DomainException implements ExceptionInterface
{
    public static function fromEmptyQueryArray() : self
    {
        return new self('Find query requires at least one query');
    }
}
