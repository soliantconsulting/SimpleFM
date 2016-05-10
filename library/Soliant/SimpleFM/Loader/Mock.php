<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2016 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */
namespace Soliant\SimpleFM\Loader;

use SimpleXMLElement;
use Soliant\SimpleFM\Adapter;

class Mock extends AbstractLoader
{
    /**
     * @var string
     */
    protected $testXml;

    /**
     * @var string
     */
    protected $mockError;

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
     * @param string $mockError
     * @return Mock
     */
    public function setMockError($mockError)
    {
        $this->mockError = $mockError;
        return $this;
    }

    /**
     * @param Adapter $adapter
     * @param null $testXmlOverride
     * @return SimpleXMLElement
     */
    public function load($testXmlOverride = null)
    {
        $this->prepare();
        $testXml = $testXmlOverride ? $testXmlOverride : $this->testXml;
        if ($this->mockError) {
            return $this->handleReturn($testXml, $this->mockError);
        }
        return $this->handleReturn($testXml);
    }
}
