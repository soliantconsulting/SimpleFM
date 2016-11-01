<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use Assert\InvalidArgumentException;
use DateTimeImmutable;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Type\DateType;

final class DateTypeTest extends TestCase
{
    public function testSuccessfulConversionFromFileMaker()
    {
        $type = new DateType();
        $value = new DateTimeImmutable();
        $this->assertSame($value, $type->fromFileMakerValue($value));
    }

    public function testNullConversionFromFileMaker()
    {
        $this->assertNull((new DateType())->fromFileMakerValue(null));
    }

    public function testUnsuccessfulConversionFromFileMaker()
    {
        $type = new DateType();
        $this->expectException(InvalidArgumentException::class);
        $type->fromFileMakerValue('foo');
    }

    public function testSuccessfulConversionToFileMaker()
    {
        $this->assertSame(
            '01/23/4567',
            (new DateType())->toFileMakerValue(DateTimeImmutable::createFromFormat('!m/d/Y', '01/23/4567'))
        );
    }

    public function testNullConversionToFileMaker()
    {
        $this->assertNull((new DateType())->toFileMakerValue(null));
    }

    public function testUnsuccessfulConversionToFileMaker()
    {
        $type = new DateType();
        $this->expectException(InvalidArgumentException::class);
        $type->toFileMakerValue('foo');
    }
}
