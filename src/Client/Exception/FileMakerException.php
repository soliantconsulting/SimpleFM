<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\Exception;

use RuntimeException;

final class FileMakerException extends RuntimeException implements ExceptionInterface
{
    public static function fromMessages(array $messages) : self
    {
        $firstMessage = $messages[0];
        return new self($firstMessage['message'], (int) ($firstMessage['code'] ?? -1));
    }

    public static function fromUnknown() : self
    {
        return new self('An unknown error occurred');
    }
}
