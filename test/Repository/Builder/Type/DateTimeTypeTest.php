<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use Assert\InvalidArgumentException;
use DateTimeImmutable;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Type\DateTimeType;

final class DateTimeTypeTest extends TestCase
{
    public function testSuccessfulConversionFromFileMaker()
    {
        $type = new DateTimeType();
        $value = new DateTimeImmutable();
        $this->assertSame($value, $type->fromFileMakerValue($value));
    }

    public function testUnsuccessfulConversionFromFileMaker()
    {
        $type = new DateTimeType();
        $this->expectException(InvalidArgumentException::class);
        $type->fromFileMakerValue('foo');
    }

    public function testSuccessfulConversionToFileMaker()
    {
        $type = new DateTimeType();
        $value = new DateTimeImmutable();
        $this->assertSame($value, $type->toFileMakerValue($value));
    }

    public function testUnsuccessfulConversionToFileMaker()
    {
        $type = new DateTimeType();
        $this->expectException(InvalidArgumentException::class);
        $type->toFileMakerValue('foo');
    }
}
