<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2015 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */
namespace Soliant\SimpleFM\Loader;

use Soliant\SimpleFM\Adapter;
use SimpleXMLElement;

class Mock extends AbstractLoader
{
    /**
     * @var string
     */
    protected $testXml;

    /**
     * @param null $testXml
     */
    public function __construct($testXml = null)
    {
        if ($testXml) {
            $this->setTestXml($testXml);
        }
    }

    /**
     * @param string $testXml
     * @return $this
     */
    public function setTestXml($testXml)
    {
        $this->testXml = $testXml;
        return $this;
    }

    /**
     * @param Adapter $adapter
     * @param null $testXmlOverride
     * @return SimpleXMLElement
     */
    public function load($testXmlOverride = null)
    {
        $testXml = $testXmlOverride ? $testXmlOverride : $this->testXml;
        $this->errorCapture();
        return $this->handleReturn($testXml);
    }
}
