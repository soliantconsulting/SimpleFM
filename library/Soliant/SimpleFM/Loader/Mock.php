<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\Loader;

require_once('AbstractLoader.php');

use Soliant\SimpleFM\Loader\AbstractLoader;
use Soliant\SimpleFM\Adapter;

class Mock extends AbstractLoader
{
    /**
     * @var string
     */
    protected $testXml;

    /**
     * @return the $testXml
     */
    public function getTestXml ()
    {
        return $this->testXml;
    }

    /**
     * @param string $testXml
     */
    public function setTestXml ($testXml)
    {
        $this->testXml = $testXml;
        return $this;
    }

    /**
     * @return SimpleXMLElement
     */
    public function load(Adapter $adapter, $testXmlOverride=NULL)
    {
        $this->adapter = $adapter;

        $testXml = $testXmlOverride ? $testXmlOverride : $this->testXml;

        self::prepare();

        libxml_use_internal_errors(true);

        return simplexml_load_string($testXml);

    }

}
