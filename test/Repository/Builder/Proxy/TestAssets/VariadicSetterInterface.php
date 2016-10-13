<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Proxy\TestAssets;

interface VariadicSetterInterface
{
    public function setFoo(string ...$foo);

    public function getFoo() : array;
}
