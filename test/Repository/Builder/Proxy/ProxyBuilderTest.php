<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Proxy\Exception\InvalidInterfaceException;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyBuilder;
use Soliant\SimpleFM\Repository\Builder\Proxy\ProxyInterface;
use SoliantTest\SimpleFM\Repository\Builder\Proxy\TestAssets\ComplexSetterInterface;
use SoliantTest\SimpleFM\Repository\Builder\Proxy\TestAssets\SimpleGetterInterface;
use SoliantTest\SimpleFM\Repository\Builder\Proxy\TestAssets\SimpleSetterInterface;
use SoliantTest\SimpleFM\Repository\Builder\Proxy\TestAssets\VariadicSetterInterface;
use stdClass;

final class ProxyBuilderTest extends TestCase
{
    public function testInternalProxyMethods()
    {
        $realEntity = new class implements SimpleGetterInterface
        {
            public function getFoo() : string
            {
                return '';
            }
        };

        $proxyBuilder = new ProxyBuilder();
        $proxy = $proxyBuilder->createProxy(SimpleGetterInterface::class, function () use ($realEntity) {
            return $realEntity;
        }, 1);

        $this->assertInstanceOf(ProxyInterface::class, $proxy);
        $this->assertInstanceOf(SimpleGetterInterface::class, $proxy);
        $this->assertSame(1, $proxy->__getRelationId());
        $this->assertSame($realEntity, $proxy->__getRealEntity());
    }

    public function testExceptionOnInvalidInterface()
    {
        $proxyBuilder = new ProxyBuilder();

        $this->expectException(InvalidInterfaceException::class);
        $this->expectExceptionMessage('"stdClass" was expected to be an interface');
        $proxyBuilder->createProxy(stdClass::class, function () {
        }, 1);
    }

    public function testSimpleGetter()
    {
        $proxyBuilder = new ProxyBuilder();
        $proxy = $proxyBuilder->createProxy(SimpleGetterInterface::class, function () {
            return new class implements SimpleGetterInterface
            {
                public function getFoo() : string
                {
                    return 'foo';
                }
            };
        }, 1);

        $this->assertSame('foo', $proxy->getFoo());
    }

    public function testInvalidInitializerReturn()
    {
        $proxyBuilder = new ProxyBuilder();
        $proxy = $proxyBuilder->createProxy(SimpleGetterInterface::class, function () {
            return new stdClass();
        }, 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SimpleGetterInterface" but is not');
        $proxy->getFoo();
    }

    public function testSimpleSetter()
    {
        $proxyBuilder = new ProxyBuilder();
        $proxy = $proxyBuilder->createProxy(SimpleSetterInterface::class, function () {
            return new class implements SimpleSetterInterface
            {
                private $foo;

                public function setFoo(string $foo)
                {
                    $this->foo = $foo;
                }

                public function getFoo() : string
                {
                    return $this->foo;
                }
            };
        }, 1);

        $proxy->setFoo('bar');
        $this->assertSame('bar', $proxy->getFoo());
    }

    public function testVariadicSetter()
    {
        $proxyBuilder = new ProxyBuilder();
        $proxy = $proxyBuilder->createProxy(VariadicSetterInterface::class, function () {
            return new class implements VariadicSetterInterface
            {
                private $foo;

                public function setFoo(string ...$foo)
                {
                    $this->foo = $foo;
                }

                public function getFoo() : array
                {
                    return $this->foo;
                }
            };
        }, 1);

        $proxy->setFoo(...['bar', 'baz']);
        $this->assertSame(['bar', 'baz'], $proxy->getFoo());
    }

    public function testComplexSetter()
    {
        $proxyBuilder = new ProxyBuilder();
        $proxy = $proxyBuilder->createProxy(ComplexSetterInterface::class, function () {
            return new class implements ComplexSetterInterface
            {
                private $foo;
                private $bar;
                private $baz;

                public function setFoo(string $foo, bool $bar, int ...$baz)
                {
                    $this->foo = $foo;
                    $this->bar = $bar;
                    $this->baz = $baz;
                }

                public function getFoo() : array
                {
                    return [
                        $this->foo,
                        $this->bar,
                        $this->baz,
                    ];
                }
            };
        }, 1);

        $proxy->setFoo('bar', true, ...[2, 3]);
        $this->assertSame(['bar', true, [2, 3]], $proxy->getFoo());
    }
}
