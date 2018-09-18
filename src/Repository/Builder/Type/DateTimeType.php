<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use DateTimeImmutable;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;

final class DateTimeType implements TypeInterface
{
    public function fromFileMakerValue($value, ClientInterface $client)
    {
        if (! is_string($value)) {
            throw ConversionException::fromInvalidType($value, 'string');
        }

        if ('' === $value) {
            return null;
        }

        $dateTime = DateTimeImmutable::createFromFormat('m/d/Y H:i:s', $value, $client->getConnection()->getTimeZone());

        if (false === $dateTime) {
            throw ConversionException::fromUnexpectedValue($value);
        }

        return $dateTime;
    }

    public function toFileMakerValue($value, ClientInterface $client)
    {
        if (null === $value) {
            return '';
        }

        if (! $value instanceof DateTimeImmutable) {
            throw ConversionException::fromInvalidType($value, DateTimeImmutable::class);
        }

        return $value->setTimezone($client->getConnection()->getTimeZone())->format('m/d/Y H:i:s');
    }
}
