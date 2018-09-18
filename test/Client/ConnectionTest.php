<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Client;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Soliant\SimpleFM\Client\Connection;

final class ConnectionTest extends TestCase
{
    public function testValueObject() : void
    {
        $timeZone = new DateTimeZone('Europe/Berlin');
        $connection = new Connection('http://foo', 'bar', 'baz', 'bat', $timeZone);
        $this->assertSame('http://foo', $connection->getBaseUri());
        $this->assertSame('bar', $connection->getUsername());
        $this->assertSame('baz', $connection->getPassword());
        $this->assertSame('bat', $connection->getDatabase());
        $this->assertSame($timeZone, $connection->getTimeZone());
    }
}
