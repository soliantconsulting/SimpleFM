<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Type\NullableStringType;

final class NullableStringTypeTest extends TestCase
{
    public function testSuccessfulConversionFromFileMaker()
    {
        $type = new NullableStringType();
        $this->assertSame('foo', $type->fromFileMakerValue('foo'));
    }

    public function testNullConversionFromFileMaker()
    {
        $type = new NullableStringType();
        $this->assertNull($type->fromFileMakerValue(''));
    }

    public function testUnsuccessfulConversionFromFileMaker()
    {
        $type = new NullableStringType();
        $this->expectException(InvalidArgumentException::class);
        $type->fromFileMakerValue(1);
    }

    public function testSuccessfulConversionToFileMaker()
    {
        $type = new NullableStringType();
        $this->assertSame('foo', $type->toFileMakerValue('foo'));
    }

    public function testNullConversionToFileMaker()
    {
        $type = new NullableStringType();
        $this->assertSame('', $type->toFileMakerValue(null));
    }

    public function testUnsuccessfulConversionToFileMaker()
    {
        $type = new NullableStringType();
        $this->expectException(InvalidArgumentException::class);
        $type->toFileMakerValue(1);
    }
}
