<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Connection;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Connection\Exception\DomainException;

final class CommandTest extends TestCase
{
    public function testUseDisallowedDatabaseParameter()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('parameter "-db" is not allowed');
        new Command('foo', ['-db' => 'foo']);
    }

    public function testUseDisallowedLayoutParameter()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('parameter "-lay" is not allowed');
        new Command('foo', ['-lay' => 'foo']);
    }

    public function testUseDisallowedValueType()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('must either be scalar, null, Decimal or implement DateTimeInterface');
        new Command('foo', ['bar' => []]);
    }

    public function testGetLayout()
    {
        $command = new Command('foo', []);
        $this->assertSame('foo', $command->getLayout());
    }

    public function testCloneWithCredentials()
    {
        $command = new Command('foo', []);
        $newCommand = $command->withCredentials('bar', 'baz');

        $this->assertNotSame($newCommand, $command);
        $this->assertFalse($command->hasCredentials());
        $this->assertTrue($newCommand->hasCredentials());
        $this->assertSame('bar', $newCommand->getUsername());
        $this->assertSame('baz', $newCommand->getPassword());
    }

    public function testGetUsernameWithoutCredentials()
    {
        $this->expectException(InvalidArgumentException::class);
        $command = new Command('foo', []);
        $command->getUsername();
    }

    public function testGetPasswordWithoutCredentials()
    {
        $this->expectException(InvalidArgumentException::class);
        $command = new Command('foo', []);
        $command->getPassword();
    }

    public function parameterProvider() : array
    {
        return [
            'no-parameters' => [
                [],
                '-lay=foo',
            ],
            'empty-parameter' => [
                ['foo' => ''],
                '-lay=foo&foo',
            ],
            'null-parameter' => [
                ['foo' => null],
                '-lay=foo&foo',
            ],
            'string-parameter' => [
                ['foo' => 'bar'],
                '-lay=foo&foo=bar',
            ],
            'integer-parameter' => [
                ['foo' => 3],
                '-lay=foo&foo=3',
            ],
            'float-parameter' => [
                ['foo' => 3.3],
                '-lay=foo&foo=3.3',
            ],
            'boolean-parameter' => [
                ['foo' => true],
                '-lay=foo&foo=1',
            ],
            'datetime-parameter' => [
                ['foo' => new \DateTimeImmutable('2016-01-01 00:00:00 UTC')],
                '-lay=foo&foo=01%2F01%2F2016+00%3A00%3A00',
            ],
            'decimal-parameter' => [
                ['foo' => \Litipk\BigNumbers\Decimal::fromString('12.499734362638823')],
                '-lay=foo&foo=12.499734362638823',
            ],
        ];
    }

    /**
     * @dataProvider parameterProvider
     */
    public function testToString(array $parameters, string $expectedString)
    {
        $command = new Command('foo', $parameters);
        $this->assertSame($expectedString, (string) $command);
    }
}
