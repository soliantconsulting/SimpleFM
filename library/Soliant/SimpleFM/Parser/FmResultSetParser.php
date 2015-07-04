<?php
namespace Soliant\SimpleFM\Parser;

use Soliant\SimpleFM\StringUtils;
use Soliant\SimpleFM\Result\FmResultSet;

class FmResultSetParser extends AbstractParser
{
    /**
     * fmi/xml grammar
     */
    const GRAMMAR = '/fmi/xml/fmresultset.xml';

    public function __construct($xml, $commandUrlDebug)
    {
        parent::__construct($xml, $commandUrlDebug, FmResultSet::class);
    }

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
        $this->rowsByRecId = (boolean)$rowsByRecId;
        return $this;
    }

    public function parse()
    {
        if (empty($this->xml)) {
            // No xml to parse so return gracefully here
            return $this->emptyResult;
        }

        $xml = $this->xml;
        $rows = array();

        /**
         *   simplexml fmresultset path reference:
         *   $fmresultset->resultset[0]->record[0]->field[0]->data[0]
         */
        // loop over rows
        $counterI = 0;
        foreach ($xml->resultset[0]->record as $row) {
            $conditional_id = $this->rowsByRecId === true ? (string)$row['record-id'] : (int)$counterI;

            $rows[$conditional_id]['index'] = (int)$counterI;
            $rows[$conditional_id]['recid'] = (int)$row['record-id'];
            $rows[$conditional_id]['modid'] = (int)$row['mod-id'];

            foreach ($xml->resultset[0]->record[$counterI]->field as $field) {
                $fieldname = (string)$field['name'];
                if (count($field) > 1) {
                    $fielddata = array();
                    foreach ($field->data as $data) {
                        $fielddata[] = (string)$data;
                    }
                } else {
                    $fielddata = (string)$field->data;
                }

                // validate fieldnames on first row
                $fieldNameIsValid = $counterI === 0 ? StringUtils::fieldnameIsValid($fieldname) : true;
                $rows[$conditional_id][$fieldname] = $fielddata;

            }
            // check if portals exist
            if (isset($xml->resultset[0]->record[0]->relatedset)) {
                // the portal index
                $counterIi = 0;
                // handle portals
                foreach ($xml->resultset[0]->record[0]->relatedset as $portal) {
                    $portalname = (string)$portal['table'];

                    $rows[$conditional_id][$portalname]['parentindex'] = (int)$counterI;
                    $rows[$conditional_id][$portalname]['parentrecid'] = (int)$row['record-id'];
                    $rows[$conditional_id][$portalname]['portalindex'] = (int)$counterIi;
                    /**
                     * @TODO Verify if next line is a bug where portalrecordcount may be returning same value for all
                     * portals. Test for possible issues with $portalname being non-unique.
                     */
                    $rows[$conditional_id][$portalname]['portalrecordcount'] = (int)$portal['count'];

                    // the portal row index
                    $counterIii = 0;
                    // handle portal rows
                    foreach ($xml->resultset[0]->record[$counterI]->relatedset[$counterIi]->record as $portal_row) {
                        $portal_cond_id = $this->rowsByRecId === true ? (int)$portal_row['record-id'] : $counterIii;

                        $rows[$conditional_id][$portalname]['rows'][$portal_cond_id]['index'] = (int)$counterIii;
                        $rows[$conditional_id][$portalname]['rows'][$portal_cond_id]['modid'] = (int)$portal_row['mod-id'];
                        $rows[$conditional_id][$portalname]['rows'][$portal_cond_id]['recid'] = (int)$portal_row['record-id'];

                        // handle portal fields
                        foreach ($xml->resultset[0]->record[$counterI]->relatedset[$counterIi]->record[$counterIii]->field as $portal_field) {
                            $portal_fieldname = (string)str_replace($portalname . '::', '', $portal_field['name']);
                            if (count($portal_field) > 1) {
                                $portal_fielddata = array();
                                foreach ($portal_field->data as $data) {
                                    $portal_fielddata[] = (string)$data;
                                }
                            } else {
                                $portal_fielddata = (string)$portal_field->data;
                            }

                            // validate fieldnames on first row
                            $fieldNameIsValid = $counterIii === 0 ? StringUtils::fieldnameIsValid($portal_fieldname) : true;
                            $rows[$conditional_id][$portalname]['rows'][$portal_cond_id][$portal_fieldname] = $portal_fielddata;
                        }
                        ++$counterIii;
                    }
                    ++$counterIi;
                }
            }
            ++$counterI;
        }

        $count = (int)$xml->resultset['count'];
        $fetchSize = (int)$xml->resultset['fetch-size'];

        $result = new FmResultSet(
            $this->commandUrlDebug,
            (int)$xml->error['code'],
            StringUtils::errorToEnglish((int)$xml->error['code']),
            'FileMaker',
            $count,
            $fetchSize,
            $rows
        );

        return $result;
    }
}
