<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Client\Connection;
use Soliant\SimpleFM\Repository\Builder\Type\DateTimeType;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;

final class DateTimeTypeTest extends TestCase
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function setUp() : void
    {
        $client = $this->prophesize(ClientInterface::class);
        $client->getConnection()->willReturn(
            new Connection('', '', '', '', new DateTimeZone('Europe/Berlin'))
        );
        $this->client = $client->reveal();
    }

    public function testSuccessfulConversionFromFileMaker() : void
    {
        $type = new DateTimeType();
        $result = $type->fromFileMakerValue('01/23/4567 13:14:15', $this->client);
        $this->assertSame('01/23/4567 13:14:15', $result->format('m/d/Y H:i:s'));
        $this->assertSame('Europe/Berlin', $result->getTimezone()->getName());
    }

    public function testNullConversionFromFileMaker() : void
    {
        $this->assertNull((new DateTimeType())->fromFileMakerValue('', $this->client));
    }

    public function testUnsuccessfulConversionFromFileMaker() : void
    {
        $type = new DateTimeType();
        $this->expectException(ConversionException::class);
        $type->fromFileMakerValue('foo', $this->client);
    }

    public function testSuccessfulConversionToFileMaker() : void
    {
        $type = new DateTimeType();
        $value = new DateTimeImmutable('01/23/4567 13:14:15 America/Los_Angeles');
        $this->assertSame('01/23/4567 22:14:15', $type->toFileMakerValue($value, $this->client));
    }

    public function testNullConversionToFileMaker() : void
    {
        $this->assertSame('', (new DateTimeType())->toFileMakerValue(null, $this->client));
    }

    public function testUnsuccessfulConversionToFileMaker() : void
    {
        $type = new DateTimeType();
        $this->expectException(ConversionException::class);
        $type->toFileMakerValue('foo', $this->client);
    }
}
