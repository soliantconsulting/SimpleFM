<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Client\ResultSet\Transformer;

use DateTimeImmutable;
use DateTimeZone;

final class DateTransformer
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
            '!m/d/Y',
            $value,
            self::$utcTimeZone ?: (self::$utcTimeZone = new DateTimeZone('UTC'))
        );

        if (false === $dateTime) {
            throw DateTimeException::fromDateTimeError($value, DateTimeImmutable::getLastErrors());
        }

        return $dateTime;
    }
}
