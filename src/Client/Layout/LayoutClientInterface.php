<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Client\Layout;

use Soliant\SimpleFM\Connection\Command;

interface LayoutClientInterface
{
    public function execute(Command $command) : Layout;
}
