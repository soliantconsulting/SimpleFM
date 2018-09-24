<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\Exception;

use LibXMLError;
use RuntimeException;

final class FileMakerException extends RuntimeException implements ExceptionInterface
{
    public static function fromMessages(array $messages) : self
    {
        $firstMessage = $messages[0];
        return new self($firstMessage['message'], (int) ($firstMessage['code'] ?? -1));
    }

    public static function fromXmlErrorCode(int $errorCode) : self
    {
        return new self(sprintf('The XML api returned with an error code of %d', $errorCode));
    }

    public static function fromXmlError(?LibXMLError $error) : self
    {
        if (null === $error) {
            return new self('An unknown error occured while parsing response XML');
        }

        return new self(sprintf(
            'An error occurred (%d) while parsing response XML: %s',
            $error->code,
            $error->message
        ));
    }

    public static function fromUnknown() : self
    {
        return new self('An unknown error occurred');
    }

    public static function fromUnreachableXmlApi() : self
    {
        return new self('Cannot reach XML API, are you sure it is enabled?');
    }
}
