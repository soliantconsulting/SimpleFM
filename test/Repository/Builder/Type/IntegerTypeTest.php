<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use Assert\InvalidArgumentException;
use Litipk\BigNumbers\Decimal;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Type\IntegerType;

final class IntegerTypeTest extends TestCase
{
    public function testSuccessfulConversionFromFileMaker()
    {
        $type = new IntegerType();
        $value = Decimal::fromInteger(1);
        $this->assertSame(1, $type->fromFileMakerValue($value));
    }

    public function testUnsuccessfulConversionFromFileMaker()
    {
        $type = new IntegerType();
        $this->expectException(InvalidArgumentException::class);
        $type->fromFileMakerValue('foo');
    }

    public function testSuccessfulConversionToFileMaker()
    {
        $type = new IntegerType();
        $this->assertSame(1, $type->toFileMakerValue(1)->asInteger());
    }

    public function testUnsuccessfulConversionToFileMaker()
    {
        $type = new IntegerType();
        $this->expectException(InvalidArgumentException::class);
        $type->toFileMakerValue('foo');
    }
}
