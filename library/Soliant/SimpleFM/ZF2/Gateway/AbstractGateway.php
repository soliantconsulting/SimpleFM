<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Gateway;

use Doctrine\Common\Collections\ArrayCollection;
use Soliant\SimpleFM\Adapter as SimpleFMAdapter;
use Soliant\SimpleFM\Exception\ErrorException;
use Soliant\SimpleFM\Exception\FileMakerException;
use Soliant\SimpleFM\Exception\HttpException;
use Soliant\SimpleFM\Exception\XmlException;
use Soliant\SimpleFM\Exception\InvalidArgumentException;
use Soliant\SimpleFM\ZF2\Entity\AbstractEntity;
use Soliant\SimpleFM\ZF2\Authentication\Mapper\Identity;

abstract class AbstractGateway
{

    /**
     * The fully qualified class name for an object that implements
     * \Soliant\SimpleFM\ZF2\Entity\AbstractEntity
     * @var string
     */
    protected $entityName;

    /**
     * The FileMaker Layout assigned to the $entityPointerName
     * @var string
     */
    protected $entityLayout;

    /**
     * @var array
     */
    protected $fieldMap;

    /**
     * @var \Soliant\SimpleFM\Adapter
     */
    protected $simpleFMAdapter;

    /**
     * @param ServiceManager $serviceManager
     * @param AbstractEntity $entity
     * @param SimpleFMAdapter $simpleFMAdapter
     */
    public function __construct($fieldMap, AbstractEntity $entity, SimpleFMAdapter $simpleFMAdapter, Identity $identity=NULL, $encryptionKey=NULL )
    {
        if (!is_array($fieldMap)){
            throw new InvalidArgumentException('$fieldMap must be an array.');
        }

        $this->setSimpleFMAdapter($simpleFMAdapter);
        $this->setEntityName(get_class($entity));
        $this->setEntityLayout($entity->getDefaultWriteLayoutName());

        if (!array_key_exists($this->getEntityName(), $fieldMap)){
            throw new InvalidArgumentException($this->getEntityName() . ' is missing from $fieldMap.');
        }

        $this->fieldMap = $fieldMap;

        if (!array_key_exists('writeable', $this->fieldMap[$this->getEntityName()])){
            throw new InvalidArgumentException($this->getEntityName() . ' fieldMap is missing from "writeable" array.');
        }

        if (!array_key_exists('readonly', $this->fieldMap[$this->getEntityName()])){
            throw new InvalidArgumentException($this->getEntityName() . ' fieldMap is missing from "readonly" array.');
        }

        if (!empty($identity) && !empty($encryptionKey)) {
            $this->simpleFMAdapter->setUsername($identity->getUsername());
            $this->simpleFMAdapter->setPassword($identity->getPassword($encryptionKey));
        }

    }

    /**
     * @param AbstractEntity $entity
     * @param string $entityLayout
     * @return \Soliant\SimpleFM\ZF2\Entity\AbstractEntity
     */
    public function resolveEntity(AbstractEntity $entity, $entityLayout=NULL)
    {
        if (!empty($entityLayout)){
            $this->setEntityLayout($entityLayout);
        }
        return $this->find($entity->getRecid());
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
             ->setLayoutname($this->getEntityLayout());
        $result = $this->handleAdapterResult($this->simpleFMAdapter->execute());
        return $this->rowsToArrayCollection($result['rows']);
    }

    public function findBy(array $search, array $sort = array(), $max = NULL, $skip = NULL)
    {
        $commandArray = array_merge(
            $search,
            $this->sortArrayToCommandArray($sort),
            $this->maxSkipToCommandArray($max, $skip),
            array('-find' => NULL)
        );
        $this->simpleFMAdapter
             ->setCommandArray($commandArray)
             ->setLayoutname($this->getEntityLayout());
        $result = $this->handleAdapterResult($this->simpleFMAdapter->execute());
        return $this->rowsToArrayCollection($result['rows']);
    }

    public function create(AbstractEntity $entity)
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

    public function edit(AbstractEntity $entity)
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

    public function delete(AbstractEntity $entity)
    {
        $commandArray = array(
            '-recid' => $entity->getRecid(),
            '-modid' => $entity->getModid(),
            '-delete' => NULL,
        );
        $this->simpleFMAdapter
             ->setCommandArray($commandArray)
             ->setLayoutname($this->getEntityLayout());
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
     * @return the $fieldMap
     */
    public function getFieldMap() {
        return $this->fieldMap;
    }

    /**
     * @param multitype: $fieldMap
     */
    public function setFieldMap($fieldMap) {
        $this->fieldMap = $fieldMap;
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
                $collection[] = new $this->entityName($this->fieldMap, $row);
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


