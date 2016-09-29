<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\ResultSet;

use Soliant\SimpleFM\Connection\Command;

interface ResultSetClientInterface
{
    public function execute(Command $command) : array;

    public function quoteString(string $string) : string;
}
