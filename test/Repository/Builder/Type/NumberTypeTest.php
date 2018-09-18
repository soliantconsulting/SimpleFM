<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;
use Soliant\SimpleFM\Repository\Builder\Type\NumberType;

final class NumberTypeTest extends TestCase
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
        $type = new NumberType(false);
        $this->assertSame(1.1, $type->fromFileMakerValue(1.1, $this->client));
    }

    public function testSuccessfulFloatConversionFromFileMakerToInt() : void
    {
        $type = new NumberType(true);
        $this->assertSame(1, $type->fromFileMakerValue(1.1, $this->client));
    }

    public function testNullConversionFromFileMaker() : void
    {
        $this->assertNull((new NumberType(false))->fromFileMakerValue('', $this->client));
    }

    public function testUnsuccessfulConversionFromFileMaker() : void
    {
        $type = new NumberType(false);
        $this->expectException(ConversionException::class);
        $type->fromFileMakerValue(false, $this->client);
    }

    public function testSuccessfulConversionToFileMaker() : void
    {
        $type = new NumberType(false);
        $this->assertSame(1.1, $type->toFileMakerValue(1.1, $this->client));
    }

    public function testNullConversionToFileMaker() : void
    {
        $this->assertSame('', (new NumberType(false))->toFileMakerValue(null, $this->client));
    }

    public function testUnsuccessfulConversionToFileMaker() : void
    {
        $type = new NumberType(false);
        $this->expectException(ConversionException::class);
        $type->toFileMakerValue('foo', $this->client);
    }

    public function testUnsuccessfulFloatConversionToFileMakerInt() : void
    {
        $type = new NumberType(true);
        $this->expectException(ConversionException::class);
        $type->toFileMakerValue(1.1, $this->client);
    }

    public function numberProvider() : array
    {
        return [
            ['foo1bar2', 12.],
            ['714/715', 714715.],
            ['7-14/...71.5', 714.715],
            ['foo-7-14/...71.5', -714.715],
        ];
    }

    /**
     * @dataProvider numberProvider
     */
    public function testNumberCleanup(string $fileMakerValue, $expectedValue) : void
    {
        $numberType = new NumberType(false);
        $this->assertSame($expectedValue, $numberType->fromFileMakerValue($fileMakerValue, $this->client));
    }
}
