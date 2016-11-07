<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata\Exception;

use DomainException;

final class MissingInterfaceException extends DomainException implements ExceptionInterface
{
    public static function fromMissingInterface(string $mainEntityClassName, string $relationEntityClassName) : self
    {
        return new self(sprintf(
            'Relation on entity "%s" to entity "%s" requires the latter to have an interface specified, bus is not',
            $mainEntityClassName,
            $relationEntityClassName
        ));
    }
}
