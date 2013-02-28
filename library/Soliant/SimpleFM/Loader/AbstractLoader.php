<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\Loader;

require_once('LoaderInterface.php');

abstract class AbstractLoader implements LoaderInterface
{
    
    protected $credentials;
    protected $username;
    protected $args;
    protected $commandURL;
    
    protected function createCredentials()
    {
        $username = $this->adapter->getUsername();
        $password = $this->adapter->getPassword();
    
        $this->username = $username;
        $this->credentials = empty($username)?'':$username.':'.$password;
        return $this->credentials;
    }
    
    protected function createArgs()
    {
        $dbname = $this->adapter->getDbname();
        $layoutname = $this->adapter->getLayoutname();
        $commandstring = $this->adapter->getCommandstring();
    
        $this->args = "-db=$dbname&-lay=$layoutname&$commandstring";
        return $this->args;
    }
    
    protected function createCommandURL()
    {
        $credentials = self::createCredentials();
        $args = self::createArgs();
    
        $protocol = $this->adapter->getProtocol();
        $hostname = $this->adapter->getHostname();
        $port = $this->adapter->getPort();
        $fmresultsetUri = $this->adapter->getFmresultsetUri();
    
        $this->commandURL = "$protocol://$credentials@$hostname:$port$fmresultsetUri?$args";
        return $this->commandURL;
    }

    protected function setAdapterCommandURLdebug()
    {
        $this->adapter->setCommandURLdebug(empty($this->credentials)?$this->commandURL:str_replace($this->credentials, $this->username.':[...]', $this->commandURL));
    }
    
    protected function prepare()
    {
        self::createCredentials();
        self::createArgs();
        self::createCommandURL();
        self::setAdapterCommandURLdebug();
    }
    
}