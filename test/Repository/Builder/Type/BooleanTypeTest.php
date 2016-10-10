<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use Assert\InvalidArgumentException;
use Litipk\BigNumbers\Decimal;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Type\BooleanType;
use stdClass;

final class BooleanTypeTest extends TestCase
{
    public static function fileMakerBooleanProvider() : array
    {
        return [
            [null, false],
            ['', false],
            ['0', false],
            ['1', true],
            ['test', true],
            [Decimal::fromInteger(0), false],
            [Decimal::fromInteger(1), true],
            [Decimal::fromInteger(2), true],
            [Decimal::fromInteger(-1), true],
        ];
    }

    /**
     * @dataProvider fileMakerBooleanProvider
     */
    public function testSuccessfulConversionFromFileMaker($fileMakerValue, bool $expectedResult)
    {
        $type = new BooleanType();
        $this->assertSame($expectedResult, $type->fromFileMakerValue($fileMakerValue));
    }

    public function testUnsuccessfulConversionFromFileMaker()
    {
        $type = new BooleanType();
        $this->assertTrue($type->fromFileMakerValue(new stdClass()));
    }

    public function testSuccessfulConversionToFileMaker()
    {
        $type = new BooleanType();
        $this->assertSame(0, $type->toFileMakerValue(false)->asInteger());
        $this->assertSame(1, $type->toFileMakerValue(true)->asInteger());
    }

    public function testUnsuccessfulConversionToFileMaker()
    {
        $type = new BooleanType();
        $this->expectException(InvalidArgumentException::class);
        $type->toFileMakerValue('foo');
    }
}
