<?php
namespace Soliant\SimpleFM\Parser;

use SimpleXMLElement;
use Soliant\SimpleFM\ErrorCodes;
use Soliant\SimpleFM\Result\FmResultSet;
use Soliant\SimpleFM\StringUtils;

class FmResultSetParser extends AbstractParser
{
    /**
     * @var int
     */
    private $counterI;

    /**
     * @var int
     */
    private $counterIi;

    /**
     * @var int
     */
    private $counterIii;

    /**
     * @var array
     */
    private $rows;

    /**
     * fmi/xml grammar
     */
    const GRAMMAR = '/fmi/xml/fmresultset.xml';

    /**
     * @var boolean
     */
    protected $rowsByRecId = false;

    /**
     * @param boolean $rowsByRecId
     * @return FmResultSetParser
     */
    public function setRowsByRecId($rowsByRecId)
    {
        $this->rowsByRecId = (boolean) $rowsByRecId;
        return $this;
    }

    public function parse($commandUrlDebug)
    {
        if (empty($this->xml)) {
            // No xml to parse so set a graceful return value here
            return $this->handleEmptyXml(FmResultSet::class, $commandUrlDebug);
        }

        $this->rows = [];

        /**
         *   simplexml fmresultset path reference:
         *   $fmresultset->resultset[0]->record[0]->field[0]->data[0]
         */
        // loop over rows
        $this->counterI = 0;
        foreach ($this->xml->resultset[0]->record as $row) {
            $this->parseRow($row);
        }

        $count = (int) $this->xml->resultset['count'];
        $fetchSize = (int) $this->xml->resultset['fetch-size'];

        $result = new FmResultSet(
            $commandUrlDebug,
            (int) $this->xml->error['code'],
            ErrorCodes::errorToEnglish((int) $this->xml->error['code']),
            'FileMaker',
            $count,
            $fetchSize,
            $this->rows
        );

        return $result;
    }

    /**
     * @param $row
     */
    private function parseRow($row)
    {
        $conditionalId = $this->getConditionalId($row);

        $this->rows[$conditionalId]['index'] = (int) $this->counterI;
        $this->rows[$conditionalId]['recid'] = (int) $row['record-id'];
        $this->rows[$conditionalId]['modid'] = (int) $row['mod-id'];

        foreach ($this->xml->resultset[0]->record[$this->counterI]->field as $field) {
            $fieldName = $this->extractFieldName($field);
            $fieldData = $this->extractFieldData($field);
            $this->rows[$conditionalId][$fieldName] = $fieldData;
        }

        // check if portals exist
        if (isset($this->xml->resultset[0]->record[0]->relatedset)) {
            // the portal index
            $this->counterIi = 0;
            // handle portals
            foreach ($this->xml->resultset[0]->record[0]->relatedset as $portal) {
                $this->parsePortal($row, $portal);
            }
        }
        ++$this->counterI;
    }

    /**
     * @param $row
     * @param $portal
     * @param $conditionalId
     */
    private function parsePortal($row, $portal)
    {
        $conditionalId = $this->getConditionalId($row);
        $portalName = (string) $portal['table'];

        $this->rows[$conditionalId][$portalName]['parentindex'] = (int) $this->counterI;
        $this->rows[$conditionalId][$portalName]['parentrecid'] = (int) $row['record-id'];
        $this->rows[$conditionalId][$portalName]['portalindex'] = (int) $this->counterIi;
        $this->rows[$conditionalId][$portalName]['portalrecordcount'] = (int) $portal['count'];

        // the portal row index
        $this->counterIii = 0;
        // handle portal rows
        foreach ($this->xml->resultset[0]->record[$this->counterI]->relatedset[$this->counterIi]->record as $portalRow) {
            $this->parsePortalRow($row, $portalRow, $portalName);
        }
        ++$this->counterIi;
    }

    /**
     * @param $portalRow
     * @param $conditionalId
     * @param $portalName
     * @param $portalConditionalId
     */
    private function parsePortalRow($row, $portalRow, $portalName)
    {
        $conditionalId = $this->getConditionalId($row);
        $portalConditionalId = $this->getPortalConditionalId($portalRow);
        $this->rows[$conditionalId][$portalName]['rows'][$portalConditionalId]['index'] = (int) $this->counterIii;
        $this->rows[$conditionalId][$portalName]['rows'][$portalConditionalId]['modid'] = (int) $portalRow['mod-id'];
        $this->rows[$conditionalId][$portalName]['rows'][$portalConditionalId]['recid'] = (int) $portalRow['record-id'];

        // handle portal fields
        foreach ($this->xml->resultset[0]->record[$this->counterI]->relatedset[$this->counterIi]->record[$this->counterIii]->field as $portalField) {
            $portalFieldName = $this->extractFieldName($portalField, $portalName);
            $portalFieldData = $this->extractFieldData($portalField);
            $this->rows[$conditionalId][$portalName]['rows'][$portalConditionalId][$portalFieldName] = $portalFieldData;
        }
        ++$this->counterIii;
    }

    /**
     * @param $row
     * @return int
     */
    private function getConditionalId($row)
    {
        return $this->rowsByRecId === true ? (int) $row['record-id'] : (int) $this->counterI;
    }

    /**
     * @param $portalRow
     * @return int
     */
    private function getPortalConditionalId($portalRow)
    {
        return $this->rowsByRecId === true ? (int) $portalRow['record-id'] : (int) $this->counterIii;
    }

    /**
     * @param array $field
     * @param null|string $portalName
     * @return string
     */
    private function extractFieldName(SimpleXMLElement $field, $portalName = null)
    {
        if ($portalName) {
            $fieldName = str_replace($portalName . '::', '', (string) $field['name']);
            if ($this->counterIii === 0) {
                StringUtils::fieldNameIsValid($fieldName);
            }
        } else {
            $fieldName = (string) $field['name'];
            if ($this->counterI === 0) {
                StringUtils::fieldNameIsValid($fieldName);
            }
        }

        return $fieldName;
    }

    private function extractFieldData(SimpleXMLElement $field)
    {
        if ($field->count() > 1) {
            $fieldData = [];
            foreach ($field->data as $data) {
                $fieldData[] = (string) $data;
            }
        } else {
            $fieldData = (string) $field->data;
        }
        return $fieldData;
    }
}
