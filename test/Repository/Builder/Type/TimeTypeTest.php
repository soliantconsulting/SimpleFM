<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use Assert\InvalidArgumentException;
use DateTimeImmutable;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Type\TimeType;

final class TimeTypeTest extends TestCase
{
    public function testSuccessfulConversionFromFileMaker()
    {
        $type = new TimeType();
        $value = new DateTimeImmutable();
        $this->assertSame($value, $type->fromFileMakerValue($value));
    }

    public function testNullConversionFromFileMaker()
    {
        $this->assertNull((new TimeType())->fromFileMakerValue(null));
    }

    public function testUnsuccessfulConversionFromFileMaker()
    {
        $type = new TimeType();
        $this->expectException(InvalidArgumentException::class);
        $type->fromFileMakerValue('foo');
    }

    public function testSuccessfulConversionToFileMaker()
    {
        $this->assertSame(
            '01:23:45',
            (new TimeType())->toFileMakerValue(DateTimeImmutable::createFromFormat('!H:i:s', '01:23:45'))
        );
    }

    public function testNullConversionToFileMaker()
    {
        $this->assertNull((new TimeType())->toFileMakerValue(null));
    }

    public function testUnsuccessfulConversionToFileMaker()
    {
        $type = new TimeType();
        $this->expectException(InvalidArgumentException::class);
        $type->toFileMakerValue('foo');
    }
}
