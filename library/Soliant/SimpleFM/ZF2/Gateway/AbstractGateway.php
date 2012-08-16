<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2012 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Gateway;

use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;
use Soliant\SimpleFM\Adapter as SimpleFMAdapter;
use Soliant\SimpleFM\ZF2\Entity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractGateway 
{

    /**
     * @var \Zend\EventManager\EventManager
     */
    protected $eventManager;
    
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;
    
    /**
     * concrete \Soliant\SimpleFM\ZF2\Entity\AbstractEntity  class name
     * @var string
     */
    protected $entityName;
    
    /**
     * @var \Soliant\SimpleFM\Adapter
     */
    protected $simpleFMAdapter;

    /**
     * @param ServiceManager $serviceManager
     * @param AbstractEntity $entity
     * @param SimpleFMAdapter $simpleFMAdapter
     * @param string $layoutname
     */
    public function __construct(ServiceManager $serviceManager, AbstractEntity $entity, SimpleFMAdapter $simpleFMAdapter, $layoutname=NULL) 
    {
        $this->setServiceManager($serviceManager);
        $this->setSimpleFMAdapter($simpleFMAdapter->setLayoutname($layoutname));
        $this->entityName = get_class($entity);
    }
    
    /**
     * @param AbstractEntity $pointer
     * @return \Soliant\SimpleFM\ZF2\Entity\AbstractEntity
     */
    public function resolvePointer(AbstractEntity $pointer)
    {
        return $this->find($pointer->getRecid());
    }

    /**
     * @return SimpleFMAdapter
     */
    public function getSimpleFMAdapter()
    {
        return $this->simpleFMAdapter;
    }
    
    /**
     * @param SimpleFMAdapter $simpleFMAdapter
     * @return \Soliant\SimpleFM\ZF2\Gateway\AbstractGateway
     */
    public function setSimpleFMAdapter(SimpleFMAdapter $simpleFMAdapter)
    {
        $this->simpleFMAdapter = $simpleFMAdapter;
        return $this;
    }

    /**
     * @param ServiceManager $serviceManager
     * @return \Soliant\SimpleFM\ZF2\Gateway\AbstractGateway
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getLocator()
    {
        return $this->getServiceManager();
    }

    
    /**
     * Example return: Application\Entity\Entity
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Set the event manager to use with this object
     * @param EventManager $events
     * @return \Soliant\SimpleFM\ZF2\Gateway\AbstractGateway
     */
    public function setEventManager(EventManager $events)
    {
        $this->eventManager = $events;
        return $this;
    }

    /**
     * Retrieve the currently set event manager
     *
     * If none is initialized, an EventManager instance will be created with
     * the contexts of this class, the current class name (if extending this
     * class), and "bootstrap".
     *
     * @return EventManager
     */
    public function events()
    {
        if (!$this->eventManager instanceof EventManager) {
            $this->setEventManager(new EventManager(array(
                __CLASS__,
                get_called_class(),
            )));
        }
        return $this->eventManager;
    }
    
    /**
     * @param int $max
     * @param int $skip
     * @return array:
     */
    protected function maxSkipToCommandArray(int $max, int $skip)
    {
    
        $maxCommand = empty($max) ? array() : array('-max' => $max);
        $skipCommand = empty($skip) ? array() : array('-skip' => $skip);
    
        return array_merge($maxCommand, $skipCommand);
    }
    
    /**
     * @param array $sort
     * @return array
     */
    protected function sortArrayToCommandArray(array $sort)
    {
    
        // -sortfield.[1-9] = fully-qualified-field-name
        // -sortorder.[1-9] = [ascend|descend|value-list-name]
    
        if (empty($sort)) return array();
    
        $i = 1;
        $command = array();
        foreach ($sort as $field => $method){
            if ($i > 9) break; // FileMaker API limited to max 9 fields
    
            switch ($method) {
                case 'dsc':
                    $sortMethod = 'descend';
                    break;
                case 'desc':
                    $sortMethod = 'descend';
                    break;
                case 'descend':
                    $sortMethod = 'descend';
                    break;
                case 'asc':
                    $sortMethod = 'ascend';
                    break;
                case 'ascend':
                    $sortMethod = 'ascend';
                    break;
                case '':
                    $sortMethod = 'ascend';
                    break;
                case NULL:
                    $sortMethod = 'ascend';
                    break;
                default:
                    $sortMethod = $method;
                    break;
            }
    
            $command['-sortfield.' . $i] = $field;
            $command['-sortorder.' . $i] = $sortMethod;
            $i++;
        }
    
        return $command;
    
    }
}