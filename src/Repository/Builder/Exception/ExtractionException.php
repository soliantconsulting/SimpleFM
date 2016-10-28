<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Exception;

use Exception;
use RuntimeException;
use Soliant\SimpleFM\Repository\Builder\Metadata\Entity;
use Soliant\SimpleFM\Repository\Builder\Metadata\Field;

final class ExtractionException extends RuntimeException implements ExceptionInterface
{
    public static function fromInvalidField(
        Entity $entityMetadata,
        Field $fieldMetadata,
        Exception $previousException
    ) : self {
        return new self(sprintf(
            'Could not extract field "%s" from entity "%s", reason: %s',
            $fieldMetadata->getPropertyName(),
            $entityMetadata->getClassName(),
            $previousException->getMessage()
        ), 0, $previousException);
    }
}
