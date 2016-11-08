<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Proxy\Exception;

use DomainException;

final class InvalidInterfaceException extends DomainException implements ExceptionInterface
{
    public static function fromInvalidInterface(string $interfaceName) : self
    {
        return new self(sprintf(
            '"%s" was expected to be an interface, but is not',
            $interfaceName
        ));
    }
}
