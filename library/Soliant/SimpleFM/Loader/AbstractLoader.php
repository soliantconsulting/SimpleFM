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
        $username = $this->adapter->getUsername();
        $password = $this->adapter->getPassword();

        $this->username = $username;
        $this->credentials = empty($username) ? '' : $username . ':' . $password;
        return $this->credentials;
    }

    /**
     * @return string
     */
    protected function createArgs()
    {
        $dbname = $this->adapter->getDbname();
        $layoutname = $this->adapter->getLayoutname();
        $commandstring = $this->adapter->getCommandstring();

        $this->args = "-db=$dbname&-lay=$layoutname&$commandstring";
        return $this->args;
    }

    /**
     * @return string
     */
    protected function createCommandURL()
    {
        $credentials = $this->createCredentials();
        $args = $this->createArgs();

        $protocol = $this->adapter->getProtocol();
        $hostname = $this->adapter->getHostname();
        $port = $this->adapter->getPort();
        $uri = $this->adapter->getUri();

        $this->commandURL = "$protocol://$credentials@$hostname:$port$uri?$args";
        return $this->commandURL;
    }

    /**
     * @return void
     */
    protected function setAdapterCommandURLdebug()
    {
        $this->adapter->setCommandURLdebug(
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
}
