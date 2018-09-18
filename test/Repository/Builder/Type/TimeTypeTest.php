<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;
use Soliant\SimpleFM\Repository\Builder\Type\TimeType;

final class TimeTypeTest extends TestCase
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function setUp() : void
    {
        $this->client = $this->prophesize(ClientInterface::class)->reveal();
    }

    public function testSuccessfulConversionFromFileMaker() : void
    {
        $type = new TimeType();
        $result = $type->fromFileMakerValue('13:14:15', $this->client);
        $this->assertSame('13:14:15', $result->format('H:i:s'));
        $this->assertSame('UTC', $result->getTimezone()->getName());
    }

    public function testNullConversionFromFileMaker() : void
    {
        $this->assertNull((new TimeType())->fromFileMakerValue('', $this->client));
    }

    public function testUnsuccessfulConversionFromFileMaker() : void
    {
        $type = new TimeType();
        $this->expectException(ConversionException::class);
        $type->fromFileMakerValue('foo', $this->client);
    }

    public function testSuccessfulConversionToFileMaker() : void
    {
        $this->assertSame(
            '01:23:45',
            (new TimeType())->toFileMakerValue(DateTimeImmutable::createFromFormat('!H:i:s', '01:23:45'), $this->client)
        );
    }

    public function testNullConversionToFileMaker() : void
    {
        $this->assertSame('', (new TimeType())->toFileMakerValue(null, $this->client));
    }

    public function testUnsuccessfulConversionToFileMaker() : void
    {
        $type = new TimeType();
        $this->expectException(ConversionException::class);
        $type->toFileMakerValue('foo', $this->client);
    }
}
