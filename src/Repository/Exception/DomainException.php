<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Exception;

use DomainException as PhpDomainException;

final class DomainException extends PhpDomainException implements ExceptionInterface
{
    public static function fromMissingIdentityHandler() : self
    {
        return new self('An identity handler must be present to use this feature');
    }

    public static function fromUnmanagedEntity($entity) : self
    {
        return new self(sprintf('Entity with ID %s is not managed by the gateway', spl_object_hash($entity)));
    }

    public static function fromTooManySortParameters(int $allowed, array $sort)
    {
        return new self(sprintf(
            'There cannot be more than %d sort parameters, %d supplied',
            $allowed,
            count($sort)
        ));
    }
}
