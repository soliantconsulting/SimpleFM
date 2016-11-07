<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata\Exception;

use DomainException;

final class MissingInterfaceException extends DomainException implements ExceptionInterface
{
    public static function fromMissingInterface(string $mainEntityClassName, string $relationEntityClassName) : self
    {
        return new self(sprintf(
            'Relation on entity "%1$s" to entity "%2$s" requires the interface-name to be specified in the "%2$s"'
            . ' entity-metadata',
            $mainEntityClassName,
            $relationEntityClassName
        ));
    }
}
