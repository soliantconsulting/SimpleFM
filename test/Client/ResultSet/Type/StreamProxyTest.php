<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Client\ResultSet\Type;

use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\StreamInterface;
use Soliant\SimpleFM\Client\ResultSet\Transformer\StreamProxy;
use Soliant\SimpleFM\Connection\ConnectionInterface;

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
    public function testProxyMethods(string $methodName, array $arguments, $returnValue)
    {
        $stream = $this->prophesize(StreamInterface::class);
        $stream->{$methodName}(...$arguments)->willReturn($returnValue)->shouldBeCalled();

        $connection = $this->prophesize(ConnectionInterface::class);
        $connection->getAsset('/foo')->willReturn($stream->reveal());

        $proxy = new StreamProxy($connection->reveal(), '/foo');
        $this->assertSame($returnValue, $proxy->{$methodName}(...$arguments));
    }

    public function testWrappedStreamIsOnlyRetrievedOnce()
    {
        $connection = $this->prophesize(ConnectionInterface::class);
        $connection
            ->getAsset('/foo')
            ->shouldBeCalledTimes(1)
            ->willReturn($this->prophesize(StreamInterface::class)->reveal());

        $proxy = new StreamProxy($connection->reveal(), '/foo');
        $proxy->rewind();
        $proxy->rewind();
    }
}
