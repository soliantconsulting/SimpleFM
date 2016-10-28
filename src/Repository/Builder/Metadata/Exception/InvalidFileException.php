<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Metadata\Exception;

use RuntimeException;

final class InvalidFileException extends RuntimeException implements ExceptionInterface
{
    public static function fromNonExistentFile(string $path, string $entityClassName) : self
    {
        return new self(sprintf('File "%s" for entity "%s" does not exist', $path, $entityClassName));
    }

    public static function fromInvalidFile(string $path) : self
    {
        return new self(sprintf('File "%s" is not valid', $path));
    }
}
