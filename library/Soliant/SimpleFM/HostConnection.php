<?php
namespace Soliant\SimpleFM;

use Soliant\SimpleFM\Exception\InvalidArgumentException;

class HostConnection
{
    /**
     * @var string
     */
    protected $hostName = '127.0.0.1';

    /**
     * @var string
     */
    protected $dbName = '';

    /**
     * @var string
     */
    protected $userName = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var string
     */
    protected $protocol = 'http';

    /**
     * @var int
     */
    protected $port = 80;

    /**
     * @var boolean
     */
    protected $sslVerifyPeer = true;

    public function __construct(
        $hostName,
        $dbName,
        $userName,
        $password,
        $protocol = 'http',
        $port = 80,
        $sslVerifyPeer = true
    ) {
        $this->hostName = $hostName ? : null;
        $this->dbName = $dbName ? : null;
        $this->userName = $userName ? : null;
        $this->password = $password ? : null;

        if ($protocol) {
            $this->setProtocol($protocol);
        }
        if ($port) {
            $this->setPort($port);
        }

        $this->setSslVerifyPeer((boolean)$sslVerifyPeer);
    }

    /**
     * @return string
     */
    public function getHostName()
    {
        return $this->hostName;
    }

    /**
     * @param $hostName
     * @return $this
     */
    public function setHostName($hostName)
    {
        $this->hostName = $hostName;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param $userName
     * @return $this
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
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
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * @param $dbName
     * @return $this
     */
    public function setDbName($dbName)
    {
        $this->dbName = $dbName;
        return $this;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param $protocol
     * @return $this
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
     * @return bool
     */
    public function getSslVerifyPeer()
    {
        return (boolean)$this->sslVerifyPeer;
    }

    /**
     * @param $sslVerifyPeer
     * @return $this
     */
    public function setSslVerifyPeer($sslVerifyPeer)
    {
        $this->sslVerifyPeer = (boolean)$sslVerifyPeer;
        return $this;
    }

    /**
     * @return int
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
     * @param $port
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }
}
