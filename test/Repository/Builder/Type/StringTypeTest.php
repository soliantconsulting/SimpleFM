<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;
use Soliant\SimpleFM\Repository\Builder\Type\StringType;

final class StringTypeTest extends TestCase
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
        $type = new StringType();
        $this->assertSame('foo', $type->fromFileMakerValue('foo', $this->client));
    }

    public function testEmptyStringConversionFromFileMaker() : void
    {
        $type = new StringType();
        $this->assertSame('', $type->fromFileMakerValue('', $this->client));
    }

    public function testUnsuccessfulConversionFromFileMaker() : void
    {
        $type = new StringType();
        $this->expectException(ConversionException::class);
        $type->fromFileMakerValue(1, $this->client);
    }

    public function testSuccessfulConversionToFileMaker() : void
    {
        $type = new StringType();
        $this->assertSame('foo', $type->toFileMakerValue('foo', $this->client));
    }

    public function testUnsuccessfulConversionToFileMaker() : void
    {
        $type = new StringType();
        $this->expectException(ConversionException::class);
        $type->toFileMakerValue(1, $this->client);
    }

    public function testUnsuccessfulNullConversionToFileMaker() : void
    {
        $type = new StringType();
        $this->expectException(ConversionException::class);
        $type->toFileMakerValue(null, $this->client);
    }
}
