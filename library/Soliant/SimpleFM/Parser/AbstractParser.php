<?php
namespace Soliant\SimpleFM\Parser;

use SimpleXMLElement;
use Soliant\SimpleFM\StringUtils;
use Soliant\SimpleFM\Exception\RuntimeException;
use Soliant\SimpleFM\Result\AbstractResult;

abstract class AbstractParser
{
    protected $xml;
    protected $commandUrlDebug;
    protected $emptyResult;

    public function __construct($xml, $commandUrlDebug, $resultClassName = null)
    {
        if ($xml instanceof SimpleXMLElement) {
            $this->xml = $xml;
        } else {
            $this->xml = simplexml_load_string($xml);
        }

        // No xml to parse so set a graceful return value here
        if (empty($this->xml)) {
            $this->emptyResult = $this->handleEmptyXml($resultClassName);
        }

        $this->commandUrlDebug = $commandUrlDebug;
    }

    abstract public function parse();

    /**
     * SimpleXML does not throw errors
     * It returns a SimpleXML object on success and false on error
     * See http://www.php.net/manual/en/simplexml.examples-errors.php
     * @param $result
     * @param $grammar
     * @return AbstractResult
     */
    protected function handleEmptyXml($resultClassName)
    {
        $simpleXmlErrors['xml'] = libxml_get_errors();
        $simpleXmlErrors['php'] = error_get_last();
        $phpErrors = StringUtils::extractErrorFromPhpMessage($simpleXmlErrors['php']['message']);
        $error = $phpErrors['error'];
        $errorText = $phpErrors['errortext'];
        $errorType = $phpErrors['errortype'];
        libxml_clear_errors();

        if (!class_exists($resultClassName)) {
            throw new RuntimeException(
                '$resultClassName must create an instance of Soliant\SimpleFM\Result\AbstractResult'
            );
        }

        /** @var AbstractResult $result */
        $result = new $resultClassName(
            $this->commandUrlDebug,
            $error,
            $errorText,
            $errorType
        );

        if (!$result instanceof AbstractResult) {
            throw new RuntimeException(
                '$resultClassName must create an instance of Soliant\SimpleFM\Result\AbstractResult'
            );
        }

        return $result;
    }
}
