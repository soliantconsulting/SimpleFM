<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\ResultSet\Transformer;

use DateTimeImmutable;
use DateTimeZone;
use Soliant\SimpleFM\Client\ResultSet\Transformer\Exception\DateTimeException;

final class TimeTransformer
{
    /**
     * @var DateTimeZone
     */
    private static $utcTimeZone;

    public function __invoke(string $value)
    {
        if ('' === $value) {
            return null;
        }

        $dateTime = DateTimeImmutable::createFromFormat(
            '!H:i:s',
            $value,
            self::$utcTimeZone ?: (self::$utcTimeZone = new DateTimeZone('UTC'))
        );

        if (false === $dateTime) {
            throw DateTimeException::fromDateTimeError($value, DateTimeImmutable::getLastErrors());
        }

        return $dateTime;
    }
}
