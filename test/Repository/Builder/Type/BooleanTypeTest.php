<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\BooleanType;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;
use stdClass;

final class BooleanTypeTest extends TestCase
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function setUp() : void
    {
        $this->client = $this->prophesize(ClientInterface::class)->reveal();
    }

    public static function fileMakerBooleanProvider() : array
    {
        return [
            [null, false],
            ['', false],
            ['0', false],
            ['1', true],
            ['test', true],
            [0, false],
            [2, true],
            [2, true],
            [-1, true],
        ];
    }

    /**
     * @dataProvider fileMakerBooleanProvider
     */
    public function testSuccessfulConversionFromFileMaker($fileMakerValue, bool $expectedResult) : void
    {
        $type = new BooleanType();
        $this->assertSame($expectedResult, $type->fromFileMakerValue($fileMakerValue, $this->client));
    }

    public function testUnsuccessfulConversionFromFileMaker() : void
    {
        $type = new BooleanType();
        $this->assertTrue($type->fromFileMakerValue(new stdClass(), $this->client));
    }

    public function testSuccessfulConversionToFileMaker() : void
    {
        $type = new BooleanType();
        $this->assertSame(0, $type->toFileMakerValue(false, $this->client));
        $this->assertSame(1, $type->toFileMakerValue(true, $this->client));
    }

    public function testUnsuccessfulConversionToFileMaker() : void
    {
        $type = new BooleanType();
        $this->expectException(ConversionException::class);
        $type->toFileMakerValue('foo', $this->client);
    }
}
