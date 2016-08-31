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
use Soliant\SimpleFM\Exception\LoaderException;
use Soliant\SimpleFM\Result\AbstractResult;
use Soliant\SimpleFM\Result\FmLayout;
use Soliant\SimpleFM\Result\FmResultSet;
use Soliant\SimpleFM\StringUtils;

abstract class AbstractLoader
{

    /**
     * @var Adapter
     */
    protected $adapter;
    protected $credentials;
    protected $username;
    protected $args;
    protected $commandUrl;
    protected $postUrl;
    protected $throwErrors = false;
    protected $lastError = [];

    /**
     * @return Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param Adapter $adapter
     * @return AbstractLoader
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->prepare();
        return $this;
    }

    /**
     * @return SimpleXMLElement
     */
    abstract public function load();

    /**
     * @return bool
     */
    public function hasError()
    {
        if ($this->lastError && isset($this->lastError['errorCode']) && $this->lastError['errorCode']) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @return FmResultSet
     */
    public function getLastErrorResultFmResultSet()
    {
        return $this->getLastErrorResult(FmResultSet::class);
    }

    /**
     * @return FmLayout
     */
    public function getLastErrorResultFmLayout()
    {
        return $this->getLastErrorResult(FmLayout::class);
    }

    /**
     * @return AbstractResult
     */
    public function getLastErrorResult($resultClassName)
    {
        return StringUtils::createResult(
            $resultClassName,
            $this->getCommandUrlDebug(),
            $this->lastError['errorCode'],
            $this->lastError['errorMessage'],
            $this->lastError['errorType']
        );
    }

    /**
     * @param bool $throwErrors
     * @return bool
     */
    public function throwErrors($throwErrors = true)
    {
        $this->throwErrors = $throwErrors;
        return $this->throwErrors;
    }

    /**
     * @param string|null $error
     * @throws LoaderException
     */
    protected function errorCapture($error = null)
    {
        if ($error) {
            $this->lastError = StringUtils::extractErrorFromPhpMessage($error);
        } else {
            $this->lastError = StringUtils::extractErrorFromPhpMessage(error_get_last());
        }

        $this->lastError['debugUrl'] = $this->getCommandUrlDebug();

        if ($this->getLastError()['errorCode'] && $this->throwErrors) {
            throw new LoaderException(
                $this->getLastError()['errorMessage'],
                $this->getLastError()['errorCode']
            );
        }
    }

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

        $this->commandUrl = "$protocol://$credentials@$hostname:$port$uri?$args";
        $this->postUrl = "$protocol://$hostname:$port$uri";
        return $this->commandUrl;
    }

    /**
     * @return string
     */
    public function getCommandUrlDebug()
    {
        $debugUrl = $this->commandUrl;
        if (!empty($this->credentials)) {
            // strip the password out of the credentials
            $debugUrl = str_replace($this->credentials, $this->username . ':[...]', $this->commandUrl);
        }
        return (string) $debugUrl;
    }

    /**
     * @return void
     */
    protected function prepare()
    {
        StringUtils::errorClearLast();
        $this->createCredentials();
        $this->createArgs();
        $this->createCommandURL();
    }

    /**
     * @param string $data
     * @param null $error
     * @return SimpleXMLElement
     * @throws LoaderException
     */
    protected function handleReturn($data, $error = null)
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($data);

        $this->errorCapture($error);
        return $xml;
    }
}
