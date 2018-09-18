<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata\Exception;

use DomainException;

final class RelationException extends DomainException implements ExceptionInterface
{
    public static function fromOwningSide() : self
    {
        return new self('Relation is the owning side and thus must have a field name and target property name');
    }

    public static function fromInverseSide() : self
    {
        return new self('Relation is the inverse side');
    }
}
