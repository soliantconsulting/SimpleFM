<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata\Exception;

use DomainException;

final class InvalidCollectionException extends DomainException implements ExceptionInterface
{
    public static function fromUnexpectedValueInCollection(string $expectedClassName) : self
    {
        return new self(sprintf('At least one element in the collection is not an instance of %s', $expectedClassName));
    }
}
