<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\ResultSet;

use Soliant\SimpleFM\Collection\CollectionInterface;
use Soliant\SimpleFM\Connection\Command;

interface ResultSetClientInterface
{
    public function execute(Command $command) : CollectionInterface;

    public function quoteString(string $string) : string;
}
