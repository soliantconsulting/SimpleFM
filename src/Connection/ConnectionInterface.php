<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Connection;

use Psr\Http\Message\StreamInterface;
use SimpleXMLElement;

interface ConnectionInterface
{
    public function execute(Command $command, string $grammarPath) : SimpleXMLElement;

    public function getAsset(string $path) : StreamInterface;
}
