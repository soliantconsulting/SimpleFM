<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Proxy\TestAssets;

interface ComplexSetterInterface
{
    public function setFoo(string $foo, bool $bar, int ...$baz);

    public function getFoo() : array;
}
