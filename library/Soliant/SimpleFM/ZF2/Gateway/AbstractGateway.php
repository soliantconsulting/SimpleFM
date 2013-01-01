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
use Doctrine\Common\Collections\ArrayCollection;
use Soliant\SimpleFM\Adapter as SimpleFMAdapter;
use Soliant\SimpleFM\Exception\ErrorException;
use Soliant\SimpleFM\Exception\FileMakerException;
use Soliant\SimpleFM\Exception\HttpException;
use Soliant\SimpleFM\Exception\XmlException;
use Soliant\SimpleFM\ZF2\Entity\EntityInterface;
use Soliant\SimpleFM\ZF2\Entity\SerializableEntityInterface;

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
     * The fully qualified class name for a concrete implementation of
     * \Soliant\SimpleFM\ZF2\Entity\EntityInterface
     * @var string
     */
    protected $entityPointerName;
    
    /**
     * The FileMaker Layout assigned to the $entityPointerName
     * @var string
     */
    protected $entityPointerLayout;
    
    /**
     * The fully qualified class name for the object that extends
     * $this->entityPointerName and implements 
     * \Soliant\SimpleFM\ZF2\Entity\SerializableEntityInterface
     * @var string
     */
    protected $entityName;
    
    /**
     * The FileMaker Layout assigned to the $entityPointerName
     * @var string
     */
    protected $entityLayout;
    
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
    public function __construct(ServiceManager $serviceManager, SerializableEntityInterface $entity, SimpleFMAdapter $simpleFMAdapter, $layoutnamePointer=NULL, $layoutname=NULL) 
    {
        $this->setServiceManager($serviceManager);
        $this->setSimpleFMAdapter($simpleFMAdapter);
        $this->setEntityPointerName($entity->getEntityPointerName());
        $this->setEntityPointerLayout($layoutnamePointer);
        $this->setEntityName($entity->getEntityName());
        $this->setEntityLayout($layoutname);
    }
    
    /**
     * @param AbstractEntity $pointer
     * @return \Soliant\SimpleFM\ZF2\Entity\SerializableEntityInterface
     */
    public function resolvePointer(EntityInterface $pointer)
    {
        return $this->find($pointer->getRecid());
    }
    
    public function find($recid)
    {
        $commandArray = array('-recid' => $recid, '-find' => NULL);
        $this->simpleFMAdapter
             ->setCommandArray($commandArray)
             ->setLayoutname($this->getEntityLayout());
        $result = $this->handleAdapterResult($this->simpleFMAdapter->execute());
        $entity = new $this->entityName($result['rows'][0]);
        return $entity;
    }
    
    public function findOneBy(array $search)
    {
        $commandArray = array_merge(
            $search, 
            array(
                '-max' => '1',
                '-find' => NULL
            )
        );
        $this->simpleFMAdapter
             ->setCommandArray($commandArray)
             ->setLayoutname($this->getEntityLayout());
        $result = $this->handleAdapterResult($this->simpleFMAdapter->execute());
        $entity = new $this->entityName($result['rows'][0]);
        return $entity;
    }
    
    public function findAll(array $sort = array(), $max = NULL, $skip = NULL)
    {
        $commandArray = array_merge(
            $this->sortArrayToCommandArray($sort),
            $this->maxSkipToCommandArray($max, $skip),
            array('-findall' => NULL)
        );
        $this->simpleFMAdapter
             ->setCommandArray($commandArray)
             ->setLayoutname($this->getEntityPointerLayout());
        $result = $this->handleAdapterResult($this->simpleFMAdapter->execute());    
        return $this->rowsToArrayCollection($result['rows']);
    
    }
    
    public function findBy(array $search, array $sort = array(), $max = NULL, $skip = NULL)
    {
        $commandArray = array_merge(
            $search,
            $this->sortArrayToCommandArray($sort),
            $this->maxSkipToCommandArray($max, $skip),
            array('-findall' => NULL)
        );
        $this->simpleFMAdapter
             ->setCommandArray($commandArray)
             ->setLayoutname($this->getEntityPointerLayout());
        $result = $this->handleAdapterResult($this->simpleFMAdapter->execute());    
        return $this->rowsToArrayCollection($result['rows']);
    }
    
    public function create(SerializableEntityInterface $entity)
    {
        $serializedValues = $entity->serialize();
        unset($serializedValues['-recid']);
        unset($serializedValues['-modid']);
        $commandArray = array_merge(
            $serializedValues,
            array('-new' => NULL)
        );
        $this->simpleFMAdapter
             ->setCommandArray($commandArray)
             ->setLayoutname($this->getEntityLayout());
        $result = $this->handleAdapterResult($this->simpleFMAdapter->execute());    
        $entity = new $this->entityName($result['rows'][0]);
        return $entity;
    }
    
    public function edit(SerializableEntityInterface $entity)
    {
        $commandArray = array_merge(
            $entity->serialize(),
            array(
                '-edit' => NULL,
            )
        );
        $this->simpleFMAdapter
             ->setCommandArray($commandArray)
             ->setLayoutname($this->getEntityLayout());
        $result = $this->handleAdapterResult($this->simpleFMAdapter->execute());    
        
        $entity = new $this->entityName($result['rows'][0]);
        return $entity;
    }
    
    public function delete(EntityInterface $entity)
    {
        $commandArray = array(
            '-recid' => $entity->getRecid(),
            '-modid' => $entity->getModid(),
            '-delete' => NULL,
        );
        $this->simpleFMAdapter
             ->setCommandArray($commandArray)
             ->setLayoutname($this->getEntityPointerLayout());
        $result = $this->handleAdapterResult($this->simpleFMAdapter->execute());    
        return true;
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
     * @return the $entityPointerLayout
     */
    public function getEntityPointerLayout ()
    {
        return $this->entityPointerLayout;
    }

	/**
     * @param string $entityPointerLayout
     * @return \Soliant\SimpleFM\ZF2\Gateway\AbstractGateway
     */
    public function setEntityPointerLayout ($entityPointerLayout)
    {
        $this->entityPointerLayout = $entityPointerLayout;
        return $this;
    }

	/**
     * @return the $entityLayout
     */
    public function getEntityLayout ()
    {
        return $this->entityLayout;
    }

	/**
     * @param string $entityLayout
     * @return \Soliant\SimpleFM\ZF2\Gateway\AbstractGateway
     */
    public function setEntityLayout ($entityLayout)
    {
        $this->entityLayout = $entityLayout;
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
     * @return the $entityPointerName
     */
    public function getEntityPointerName ()
    {
        return $this->entityPointerName;
    }

	/**
     * @param string $entityPointerName
     * @return \Soliant\SimpleFM\ZF2\Gateway\AbstractGateway
     */
    public function setEntityPointerName ($entityPointerName)
    {
        $this->entityPointerName = $entityPointerName;
        return $this;
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
     * @param string $entityName
     * @return \Soliant\SimpleFM\ZF2\Gateway\AbstractGateway
     */
    public function setEntityName ($entityName)
    {
        $this->entityName = $entityName;
        return $this;
    }

    /**
     * @return the $eventManager
     */
    public function getEventManager ()
    {
        return $this->eventManager;
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
    protected function maxSkipToCommandArray($max = NULL, $skip = NULL)
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
    
    /**
     * @param array $rows
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function rowsToArrayCollection(array $rows)
    {
        $collection = new ArrayCollection();
        if (!empty($rows)){
            foreach($rows as $row){
                $collection[] = new $this->entityPointerName($row);
            }
        }
        
        return $collection;
    }
    
    protected function handleAdapterResult($simpleFMAdapterResult)
    {
        $message = $simpleFMAdapterResult['errortype'] . ' Error ' . $simpleFMAdapterResult['error'] . ': ' . 
                       $simpleFMAdapterResult['errortext'] . '. ' . $simpleFMAdapterResult['url'];
        
        if ($simpleFMAdapterResult['error'] === 0){
            return $simpleFMAdapterResult;
        
        } elseif ($simpleFMAdapterResult['errortype'] == 'FileMaker') {
            throw new FileMakerException($message, $simpleFMAdapterResult['error']);
        
        } elseif ($simpleFMAdapterResult['errortype'] == 'HTTP') {
            throw new HttpException($message, $simpleFMAdapterResult['error']);
            
        } elseif ($simpleFMAdapterResult['errortype'] == 'XML') {
            throw new XmlException($message, $simpleFMAdapterResult['error']);

        } else {
            throw new ErrorException($message, $simpleFMAdapterResult['error']);
        }
    }
    
}


