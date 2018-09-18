<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Soliant\SimpleFM\Client\ClientInterface;

interface TypeInterface
{
    public function fromFileMakerValue($value, ClientInterface $client);

    public function toFileMakerValue($value, ClientInterface $client);
}
