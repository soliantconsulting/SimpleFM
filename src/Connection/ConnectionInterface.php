<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Connection;

interface ConnectionInterface
{
    public function execute(Command $command, string $grammarPath);
}
