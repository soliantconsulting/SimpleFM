<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Repository\Builder\Type\StringType;

final class StringTypeTest extends TestCase
{
    public function testSuccessfulConversionFromFileMaker()
    {
        $type = new StringType();
        $this->assertSame('foo', $type->fromFileMakerValue('foo'));
    }

    public function testUnsuccessfulConversionFromFileMaker()
    {
        $type = new StringType();
        $this->expectException(InvalidArgumentException::class);
        $type->fromFileMakerValue(1);
    }

    public function testSuccessfulConversionToFileMaker()
    {
        $type = new StringType();
        $this->assertSame('foo', $type->toFileMakerValue('foo'));
    }

    public function testUnsuccessfulConversionToFileMaker()
    {
        $type = new StringType();
        $this->expectException(InvalidArgumentException::class);
        $type->toFileMakerValue(1);
    }
}
