<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Client\ResultSet;

use DateTimeImmutable;
use DateTimeZone;
use Litipk\BigNumbers\Decimal;
use PHPUnit_Framework_TestCase as TestCase;
use Soliant\SimpleFM\Client\Exception\FileMakerException;
use Soliant\SimpleFM\Client\ResultSet\Exception\ParseException;
use Soliant\SimpleFM\Client\ResultSet\ResultSetClient;
use Soliant\SimpleFM\Client\ResultSet\Transformer\Exception\DateTimeException;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Connection\ConnectionInterface;

final class ResultSetClientTest extends TestCase
{
    public static function validXmlProvider() : array
    {
        return [
            'project-sample-data' => [
                'projectsampledata.xml',
                [
                    [
                        'record-id' => 7676,
                        'mod-id' => 5,
                        'PROJECT ID MATCH FIELD' => Decimal::fromInteger(1),
                        'Created By' => 'Tim Thomson',
                        'Creation TimeStamp' => new DateTimeImmutable('2012-02-22 17:19:47 UTC'),
                        'Project Name' => 'Launch web site',
                        'Description' => (
                            "Launch the web site with our new branding and product line.\n\n"
                            . "                    Third line"
                        ),
                        'Status' => Decimal::fromInteger(4),
                        'Status on Screen' => 'Overdue',
                        'Start Date' => new DateTimeImmutable('2011-04-13 00:00:00 UTC'),
                        'Due Date' => new DateTimeImmutable('2012-05-02 00:00:00 UTC'),
                        'Days Remaining' => Decimal::fromInteger(0),
                        'Days Elapsed' => Decimal::fromInteger(275),
                        'Project Completion' => Decimal::fromString('0.48'),
                        'Tag' => 'marketing',
                        'Start Date Project Completion' => new DateTimeImmutable('2011-04-13 00:00:00 UTC'),
                        'Due Date Project Completion' => new DateTimeImmutable('2012-05-02 00:00:00 UTC'),
                        'Repeating Field' => ['A1', 'B2', 'C3', 'D4', 'E5', 'F6', 'G7', 'H8', 'I9'],
                        'Tasks' => [
                            [
                                'record-id' => 14999,
                                'mod-id' => 1,
                                'Task Name' => 'Site map sketch',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(2),
                                'Repeating Field' => [
                                    Decimal::fromInteger(1),
                                    Decimal::fromInteger(2),
                                    Decimal::fromInteger(3),
                                ],
                            ],
                            [
                                'record-id' => 15000,
                                'mod-id' => 1,
                                'Task Name' => 'Send art to vendor',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(4),
                                'Repeating Field' => [
                                    Decimal::fromInteger(4),
                                    Decimal::fromInteger(5),
                                    Decimal::fromInteger(6),
                                ],
                            ],
                            [
                                'record-id' => 15001,
                                'mod-id' => 1,
                                'Task Name' => 'Review mock ups',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(5),
                                'Repeating Field' => [
                                    Decimal::fromInteger(7),
                                    Decimal::fromInteger(8),
                                    Decimal::fromInteger(9),
                                ],
                            ],
                            [
                                'record-id' => 15002,
                                'mod-id' => 0,
                                'Task Name' => 'Write page text',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(6),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                            [
                                'record-id' => 15003,
                                'mod-id' => 0,
                                'Task Name' => 'New logo art',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(3),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                        ],
                    ],
                    [
                        'record-id' => 7677,
                        'mod-id' => 4,
                        'PROJECT ID MATCH FIELD' => Decimal::fromInteger(7),
                        'Created By' => 'Tim Thomson',
                        'Creation TimeStamp' => new DateTimeImmutable('2012-02-22 17:19:47 UTC'),
                        'Project Name' => 'Prototype',
                        'Description' => (
                            "Build a working prototype of the new product.\n\n\n"
                            . "                    Fourth line."
                        ),
                        'Status' => Decimal::fromInteger(4),
                        'Status on Screen' => 'Overdue',
                        'Start Date' => new DateTimeImmutable('2012-02-06 00:00:00 UTC'),
                        'Due Date' => new DateTimeImmutable('2012-02-17 00:00:00 UTC'),
                        'Days Remaining' => Decimal::fromInteger(0),
                        'Days Elapsed' => Decimal::fromInteger(9),
                        'Project Completion' => Decimal::fromString('0.32'),
                        'Tag' => 'manufacturing',
                        'Start Date Project Completion' => new DateTimeImmutable('2012-02-09 00:00:00 UTC'),
                        'Due Date Project Completion' => new DateTimeImmutable('2012-02-17 00:00:00 UTC'),
                        'Repeating Field' => ['*1', '-2', '+3', '.4', '/5', '=6', '=7', '-8', '`9'],
                        'Tasks' => [
                            [
                                'record-id' => 15005,
                                'mod-id' => 1,
                                'Task Name' => 'Build prototype',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(9),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                            [
                                'record-id' => 15006,
                                'mod-id' => 1,
                                'Task Name' => 'Review sketches',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(8),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                            [
                                'record-id' => 15007,
                                'mod-id' => 1,
                                'Task Name' => 'Complete sketches',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(7),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                            [
                                'record-id' => 15014,
                                'mod-id' => 0,
                                'Task Name' => 'Draft requirements',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(16),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                            [
                                'record-id' => 15015,
                                'mod-id' => 0,
                                'Task Name' => 'Review requirements',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(17),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                        ],
                    ],
                    [
                        'record-id' => 7678,
                        'mod-id' => 4,
                        'PROJECT ID MATCH FIELD' => Decimal::fromInteger(13),
                        'Created By' => 'Tim Thomson',
                        'Creation TimeStamp' => new DateTimeImmutable('2012-02-22 17:19:47 UTC'),
                        'Project Name' => 'Investor meeting',
                        'Description' => (
                            "This is important. We need the investors to have confidence.\n"
                            . "                    Second line."
                        ),
                        'Status' => Decimal::fromInteger(4),
                        'Status on Screen' => 'Overdue',
                        'Start Date' => new DateTimeImmutable('2011-12-12 00:00:00 UTC'),
                        'Due Date' => new DateTimeImmutable('2012-03-22 00:00:00 UTC'),
                        'Days Remaining' => Decimal::fromInteger(0),
                        'Days Elapsed' => Decimal::fromInteger(73),
                        'Project Completion' => Decimal::fromString('0.4285714285714286'),
                        'Tag' => 'finance',
                        'Start Date Project Completion' => new DateTimeImmutable('2012-01-02 00:00:00 UTC'),
                        'Due Date Project Completion' => new DateTimeImmutable('2012-03-22 00:00:00 UTC'),
                        'Repeating Field' => ['a1', 'b2', 'c3', 'd4', 'e5', 'f6', 'g7', 'h8', 'i9'],
                        'Tasks' => [
                            [
                                'record-id' => 15004,
                                'mod-id' => 1,
                                'Task Name' => 'Gather requirements',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(1),
                                'Repeating Field' => [
                                    null,
                                    Decimal::fromInteger(0),
                                    null
                                ],
                            ],
                            [
                                'record-id' => 15008,
                                'mod-id' => 1,
                                'Task Name' => 'Investor meeting',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(13),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    Decimal::fromInteger(0),
                                ],
                            ],
                            [
                                'record-id' => 15009,
                                'mod-id' => 1,
                                'Task Name' => 'Final draft of slides',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(12),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                            [
                                'record-id' => 15010,
                                'mod-id' => 0,
                                'Task Name' => 'Complete business plan',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(10),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                            [
                                'record-id' => 15011,
                                'mod-id' => 0,
                                'Task Name' => 'First draft of slides',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(11),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                            [
                                'record-id' => 15012,
                                'mod-id' => 0,
                                'Task Name' => 'Market research',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(14),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                            [
                                'record-id' => 15013,
                                'mod-id' => 0,
                                'Task Name' => 'Competitive analysis',
                                'TASK ID MATCH FIELD' => Decimal::fromInteger(15),
                                'Repeating Field' => [
                                    null,
                                    null,
                                    null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'empty-resultset' => [
                'sample_fmresultset_empty.xml',
                [],
            ],
        ];
    }

    public static function allFieldTypesProvider() : array
    {
        return [
            'base-sample-data' => [
                'ParentChildAssociations/Base-recid1-id2.xml',
            ],
        ];
    }

    public function specialCharacterProvider() : array
    {
        return [
            ['\\', '\\\\'],
            ['==', '\\=\\='],
            ['=', '\\='],
            ['!', '\\!'],
            ['<', '\\<'],
            ['≤', '\\≤'],
            ['>', '\\>'],
            ['≥', '\\≥'],
            ['…', '\\…'],
            ['//', '\\//'],
            ['?', '\\?'],
            ['@', '\\@'],
            ['#', '\\#'],
            ['*', '\\*'],
            ['""', '\\"\\"'],
            ['*""', '\\*\\"\\"'],
            ['~', '\\~'],
        ];
    }

    /**
     * @dataProvider validXmlProvider
     */
    public function testValidXml(string $xmlPath, array $expectedResult)
    {
        $command = new Command('foo', []);
        $client = $this->createClient($command, $xmlPath);

        $this->assertEquals($expectedResult, $client->execute($command));
    }

    public function testErrorXml()
    {
        $command = new Command('foo', []);
        $client = $this->createClient($command, 'sample_fmresultset_fmerror4.xml');

        $this->expectException(FileMakerException::class);
        $this->expectExceptionMessage('Command is unknown');
        $this->expectExceptionCode(4);
        $client->execute($command);
    }

    public function testInvalidFileMakerExceptionCode()
    {
        $this->expectException(FileMakerException::class);
        $this->expectExceptionMessage('Unknown error');
        $this->expectExceptionCode(-100);

        throw FileMakerException::fromErrorCode(-100);
    }

    /**
     * @dataProvider allFieldTypesProvider
     */
    public function testAllFieldTransformerTypes(string $xmlPath)
    {
        $command = new Command('foo', []);
        $client = $this->createClient($command, $xmlPath);
        $firstBaseRecord = $client->execute($command)[0];

        $this->assertInstanceOf(Decimal::class, $firstBaseRecord['id']);
        $this->assertInstanceOf(Decimal::class, $firstBaseRecord['id_Parent']);
        $this->assertInstanceOf(Decimal::class, $firstBaseRecord['Number Field']);
        $this->assertInternalType('string', $firstBaseRecord['Text Field']);
        $this->assertInstanceOf(DateTimeImmutable::class, $firstBaseRecord['Time Field']);
        $this->assertInstanceOf(DateTimeImmutable::class, $firstBaseRecord['Timestamp Field']);
        $this->assertInternalType('string', $firstBaseRecord['Container Field']);
        $this->assertInternalType('string', $firstBaseRecord['Calculation Text Field']);
        $this->assertInstanceOf(Decimal::class, $firstBaseRecord['Summary Number Field']);
        $this->assertInternalType('array', $firstBaseRecord['Repeating Number Field']);
        $this->assertInstanceOf(Decimal::class, $firstBaseRecord['Repeating Number Field'][0]);
        $this->assertInstanceOf(Decimal::class, $firstBaseRecord['Repeating Number Field'][9]);
        $this->assertInstanceOf(Decimal::class, $firstBaseRecord['Parent::id']);
        $this->assertInstanceOf(Decimal::class, $firstBaseRecord['Parent::Number Field']);
        $this->assertInternalType('string', $firstBaseRecord['Parent::Text Field']);
        $this->assertInternalType('array', $firstBaseRecord['Child']);
        $this->assertInternalType('array', $firstBaseRecord['Parent']);
    }

    public function testInvalidFieldTransformerTypeFake()
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Invalid field type "fake" for field "id" discovered');

        $command = new Command('foo', []);
        $client = $this->createClient($command, 'invalidFieldTypeFake.xml');
        $client->execute($command);
    }

    public function testInvalidFieldTransformerTypeTimestamp()
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage(
            'Could not parse "invalid timestamp value", reason: A two digit month could not be found'
        );

        $command = new Command('foo', []);
        $client = $this->createClient($command, 'invalidFieldTypeTimestamp.xml');
        $client->execute($command);
    }

    public function testInvalidFieldTransformerTypeTime()
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage(
            'Could not parse "invalid time value", reason: A two digit hour could not be found'
        );

        $command = new Command('foo', []);
        $client = $this->createClient($command, 'invalidFieldTypeTime.xml');
        $client->execute($command);
    }

    public function testInvalidFieldTransformerTypeDate()
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage(
            'Could not parse "invalid date value", reason: A two digit month could not be found'
        );

        $command = new Command('foo', []);
        $client = $this->createClient($command, 'invalidFieldTypeDate.xml');
        $client->execute($command);
    }

    public function testDeletedField()
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage(
            'A field has been deleted from the table, but remained in the layout'
        );

        $command = new Command('foo', []);
        $client = $this->createClient($command, 'deleted-field.xml');
        $client->execute($command);
    }

    /**
     * @dataProvider specialCharacterProvider
     */
    public function testQuoteString(string $testString, string $expectedResult)
    {
        $client = new ResultSetClient($this->prophesize(ConnectionInterface::class)->reveal(), new DateTimeZone('UTC'));
        $this->assertSame($expectedResult, $client->quoteString($testString));
    }

    private function createClient(Command $command, string $xmlPath) : ResultSetClient
    {
        $xml = simplexml_load_file(__DIR__ . '/TestAssets/' . $xmlPath);
        $connection = $this->prophesize(ConnectionInterface::class);
        $connection->execute($command, '/fmi/xml/fmresultset.xml')->willReturn($xml);
        return new ResultSetClient($connection->reveal(), new DateTimeZone('UTC'));
    }
}
