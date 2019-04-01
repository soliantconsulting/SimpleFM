<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\ResultSet\Transformer;

use Litipk\BigNumbers\Decimal;

final class NumberTransformer
{
    public function __invoke(string $value)
    {
        $cleanedValue = preg_replace_callback(
            '(^[^\d\-.]*(-?)([^.]*)(.?)(.*)$)',
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
            $cleanedValue = '0';
        }

        if (0 === strpos($cleanedValue, '.')) {
            $cleanedValue = '0' . $cleanedValue;
        }

        return Decimal::fromString($cleanedValue);
    }
}
