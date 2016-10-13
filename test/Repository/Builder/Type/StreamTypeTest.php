<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\StreamInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\DomainException;
use Soliant\SimpleFM\Repository\Builder\Type\StreamType;

final class StreamTypeTest extends TestCase
{
    public function testSuccessfulConversionFromFileMaker()
    {
        $type = new StreamType();
        $value = $this->prophesize(StreamInterface::class)->reveal();
        $this->assertSame($value, $type->fromFileMakerValue($value));
    }

    public function testNullConversionFromFileMaker()
    {
        $this->assertNull((new StreamType())->fromFileMakerValue(null));
    }

    public function testUnsuccessfulConversionFromFileMaker()
    {
        $type = new StreamType();
        $this->expectException(InvalidArgumentException::class);
        $type->fromFileMakerValue('foo');
    }

    public function testExceptionOnConversionToFileMaker()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Attempted conversion to file maker value');
        (new StreamType())->toFileMakerValue($this->prophesize(StreamInterface::class)->reveal());
    }
}
