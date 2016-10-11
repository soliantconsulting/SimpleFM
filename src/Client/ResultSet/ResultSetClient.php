<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\ResultSet;

use Exception;
use DateTimeZone;
use SimpleXMLElement;
use Soliant\SimpleFM\Client\Exception\FileMakerException;
use Soliant\SimpleFM\Client\ResultSet\Exception\ParseException;
use Soliant\SimpleFM\Client\ResultSet\Transformer\DateTimeTransformer;
use Soliant\SimpleFM\Client\ResultSet\Transformer\DateTransformer;
use Soliant\SimpleFM\Client\ResultSet\Transformer\NumberTransformer;
use Soliant\SimpleFM\Client\ResultSet\Transformer\TextTransformer;
use Soliant\SimpleFM\Client\ResultSet\Transformer\TimeTransformer;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Connection\ConnectionInterface;

final class ResultSetClient implements ResultSetClientInterface
{
    const GRAMMAR_PATH = '/fmi/xml/fmresultset.xml';

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var DateTimeZone
     */
    private $serverTimeZone;

    public function __construct(ConnectionInterface $connection, DateTimeZone $serverTimeZone)
    {
        $this->connection = $connection;
        $this->serverTimeZone = $serverTimeZone;
    }

    public function execute(Command $command) : array
    {
        $xml = $this->connection->execute($command, self::GRAMMAR_PATH);
        $errorCode = (int) $xml->error['code'];

        if (8 === $errorCode || 401 === $errorCode) {
            // "Empty result" or "No records match the request"
            return [];
        } elseif ($errorCode > 0) {
            throw FileMakerException::fromErrorCode($errorCode);
        }

        try {
            $metadata = $this->parseMetadata($xml->metadata[0]);
            $records = [];

            foreach ($xml->resultset[0]->record as $record) {
                $records[] = $this->parseRecord($record, $metadata);
            }
        } catch (Exception $e) {
            $dataSource = $xml->datasource;

            throw ParseException::fromConcreteException(
                (string) $dataSource['database'],
                (string) $dataSource['table'],
                (string) $dataSource['layout'],
                $e
            );
        }

        return $records;
    }

    public function quoteString(string $string) : string
    {
        return strtr($string, [
            '\\' => '\\\\',
            '=' => '\\=',
            '!' => '\\!',
            '<' => '\\<',
            '≤' => '\\≤',
            '>' => '\\>',
            '≥' => '\\≥',
            '…' => '\\…',
            '//' => '\\//',
            '?' => '\\?',
            '@' => '\\@',
            '#' => '\\#',
            '*' => '\\*',
            '"' => '\\"',
            '~' => '\\~',
        ]);
    }

    private function parseRecord(SimpleXMLElement $recordData, array $metadata) : array
    {
        $record = $this->createRecord($recordData, $metadata);

        if (isset($recordData->relatedset)) {
            foreach ($recordData->relatedset as $relatedSetData) {
                $relatedSetName = (string) $relatedSetData['table'];
                $record[$relatedSetName] = [];

                foreach ($relatedSetData->record as $relatedSetRecordData) {
                    $record[$relatedSetName][] = $this->createRecord(
                        $relatedSetRecordData,
                        $metadata,
                        strlen($relatedSetName) + 2
                    );
                }
            }
        }

        return $record;
    }

    private function createRecord(SimpleXMLElement $recordData, array $metadata, int $prefixLength = 0) : array
    {
        $record = [
            'record-id' => (int) $recordData['record-id'],
            'mod-id' => (int) $recordData['mod-id'],
        ];

        foreach ($recordData->field as $fieldData) {
            $fieldName = (string) $fieldData['name'];
            $localName = substr($fieldName, $prefixLength);

            if (!$metadata[$fieldName]['repeatable']) {
                $record[$localName] = $metadata[$fieldName]['transformer']((string) $fieldData->data);
                continue;
            }

            $record[$localName] = [];

            foreach ($fieldData->data as $data) {
                $record[$localName][] = $metadata[$fieldName]['transformer']((string) $data);
            }
        }

        return $record;
    }

    private function parseMetadata(SimpleXMLElement $xml) : array
    {
        $metadata = [];

        foreach ($xml->{'field-definition'} as $fieldDefinition) {
            $metadata[(string) $fieldDefinition['name']] = [
                'repeatable' => ((int) $fieldDefinition['max-repeat']) > 1,
                'transformer' => $this->getFieldTransformer($fieldDefinition),
            ];
        }

        foreach ($xml->{'relatedset-definition'} as $relatedSetDefinition) {
            $metadata += $this->parseMetadata($relatedSetDefinition);
        }

        return $metadata;
    }

    private function getFieldTransformer(SimpleXMLElement $fieldDefinition) : callable
    {
        switch ((string) $fieldDefinition['result']) {
            case 'text':
                return new TextTransformer();

            case 'number':
                return new NumberTransformer();

            case 'date':
                return new DateTransformer();

            case 'time':
                return new TimeTransformer();

            case 'timestamp':
                return new DateTimeTransformer($this->serverTimeZone);

            case 'container':
                return new TextTransformer();

            case 'unknown':
                throw ParseException::fromDeletedField();
        }

        throw ParseException::fromInvalidFieldType(
            (string) $fieldDefinition['name'],
            (string) $fieldDefinition['result']
        );
    }
}
