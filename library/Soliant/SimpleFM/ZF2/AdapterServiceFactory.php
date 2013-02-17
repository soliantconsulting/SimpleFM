<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 * 
 * @package   Soliant\SimpleFM
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Soliant\SimpleFM\Adapter;

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
        return new Adapter($config['simple_fm_host_params']);
    }
}
