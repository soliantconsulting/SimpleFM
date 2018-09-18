<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Repository\Builder\Type;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Client\ClientInterface;
use Soliant\SimpleFM\Repository\Builder\Type\DateType;
use Soliant\SimpleFM\Repository\Builder\Type\Exception\ConversionException;

final class DateTypeTest extends TestCase
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
        $type = new DateType();
        $result = $type->fromFileMakerValue('01/23/4567', $this->client);
        $this->assertSame('01/23/4567', $result->format('m/d/Y'));
        $this->assertSame('UTC', $result->getTimezone()->getName());
    }

    public function testNullConversionFromFileMaker() : void
    {
        $this->assertNull((new DateType())->fromFileMakerValue('', $this->client));
    }

    public function testUnsuccessfulConversionFromFileMaker() : void
    {
        $type = new DateType();
        $this->expectException(ConversionException::class);
        $type->fromFileMakerValue('foo', $this->client);
    }

    public function testSuccessfulConversionToFileMaker() : void
    {
        $this->assertSame(
            '01/23/4567',
            (new DateType())->toFileMakerValue(
                DateTimeImmutable::createFromFormat('!m/d/Y', '01/23/4567'),
                $this->client
            )
        );
    }

    public function testNullConversionToFileMaker() : void
    {
        $this->assertSame('', (new DateType())->toFileMakerValue(null, $this->client));
    }

    public function testUnsuccessfulConversionToFileMaker() : void
    {
        $type = new DateType();
        $this->expectException(ConversionException::class);
        $type->toFileMakerValue('foo', $this->client);
    }
}
