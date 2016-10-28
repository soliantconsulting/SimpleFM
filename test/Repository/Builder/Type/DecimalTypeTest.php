<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use Assert\InvalidArgumentException;
use Litipk\BigNumbers\Decimal;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Type\DecimalType;

final class DecimalTypeTest extends TestCase
{
    public function testSuccessfulConversionFromFileMaker()
    {
        $type = new DecimalType();
        $value = Decimal::fromInteger(1);
        $this->assertSame($value, $type->fromFileMakerValue($value));
    }

    public function testNullConversionFromFileMaker()
    {
        $this->assertNull((new DecimalType())->fromFileMakerValue(null));
    }

    public function testUnsuccessfulConversionFromFileMaker()
    {
        $type = new DecimalType();
        $this->expectException(InvalidArgumentException::class);
        $type->fromFileMakerValue('foo');
    }

    public function testSuccessfulConversionToFileMaker()
    {
        $type = new DecimalType();
        $value = Decimal::fromInteger(1);
        $this->assertSame($value, $type->toFileMakerValue($value));
    }

    public function testNullConversionToFileMaker()
    {
        $this->assertNull((new DecimalType())->toFileMakerValue(null));
    }

    public function testUnsuccessfulConversionToFileMaker()
    {
        $type = new DecimalType();
        $this->expectException(InvalidArgumentException::class);
        $type->toFileMakerValue('foo');
    }
}
