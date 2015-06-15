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
use Soliant\SimpleFM\Exception\InvalidArgumentException;
use Soliant\SimpleFM\StringUtils;

class Adapter
{

    /**
     * fmi/xml grammars
     */
    const FMRESULTSET_URI = '/fmi/xml/fmresultset.xml';
    const FMPXMLLAYOUT_URI = '/fmi/xml/FMPXMLLAYOUT.xml';

    /**
     * @var string
     */
    protected $hostname = '127.0.0.1';

    /**
     * @var string
     */
    protected $dbname = '';

    /**
     * @var string
     */
    protected $layoutname = '';

    /**
     * @var string
     */
    protected $commandstring = '-findany';

    /**
     * @var array
     */
    protected $commandarray = array('-findany' => '');

    /**
     * @var string
     */
    protected $username = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var string
     */
    protected $protocol = 'http';

    /**
     * @var boolean
     */
    protected $sslverifypeer = true;

    /**
     * @var int
     */
    protected $port = 80;

    /**
     * @var string
     */
    protected $uri = self::FMRESULTSET_URI;

    /**
     * @var boolean
     */
    protected $rowsbyrecid = false;

    /**
     * @var string
     */
    protected $commandURLdebug;

    /**
     * @var AbstractLoader
     */
    protected $loader;

    public function __construct(array $hostParams = array(), $loader = null)
    {
        if (!empty($hostParams)) {
            $this->setHostParams($hostParams);
        }
        if ($loader instanceof AbstractLoader) {
            $this->loader = $loader;
        } else {
            $this->loader = new FilePostContents();
        }
    }

    /**
     * Bulk setter for host args
     * @param array ($hostname, $dbname, $username, $password)
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setHostParams($params = array())
    {
        $this->hostname = @$params['hostname'];
        $this->dbname = @$params['dbname'];
        $this->username = @$params['username'];
        $this->password = @$params['password'];

        if (isset($params['protocol'])) {
            $this->setProtocol($params['protocol']);
        }
        if (isset($params['port'])) {
            $this->setPort($params['port']);
        }
        if (isset($params['sslverifypeer'])) {
            $this->setSslverifypeer($params['sslverifypeer']);
        }

        return $this;
    }

    /**
     * Bulk setter for credentials
     * @param array ($username, $password)
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setCredentials($params = array())
    {
        $this->username = @$params['username'];
        $this->password = @$params['password'];
        return $this;
    }

    /**
     * Bulk setter for call args
     * @param array ($layoutname, $commandstring)
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setCallParams($params = array())
    {
        $this->layoutname = @$params['layoutname'];
        $this->commandstring = @$params['commandstring'];
        return $this;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getDbname()
    {
        return $this->dbname;
    }

    /**
     * @param string $dbname
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setDbname($dbname)
    {
        $this->dbname = $dbname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLayoutname()
    {
        return $this->layoutname;
    }

    /**
     * @param string $layoutname
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setLayoutname($layoutname)
    {
        $this->layoutname = $layoutname;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommandstring()
    {
        return $this->commandstring;
    }

    /**
     * @return array
     */
    public function getCommandarray()
    {
        return $this->commandarray;
    }

    /**
     * @param string $commandString
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setCommandstring($commandString)
    {
        $this->commandstring = $commandString;
        $this->commandarray = StringUtils::explodeNameValueString($commandString);
        return $this;
    }

    /**
     * @param array $commandArray
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setCommandarray($commandArray)
    {
        $this->commandarray = $commandArray;
        $this->commandstring = StringUtils::repackCommandString($commandArray);
        return $this;
    }

    /**
     * @return $protocol
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param string $protocol
     * @throws InvalidArgumentException
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setProtocol($protocol)
    {
        if (in_array($protocol, array('http', 'https'))) {
            $this->protocol = $protocol;
        } else {
            throw new InvalidArgumentException('setProtocol() accepts only "http" or "https" as an argument.');
        }
        return $this;
    }

    /**
     * @return $sslVerifyPeer
     */
    public function getSslverifypeer()
    {
        return (boolean)$this->sslverifypeer;
    }

    /**
     * @param boolean $sslverifypeer
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setSslverifypeer($sslverifypeer)
    {
        $this->sslverifypeer = (boolean)$sslverifypeer;
        return $this;
    }

    /**
     * @return $port
     */
    public function getPort()
    {
        if (empty($this->port)) {
            if ($this->getProtocol() == 'https') {
                $this->setPort('443');
            } elseif ($this->getProtocol() == 'http') {
                $this->setPort('80');
            }
        }
        return $this->port;
    }

    /**
     * @param int $port
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setPort($port)
    {
        $this->port = $port;
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
     * @return \Soliant\SimpleFM\Adapter
     */
    public function useLayoutGrammar()
    {
        $this->uri = self::FMPXMLLAYOUT_URI;
        return $this;
    }

    /**
     * @return \Soliant\SimpleFM\Adapter
     */
    public function useResultsetGrammar()
    {
        $this->uri = self::FMRESULTSET_URI;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRowsbyrecid()
    {
        return (boolean)$this->rowsbyrecid;
    }

    /**
     * @param boolean $rowsByRecId
     * @return \Soliant\SimpleFM\Adapter
     */
    public function setRowsbyrecid($rowsByRecId = false)
    {
        $this->rowsbyrecid = (boolean)$rowsByRecId;
        return $this;
    }

    /**
     * @return $commandURLdebug
     */
    public function getCommandURLdebug()
    {
        return $this->commandURLdebug;
    }

    /**
     * @param string $commandURLdebug
     */
    public function setCommandURLdebug($commandURLdebug)
    {
        $this->commandURLdebug = $commandURLdebug;
        return $this;
    }

    /**
     * @return $loader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @param AbstractLoader $loader
     */
    public function setLoader($loader)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * @return array
     */
    public function execute()
    {
        @$xml = $this->loader->load($this);

        $sfmresult = array();
        if ($this->uri == self::FMRESULTSET_URI) {
            $sfmresult = $this->parseFmResultSet($xml);
        } elseif ($this->uri == self::FMPXMLLAYOUT_URI) {
            $sfmresult = $this->parseFmpXmlLayout($xml);
        }

        return $sfmresult;
    }

    private function handleEmptyXml($result, $grammar)
    {
        $simpleXmlErrors['xml'] = libxml_get_errors();
        $simpleXmlErrors['php'] = error_get_last();
        $phpErrors = StringUtils::extractErrorFromPhpMessage($simpleXmlErrors['php']['message']);
        $result['error'] = $phpErrors['error'];
        $result['errortext'] = $phpErrors['errortext'];
        $result['errortype'] = $phpErrors['errortype'];

        if (self::FMRESULTSET_URI == $grammar) {
            $result['count'] = null;
            $result['fetchsize'] = null;
            $result['rows'] = null;
        }

        if (self::FMPXMLLAYOUT_URI == $grammar) {
            $result['product'] = null;
            $result['layout'] = null;
            $result['valuelists'] = null;
        }

        libxml_clear_errors();
        return $result;
    }

    /**
     * @param $xml
     * @return array
     */
    protected function parseFmResultSet($xml)
    {
        $result = array();
        $result['url'] = $this->getCommandURLdebug();

        // No xml to parse so return here
        if (empty($xml)) {
            return $this->handleEmptyXml($result, self::FMRESULTSET_URI);
        }

        $rows = array();

        /**
         *   simplexml fmresultset path reference:
         *   $fmresultset->resultset[0]->record[0]->field[0]->data[0]
         */
        // loop over rows
        $counterI = 0;
        foreach ($xml->resultset[0]->record as $row) {
            $conditional_id = $this->rowsbyrecid === true ? (string)$row['record-id'] : (int)$counterI;

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
                        $portal_conditional_id = $this->rowsbyrecid === true ? (int)$portal_row['record-id'] : $counterIii;

                        $rows[$conditional_id][$portalname]['rows'][$portal_conditional_id]['index'] = (int)$counterIii;
                        $rows[$conditional_id][$portalname]['rows'][$portal_conditional_id]['modid'] = (int)$portal_row['mod-id'];
                        $rows[$conditional_id][$portalname]['rows'][$portal_conditional_id]['recid'] = (int)$portal_row['record-id'];

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
                            $rows[$conditional_id][$portalname]['rows'][$portal_conditional_id][$portal_fieldname] = $portal_fielddata;
                        }
                        ++$counterIii;
                    }
                    ++$counterIi;
                }
            }
            ++$counterI;
        }

        $simplexmlerrors = null;
        $result['error'] = (int)$xml->error['code'];
        $result['errortext'] = StringUtils::errorToEnglish($result['error']);
        $result['errortype'] = 'FileMaker';
        $result['count'] = (string)$xml->resultset['count'];
        $result['fetchsize'] = (string)$xml->resultset['fetch-size'];
        $result['rows'] = $rows;

        return $result;
    }


    /**
     * @param $xml
     * @return array
     */
    protected function parseFmpXmlLayout($xml)
    {
        $result = array();
        $result['url'] = $this->getCommandURLdebug();

        // No xml to parse so return here
        if (empty($xml)) {
            return $this->handleEmptyXml($result, self::FMPXMLLAYOUT_URI);
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

        $simplexmlerrors = null;
        $result['error'] = (int)$xml->ERRORCODE;
        $result['errortext'] = StringUtils::errorToEnglish($result['error']);
        $result['errortype'] = 'FileMaker';
        $result['product']['build'] = (string)$xml->PRODUCT->attributes()->BUILD;
        $result['product']['name'] = (string)$xml->PRODUCT->attributes()->NAME;
        $result['product']['version'] = (string)$xml->PRODUCT->attributes()->VERSION;
        $result['layout']['database'] = (string)$xml->LAYOUT->attributes()->DATABASE;
        $result['layout']['name'] = (string)$xml->LAYOUT->attributes()->NAME;
        $result['layout']['fields'] = $fields;
        $result['valuelists'] = $valueLists;

        return $result;
    }

    /**
     * @deprecated
     * Use StringUtils::displayXmlError directly instead
     * @param $libxmlError
     * @param $xml
     * @return string
     */
    public static function displayXmlError($libxmlError, $xml)
    {
        return StringUtils::displayXmlError($libxmlError, $xml);
    }

    /**
     * @deprecated
     * Use StringUtils::extractErrorFromPhpMessage directly instead
     * @param string $httpErrorString
     * @return mixed
     */
    public static function extractErrorFromPhpMessage($httpErrorString)
    {
        return StringUtils::extractErrorFromPhpMessage($httpErrorString);
    }

    /**
     * @deprecated
     * Use StringUtils::errorToEnglish directly instead
     * @param int $errorNum
     * @return string
     */
    public static function errorToEnglish($errorNum = '-1')
    {
        return StringUtils::errorToEnglish($errorNum);
    }
}
