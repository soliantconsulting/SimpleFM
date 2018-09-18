<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Proxy\Exception;

use DomainException;

final class InvalidEntityException extends DomainException implements ExceptionInterface
{
    public static function fromInvalidEntity($entity, string $expectedClassName) : self
    {
        return new self(sprintf(
            'Entity of type "%s" received, but an instance of "%s" was expected',
            is_object($entity) ? get_class($entity) : gettype($entity),
            $expectedClassName
        ));
    }
}
