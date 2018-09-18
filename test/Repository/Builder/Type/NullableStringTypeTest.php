<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;
use Soliant\SimpleFM\Repository\Builder\Type\NullableStringType;

final class NullableStringTypeTest extends TestCase
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
        $type = new NullableStringType();
        $this->assertSame('foo', $type->fromFileMakerValue('foo', $this->client));
    }

    public function testNullConversionFromFileMaker() : void
    {
        $type = new NullableStringType();
        $this->assertNull($type->fromFileMakerValue('', $this->client));
    }

    public function testUnsuccessfulConversionFromFileMaker() : void
    {
        $type = new NullableStringType();
        $this->expectException(ConversionException::class);
        $type->fromFileMakerValue(1, $this->client);
    }

    public function testSuccessfulConversionToFileMaker() : void
    {
        $type = new NullableStringType();
        $this->assertSame('foo', $type->toFileMakerValue('foo', $this->client));
    }

    public function testNullConversionToFileMaker() : void
    {
        $type = new NullableStringType();
        $this->assertSame('', $type->toFileMakerValue(null, $this->client));
    }

    public function testUnsuccessfulConversionToFileMaker() : void
    {
        $type = new NullableStringType();
        $this->expectException(ConversionException::class);
        $type->toFileMakerValue(1, $this->client);
    }
}
