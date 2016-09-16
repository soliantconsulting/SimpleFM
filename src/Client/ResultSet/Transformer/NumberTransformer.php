<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Client\ResultSet\Transformer;

use Litipk\BigNumbers\Decimal;

final class NumberTransformer
{
    public function __invoke(string $value)
    {
        if ('' === $value) {
            return null;
        }

        return Decimal::fromString($value);
    }
}
