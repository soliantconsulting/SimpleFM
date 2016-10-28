<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client\Layout;

use SimpleXMLElement;
use Soliant\SimpleFM\Client\Exception\FileMakerException;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Connection\ConnectionInterface;

final class LayoutClient implements LayoutClientInterface
{
    const GRAMMAR_PATH = '/fmi/xml/FMPXMLLAYOUT.xml';

    /**
     * @var ConnectionInterface
     */
    private $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function execute(Command $command) : Layout
    {
        $xml = $this->connection->execute($command, self::GRAMMAR_PATH);
        $errorCode = (int) $xml->ERRORCODE;

        if ($errorCode > 0) {
            throw FileMakerException::fromErrorCode($errorCode);
        }

        return new Layout(
            (string) $xml->LAYOUT['DATABASE'],
            (string) $xml->LAYOUT['NAME'],
            ...$this->parseFields($xml, $this->parseValueLists($xml))
        );
    }

    private function parseValueLists(SimpleXMLElement $xml) : array
    {
        $valueLists = [];

        foreach ($xml->VALUELISTS->VALUELIST as $valueList) {
            $values = [];

            foreach ($valueList->VALUE as $value) {
                $values[] = new Value((string) $value['DISPLAY'], (string) $value);
            }

            $valueLists[(string) $valueList['NAME']] = new ValueList((string) $valueList['NAME'], ...$values);
        }

        return $valueLists;
    }

    private function parseFields(SimpleXMLElement $xml, array $valueLists) : array
    {
        $fields = [];

        foreach ($xml->LAYOUT->FIELD as $field) {
            $valueListName = (string) $field->STYLE['VALUELIST'];

            $fields[] = new Field(
                (string) $field['NAME'],
                (string) $field->STYLE['TYPE'],
                ('' !== $valueListName ? $valueLists[$valueListName] : null)
            );
        }

        return $fields;
    }
}
