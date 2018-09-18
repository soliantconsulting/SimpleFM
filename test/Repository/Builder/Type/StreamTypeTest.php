<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;
use Soliant\SimpleFM\Repository\Builder\Type\StreamProxy;
use Soliant\SimpleFM\Repository\Builder\Type\StreamType;

final class StreamTypeTest extends TestCase
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function setUp() : void
    {
        $this->client = $this->prophesize(ClientInterface::class)->reveal();
    }

    public function testSuccessfulConversionFromFileMaker() : void
    {
        $type = new StreamType();
        $result = $type->fromFileMakerValue('foobar', $this->client);

        $this->assertInstanceOf(StreamProxy::class, $result);
        $this->assertAttributeSame('foobar', 'url', $result);
    }

    public function testNullConversionFromFileMaker() : void
    {
        $this->assertNull((new StreamType())->fromFileMakerValue('', $this->client));
    }

    public function testUnsuccessfulConversionFromFileMaker() : void
    {
        $type = new StreamType();
        $this->expectException(ConversionException::class);
        $type->fromFileMakerValue(1, $this->client);
    }

    public function testExceptionOnConversionToFileMaker() : void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Attempted conversion to FileMaker value');
        (new StreamType())->toFileMakerValue($this->prophesize(StreamInterface::class)->reveal(), $this->client);
    }
}
