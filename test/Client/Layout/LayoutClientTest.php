<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Client\Layout;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Client\Exception\FileMakerException;
use Soliant\SimpleFM\Client\Layout\LayoutClient;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Connection\ConnectionInterface;

final class LayoutClientTest extends TestCase
{
    public function testCompleteXml()
    {
        $command = new Command('foo', []);
        $xml = simplexml_load_file(__DIR__ . '/TestAssets/sample_fmpxmllayout.xml');
        $connection = $this->prophesize(ConnectionInterface::class);
        $connection->execute($command, '/fmi/xml/FMPXMLLAYOUT.xml')->willReturn($xml);
        $client = new LayoutClient($connection->reveal());

        $layout = $client->execute($command);

        $this->assertSame('FMServer_Sample', $layout->getDatabase());
        $this->assertSame('Projects | Web', $layout->getName());
        $this->assertCount(9, $layout->getFields());

        $this->assertTrue($layout->hasField('Projects::Project Name'));
        $nameField = $layout->getField('Projects::Project Name');
        $this->assertFalse($nameField->hasValueList());
        $this->assertSame('EDITTEXT', $nameField->getType());

        $this->assertTrue($layout->hasField('Projects::Status on Screen'));
        $statusField = $layout->getField('Projects::Status on Screen');
        $this->assertTrue($statusField->hasValueList());
        $this->assertSame('Status', (string) $statusField->getValueList());
        $this->assertSame('Status', $statusField->getValueList()->getName());
        $this->assertSame('Completed', (string) $statusField->getValueList()->getValues()[0]);
        $this->assertSame('Completed', $statusField->getValueList()->getValues()[0]->getDisplay());
        $this->assertSame('Completed', $statusField->getValueList()->getValues()[0]->getValue());

        $this->expectException(InvalidArgumentException::class);
        $layout->getField('non-existent');
    }

    public function testErrorXml()
    {
        $command = new Command('foo', []);
        $xml = simplexml_load_file(__DIR__ . '/TestAssets/error_fmpxmllayout.xml');
        $connection = $this->prophesize(ConnectionInterface::class);
        $connection->execute($command, '/fmi/xml/FMPXMLLAYOUT.xml')->willReturn($xml);
        $client = new LayoutClient($connection->reveal());

        $this->expectException(FileMakerException::class);
        $this->expectExceptionMessage('User canceled action');
        $this->expectExceptionCode(1);
        $client->execute($command);
    }
}
