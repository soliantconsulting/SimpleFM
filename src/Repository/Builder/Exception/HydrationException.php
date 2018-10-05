<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Exception;

use Exception;
use RuntimeException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\Field;

final class HydrationException extends RuntimeException implements ExceptionInterface
{
    public static function fromInvalidField(
        Entity $entityMetadata,
        Field $fieldMetadata,
        Exception $previousException
    ) : self {
        return new self(sprintf(
            'Could not hydrate field "%s" for entity "%s", reason: %s',
            $fieldMetadata->getPropertyName(),
            $entityMetadata->getClassName(),
            $previousException->getMessage()
        ), 0, $previousException);
    }

    public static function fromMissingField(string $fieldName) : self
    {
        return new self(sprintf('Source data have no field named "%s"', $fieldName));
    }

    public static function fromEntityMismatch(string $className) : self
    {
        return new self(sprintf('Entity is not an instance of "%s"', $className));
    }

    public static function fromNonArrayRepeatable(string $propertyName) : self
    {
        return new self(sprintf('Property value of "%s" is not an array', $propertyName));
    }

    public static function fromMissingInterface(string $className) : self
    {
        return new self(sprintf('Entity "%s" has no interface name defined', $className));
    }
}
