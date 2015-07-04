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
use Soliant\SimpleFM\Exception\LoaderException;

abstract class AbstractLoader
{

    /**
     * @var Adapter
     */
    protected $adapter;
    protected $credentials;
    protected $username;
    protected $args;
    protected $commandURL;

    /**
     * @param array $simpleFMAdapterRow
     * @return SimpleXMLElement
     */
    abstract public function load(Adapter $adapter);


    /**
     * @return string
     */
    protected function createCredentials()
    {
        $username = $this->adapter->getHostConnection()->getUserName();
        $password = $this->adapter->getHostConnection()->getPassword();

        $this->username = $username;
        $this->credentials = empty($username) ? '' : $username . ':' . $password;
        return $this->credentials;
    }

    /**
     * @return string
     */
    protected function createArgs()
    {
        $dbname = $this->adapter->getHostConnection()->getDbName();
        $layoutName = $this->adapter->getLayoutName();
        $commandString = $this->adapter->getCommandString();

        $this->args = "-db=$dbname&-lay=$layoutName&$commandString";
        return $this->args;
    }

    /**
     * @return string
     */
    protected function createCommandURL()
    {
        $credentials = $this->createCredentials();
        $args = $this->createArgs();

        $protocol = $this->adapter->getHostConnection()->getProtocol();
        $hostname = $this->adapter->getHostConnection()->getHostName();
        $port = $this->adapter->getHostConnection()->getPort();
        $uri = $this->adapter->getUri();

        $this->commandURL = "$protocol://$credentials@$hostname:$port$uri?$args";
        return $this->commandURL;
    }

    /**
     * @return void
     */
    protected function setAdapterCommandURLdebug()
    {
        $this->adapter->setCommandUrlDebug(
            empty($this->credentials) ? $this->commandURL : str_replace(
                $this->credentials,
                $this->username . ':[...]',
                $this->commandURL
            )
        );
    }

    /**
     * @return void
     */
    protected function prepare()
    {
        $this->createCredentials();
        $this->createArgs();
        $this->createCommandURL();
        $this->setAdapterCommandURLdebug();
    }

    /**
     * @param $data
     * @param $errorMessage
     * @return SimpleXMLElement
     * @throws LoaderException
     */
    protected function handleReturn($data, $errorMessage)
    {
        if (!$data) {
            throw new LoaderException($errorMessage);
        }
        libxml_use_internal_errors(true);
        return simplexml_load_string($data);
    }
}
