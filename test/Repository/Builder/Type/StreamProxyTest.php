<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\StreamProxy;

final class StreamProxyTest extends TestCase
{
    public function methodProvider() : array
    {
        return [
            ['__toString', [], 'foo'],
            ['close', [], null],
            ['detach', [], null],
            ['eof', [], true],
            ['getContents', [], 'foo'],
            ['getMetadata', ['foo'], ['bar']],
            ['getSize', [], 3],
            ['isReadable', [], true],
            ['isSeekable', [], true],
            ['isWritable', [], true],
            ['read', [3], 'foo'],
            ['rewind', [], null],
            ['seek', [1, SEEK_CUR], null],
            ['tell', [], 0],
            ['write', ['foo'], 3],
        ];
    }

    /**
     * @dataProvider methodProvider
     */
    public function testProxyMethods(string $methodName, array $arguments, $returnValue) : void
    {
        $stream = $this->prophesize(StreamInterface::class);
        $stream->{$methodName}(...$arguments)->willReturn($returnValue)->shouldBeCalled();

        $client= $this->prophesize(ClientInterface::class);
        $client->getContainerData('foobar')->willReturn($stream->reveal());

        $proxy = new StreamProxy($client->reveal(), 'foobar');
        $this->assertSame($returnValue, $proxy->{$methodName}(...$arguments));
    }

    public function testWrappedStreamIsOnlyRetrievedOnce() : void
    {
        $client = $this->prophesize(ClientInterface::class);
        $client
            ->getContainerData('foobar')
            ->shouldBeCalledTimes(1)
            ->willReturn($this->prophesize(StreamInterface::class)->reveal());

        $proxy = new StreamProxy($client->reveal(), 'foobar');
        $proxy->rewind();
        $proxy->rewind();
    }
}
