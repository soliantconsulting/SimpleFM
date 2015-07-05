<?php
namespace Soliant\SimpleFM\Parser;

use SimpleXMLElement;
use Soliant\SimpleFM\StringUtils;
use Soliant\SimpleFM\Exception\RuntimeException;
use Soliant\SimpleFM\Result\AbstractResult;

abstract class AbstractParser
{
    protected $xml;

    public function __construct($xml)
    {
        if ($xml instanceof SimpleXMLElement) {
            $this->xml = $xml;
        } else {
            $this->xml = simplexml_load_string($xml);
        }
    }

    abstract public function parse($commandUrlDebug);

    /**
     * SimpleXML does not throw Exceptions
     * It returns a SimpleXML object on success and false on error
     * See http://www.php.net/manual/en/simplexml.examples-errors.php
     * @param $result
     * @param $grammar
     * @return AbstractResult
     */
    protected function handleEmptyXml($resultClassName, $commandUrlDebug)
    {
        $simpleXmlErrors['xml'] = libxml_get_errors();
        libxml_clear_errors();

        $simpleXmlErrors['php'] = error_get_last();
        StringUtils::errorClearLast();

        $phpErrors = StringUtils::extractErrorFromPhpMessage($simpleXmlErrors['php']['message']);

        return StringUtils::createResult(
            $resultClassName,
            $commandUrlDebug,
            $phpErrors['errorCode'],
            $phpErrors['errorMessage'],
            $phpErrors['errorType']
        );
    }
}
