<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Client\ResultSet\Transformer;

final class TextTransformer
{
    public function __invoke(string $value) : string
    {
        return $value;
    }
}
