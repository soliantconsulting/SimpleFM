<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\ResultSet\Transformer;

use DateTimeImmutable;
use DateTimeZone;
use Soliant\SimpleFM\Client\ResultSet\Transformer\Exception\DateTimeException;

final class DateTimeTransformer
{
    /**
     * @var DateTimeZone
     */
    private $timeZone;

    public function __construct(DateTimeZone $timeZone)
    {
        $this->timeZone = $timeZone;
    }

    public function __invoke(string $value)
    {
        if ('' === $value) {
            return null;
        }

        $dateTime = DateTimeImmutable::createFromFormat('!m/d/Y H:i:s', $value, $this->timeZone);

        if (false === $dateTime) {
            throw DateTimeException::fromDateTimeError($value, DateTimeImmutable::getLastErrors());
        }

        return $dateTime;
    }
}
