<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;

final class NumberType implements TypeInterface
{
    /**
     * @var bool
     */
    private $limitToInt;

    public function __construct(bool $limitToInt)
    {
        $this->limitToInt = $limitToInt;
    }

    public function fromFileMakerValue($value, ClientInterface $client)
    {
        if (is_string($value)) {
            $value = $this->castFromString($value);
        }

        if (null === $value) {
            return null;
        }

        if (! is_float($value) && ! is_int($value)) {
            throw ConversionException::fromInvalidType($value, 'float|int');
        }

        return $this->limitToInt ? (int) $value : (float) $value;
    }

    public function toFileMakerValue($value, ClientInterface $client)
    {
        if (null === $value) {
            return '';
        }

        if ($this->limitToInt && ! is_int($value)) {
            throw ConversionException::fromInvalidType($value, 'int');
        }

        if (! is_float($value) && ! is_int($value)) {
            throw ConversionException::fromInvalidType($value, 'float|int');
        }

        return $value;
    }

    private function castFromString(string $value)
    {
        $cleanedValue = preg_replace_callback(
            '(^[^\d-.]*(-?)([^.]*)(.?)(.*)$)',
            function (array $match) : string {
                return
                    $match[1]
                    . preg_replace('([^\d]+)', '', $match[2])
                    . $match[3]
                    . preg_replace('([^\d]+)', '', $match[4]);
            },
            $value
        );

        if ('' === $cleanedValue) {
            return null;
        }

        if ('-' === $cleanedValue) {
            return 0;
        }

        if (0 === strpos($cleanedValue, '.')) {
            return (float) ('0' . $cleanedValue);
        }

        return (float) $cleanedValue;
    }
}
