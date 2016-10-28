<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata\Exception;

use OutOfBoundsException;

final class InvalidTypeException extends OutOfBoundsException implements ExceptionInterface
{
    public static function fromNonExistentType(string $type) : self
    {
        return new self(sprintf('Type "%s" does not exist', $type));
    }
}
