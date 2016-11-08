<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Proxy;

interface ProxyBuilderInterface
{
    public function createProxy(string $entityInterface, callable $initializer, $relationId) : ProxyInterface;
}
