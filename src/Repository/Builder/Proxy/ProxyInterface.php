<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Proxy;

interface ProxyInterface
{
    public function __getRelationId();

    public function __getRealEntity();
}
