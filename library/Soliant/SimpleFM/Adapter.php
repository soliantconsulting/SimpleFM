<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM
 * @copyright Copyright (c) 2007-2015 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM;

use Soliant\SimpleFM\Loader\AbstractLoader;
use Soliant\SimpleFM\Loader\FilePostContents;
use Soliant\SimpleFM\HostConnection;
use Soliant\SimpleFM\StringUtils;
use Soliant\SimpleFM\Parser\FmResultSetParser;
use Soliant\SimpleFM\Parser\FmLayoutParser;

class Adapter
{
    /**
     * @var HostConnection
     */
    protected $hostConnection;

    /**
     * @var string
     */
    protected $layoutName = '';

    /**
     * @var string
     */
    protected $commandString = '-findany';

    /**
     * @var array
     */
    protected $commandArray = array('-findany' => '');

    /**
     * @var string
     */
    protected $uri = FmResultSetParser::GRAMMAR;

    /**
     * @var boolean
     */
    protected $rowsByRecId = false;

    /**
     * @var string
     */
    protected $commandUrlDebug;

    /**
     * @var AbstractLoader
     */
    protected $loader;

    /**
     * @param array|HostConnection $hostParams
     * @param null $loader
     */
    public function __construct($hostParams = array(), $loader = null)
    {
        if (!empty($hostParams)) {
            if ($hostParams instanceof HostConnection) {
                $this->hostConnection = $hostParams;
            } else {
                $this->setHostParams($hostParams);
            }
        }
        if ($loader instanceof AbstractLoader) {
            $this->loader = $loader;
        } else {
            $this->loader = new FilePostContents();
        }
    }

    /**
     * @deprecated
     * Create and update HostConnection class directly instead
     * @param array ($hostname, $dbname, $username, $password, $protocol, $port, $sslverifypeer)
     * @return $this
     */
    public function setHostParams($params = array())
    {
        $hostname = isset($params['hostname']) ? $params['hostname'] : null;
        $dbname = isset($params['dbname']) ? $params['dbname'] : null;
        $username = isset($params['username']) ? $params['username'] : null;
        $password = isset($params['password']) ? $params['password'] : null;
        $protocol = isset($params['protocol']) ? $params['protocol'] : null;
        $port = isset($params['port']) ? $params['port'] : null;
        $sslVerifyPeer = isset($params['sslverifypeer']) ? $params['sslverifypeer'] : true;

        $this->hostConnection = new HostConnection(
            $hostname,
            $dbname,
            $username,
            $password,
            $protocol,
            $port,
            $sslVerifyPeer
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getLayoutName()
    {
        return $this->layoutName;
    }

    /**
     * @param $layoutName
     * @return $this
     */
    public function setLayoutName($layoutName)
    {
        $this->layoutName = $layoutName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommandString()
    {
        return $this->commandString;
    }

    /**
     * @return array
     */
    public function getCommandArray()
    {
        return $this->commandArray;
    }

    /**
     * @param $commandString
     * @return $this
     */
    public function setCommandString($commandString)
    {
        $this->commandString = $commandString;
        $this->commandArray = StringUtils::explodeNameValueString($commandString);
        return $this;
    }

    /**
     * @param $commandArray
     * @return $this
     */
    public function setCommandArray($commandArray)
    {
        $this->commandArray = $commandArray;
        $this->commandString = StringUtils::repackCommandString($commandArray);
        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return $this
     */
    public function useLayoutGrammar()
    {
        $this->uri = FmLayoutParser::GRAMMAR;
        return $this;
    }

    /**
     * @return $this
     */
    public function useResultSetGrammar()
    {
        $this->uri = FmResultSetParser::GRAMMAR;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRowsByRecId()
    {
        return (boolean)$this->rowsByRecId;
    }

    /**
     * @param bool $rowsByRecId
     * @return $this
     */
    public function setRowsByRecId($rowsByRecId = false)
    {
        $this->rowsByRecId = (boolean)$rowsByRecId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommandUrlDebug()
    {
        return $this->commandUrlDebug;
    }

    /**
     * @param $commandUrlDebug
     * @return $this
     */
    public function setCommandUrlDebug($commandUrlDebug)
    {
        $this->commandUrlDebug = $commandUrlDebug;
        return $this;
    }

    /**
     * @return AbstractLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @param $loader
     * @return $this
     */
    public function setLoader($loader)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * @return HostConnection
     */
    public function getHostConnection()
    {
        return $this->hostConnection;
    }

    /**
     * @param HostConnection $hostConnection
     * @return Adapter
     */
    public function setHostConnection($hostConnection)
    {
        $this->hostConnection = $hostConnection;
        return $this;
    }

    /**
     * @return array
     */
    public function execute()
    {
        /**
         * SimpleXML does not throw errors
         * It returns a SimpleXML object on success and false on error
         * The xml parser methods have to be able to handle either case gracefully
         */
        $xml = $this->loader->load($this);

        $sfmresult = array();
        if ($this->uri == FmResultSetParser::GRAMMAR) {
            $sfmresult = $this->parseFmResultSet($xml);
        } elseif ($this->uri == FmLayoutParser::GRAMMAR) {
            $sfmresult = $this->parseFmpXmlLayout($xml);
        }

        return $sfmresult;
    }

    /**
     * @param $xml
     * @return array|mixed
     */
    protected function parseFmResultSet($xml)
    {
        $parser = new FmResultSetParser($xml, $this->getCommandUrlDebug());
        $parser->setRowsByRecId($this->getRowsByRecId());
        $result = $parser->parse();
        return $result->toArrayLc();
    }

    /**
     * @param $xml
     * @return array|mixed
     */
    protected function parseFmpXmlLayout($xml)
    {
        $parser = new FmLayoutParser($xml, $this->getCommandUrlDebug());
        $result = $parser->parse();
        return $result->toArrayLc();
    }
}
