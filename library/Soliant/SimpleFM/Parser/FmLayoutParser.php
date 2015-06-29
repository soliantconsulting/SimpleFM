<?php
namespace Soliant\SimpleFM\Parser;

use Soliant\SimpleFM\StringUtils;
use Soliant\SimpleFM\Result\FmLayout;

class FmLayoutParser extends AbstractParser
{
    /**
     * fmi/xml grammar
     */
    const GRAMMAR = '/fmi/xml/FMPXMLLAYOUT.xml';

    public function parse()
    {
        $xml = $this->xml;

        // No xml to parse so return gracefully here
        if (empty($xml)) {
            return $this->handleEmptyXml(FmLayout::class);
        }

        $fields = array();
        $valueLists = array();

        $counterI = 0;
        // loop over LAYOUT fields
        foreach ($xml->LAYOUT[0]->FIELD as $field) {
            $fieldname = (string)$field->attributes()->NAME;
            // throw an exception if name not valid:
            StringUtils::fieldnameIsValid($fieldname);

            $fields[$counterI]['name'] = $fieldname;
            $fields[$counterI]['type'] = (string)$field->STYLE->attributes()->TYPE;
            $fields[$counterI]['valuelist'] = (string)$field->STYLE->attributes()->VALUELIST;
            ++$counterI;
        }

        $counterJ = 0;
        // loop over VALUELISTS
        foreach ($xml->VALUELISTS[0] as $valueList) {
            $valueLists[$counterJ]['name'] = (string)$valueList->attributes()->NAME;
            $valueLists[$counterJ]['values'] = array();
            $counterJj = 0;
            foreach ($valueList->VALUE as $value) {
                $valueLists[$counterJ]['values'][$counterJj]['value'] = (string)$value[0];
                $valueLists[$counterJ]['values'][$counterJj]['display'] = (string)$value->attributes()->DISPLAY;
                $counterJj++;
            }
            ++$counterJ;
        }

        $product = [];
        $product['build'] = (string)$xml->PRODUCT->attributes()->BUILD;
        $product['name'] = (string)$xml->PRODUCT->attributes()->NAME;
        $product['version'] = (string)$xml->PRODUCT->attributes()->VERSION;

        $layout = [];
        $layout['database'] = (string)$xml->LAYOUT->attributes()->DATABASE;
        $layout['name'] = (string)$xml->LAYOUT->attributes()->NAME;
        $layout['fields'] = $fields;

        $result = new FmLayout(
            $this->commandUrlDebug,
            (int)$xml->ERRORCODE,
            StringUtils::errorToEnglish((int)$xml->ERRORCODE),
            'FileMaker',
            $product,
            $layout,
            $valueLists
        );

        return $result;
    }
}
