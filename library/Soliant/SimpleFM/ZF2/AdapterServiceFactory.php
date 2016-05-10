<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM
 * @copyright Copyright (c) 2007-2016 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2;

use Soliant\SimpleFM\Adapter;
use Soliant\SimpleFM\Exception\InvalidArgumentException;
use Soliant\SimpleFM\HostConnection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdapterServiceFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $sm)
    {
        $config = $sm->get('config');

        if (!isset($config['simple_fm_host_params']) ||
            !isset($config['simple_fm_host_params']['hostName']) ||
            !isset($config['simple_fm_host_params']['dbName']) ||
            !isset($config['simple_fm_host_params']['userName']) ||
            !isset($config['simple_fm_host_params']['password'])
        ) {
            throw new InvalidArgumentException(
                "'simple_fm_host_params' config is mandatory with 'hostName', 'dbName', 'userName', 'password'"
            );
        }

        $hostParams = $config['simple_fm_host_params'];

        // mandatory params
        $hostName = $hostParams['hostName'];
        $dbName = $hostParams['dbName'];
        $userName = $hostParams['userName'];
        $password = $hostParams['password'];

        // optional params
        $protocol = isset($hostParams['protocol']) && $hostParams['protocol'] ? $hostParams['protocol'] : 'http';
        $port = isset($hostParams['port']) ? $hostParams['port'] : 80;
        $sslVerifyPeer = isset($hostParams['sslVerifyPeer']) ? $hostParams['sslVerifyPeer'] : true;

        $hostConnection = new HostConnection(
            $hostName,
            $dbName,
            $userName,
            $password,
            $protocol,
            $port,
            $sslVerifyPeer
        );

        return new Adapter($hostConnection);
    }
}
