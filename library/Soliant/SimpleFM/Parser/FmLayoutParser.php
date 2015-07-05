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

    public function parse($commandUrlDebug)
    {
        if (empty($this->xml)) {
            // No xml to parse so set a graceful return value here
            return $this->handleEmptyXml(FmLayout::class, $commandUrlDebug);
        }

        $xml = $this->xml;
        $fields = array();
        $valueLists = array();

        // loop over LAYOUT fields
        $counterI = 0;
        foreach ($xml->LAYOUT[0]->FIELD as $field) {
            $fieldname = (string)$field->attributes()->NAME;
            // throw an exception if name not valid:
            StringUtils::fieldnameIsValid($fieldname);

            $fields[$counterI]['name'] = $fieldname;
            $fields[$counterI]['type'] = (string)$field->STYLE->attributes()->TYPE;
            $fields[$counterI]['valuelist'] = (string)$field->STYLE->attributes()->VALUELIST;
            ++$counterI;
        }

        // loop over VALUELISTS
        $counterJ = 0;
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
            $commandUrlDebug,
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
