<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use DateTimeImmutable;
use DateTimeZone;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;

final class TimeType implements TypeInterface
{
    /**
     * @var DateTimeZone
     */
    private static $utcTimeZone;

    public function fromFileMakerValue($value, ClientInterface $client)
    {
        if ('' === $value) {
            return null;
        }

        if (! is_string($value)) {
            throw ConversionException::fromInvalidType($value, 'string');
        }

        $dateTime = DateTimeImmutable::createFromFormat(
            '!H:i:s',
            $value,
            self::$utcTimeZone ?: self::$utcTimeZone = new DateTimeZone('UTC')
        );

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

        return $value->format('H:i:s');
    }
}
