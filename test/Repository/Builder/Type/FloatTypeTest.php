<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use Assert\InvalidArgumentException;
use Litipk\BigNumbers\Decimal;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Type\FloatType;

final class FloatTypeTest extends TestCase
{
    public function testSuccessfulConversionFromFileMaker()
    {
        $type = new FloatType();
        $value = Decimal::fromFloat(1.1);
        $this->assertSame(1.1, $type->fromFileMakerValue($value));
    }

    public function testUnsuccessfulConversionFromFileMaker()
    {
        $type = new FloatType();
        $this->expectException(InvalidArgumentException::class);
        $type->fromFileMakerValue('foo');
    }

    public function testSuccessfulConversionToFileMaker()
    {
        $type = new FloatType();
        $this->assertSame(1.1, $type->toFileMakerValue(1.1)->asFloat());
    }

    public function testUnsuccessfulConversionToFileMaker()
    {
        $type = new FloatType();
        $this->expectException(InvalidArgumentException::class);
        $type->toFileMakerValue('foo');
    }
}
