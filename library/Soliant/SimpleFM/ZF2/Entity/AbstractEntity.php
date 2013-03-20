<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Entity;

use Soliant\SimpleFM\Exception\InvalidArgumentException;

abstract class AbstractEntity
{
    /**
     * @var int
     */
    protected $recid;

    /**
     * @var int
     */
    protected $modid;

    /**
     * @var array
     */
    protected $simpleFMAdapterRow;

    /**
     * @var array
     */
    protected $fieldMap;

    /**
     * @var array
     */
    protected $entityAsArray;

    /**
     * This property is marked TRUE by the constructor and may be updated by unserializeField()
     * to allow serialization logic to avoid unintentional nullification of existing field values.
     * @var boolean
     */
    protected $isSerializable;

    /**
     * @param array $simpleFMAdapterRow
     */
    public function __construct($fieldMap, $simpleFMAdapterRow = array())
    {
        $this->simpleFMAdapterRow = $simpleFMAdapterRow;

        if (empty($fieldMap)){
            throw new InvalidArgumentException(get_class($this) . ' is empty or missing.');
        }

        if (!array_key_exists(get_class($this), $fieldMap)){
            throw new InvalidArgumentException(get_class($this) . ' is missing from $fieldMap.');
        }

        $this->fieldMap = $fieldMap;
        if (!array_key_exists('writeable', $this->fieldMap[get_class($this)])){
            throw new InvalidArgumentException(get_class($this) . ' fieldMap must contain a "writeable" array.');
        }

        if (!array_key_exists('readonly', $this->fieldMap[get_class($this)])){
            throw new InvalidArgumentException(get_class($this) . ' fieldMap must contain a "readonly" array.');
        }

        $this->isSerializable = TRUE;
        if (!empty($this->simpleFMAdapterRow)) $this->unserialize();
    }

    /**
     * @return the name value for the object
     */

    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * @note FileMaker internal recid
     * @return the $recid
     */
    public function getRecid()
    {
        return (string) $this->recid;
    }

    /**
     * @param number $recid
     */
    public function setRecid ($recid)
    {
        $this->recid = $recid;
        return $this;
    }

    /**
     * @note FileMaker internal modid
     * @return the $modid
     */
    public function getModid()
    {
        return (string) $this->modid;
    }

    /**
     * @param number $modid
     */
    public function setModid ($modid)
    {
        $this->modid = $modid;
        return $this;
    }

    /**
     * @note Can be a concrete field e.g. $this->name,
     * or return derived value based on business logic
     */
    abstract public function getName();

    /**
     * @return the $isSerializable
     */
    public function getIsSerializable ()
    {
        return $this->isSerializable;
    }

    /**
     * @param boolean $isSerializable
     */
    public function setIsSerializable ($isSerializable)
    {
        $this->isSerializable = $isSerializable;
        return $this;
    }

    /**
     * Default FileMaker layout for the Entity which should include all the writable fields
     */
    abstract public function getDefaultWriteLayoutName();

    /**
     * The route segment for the entity controller.
     * Example: MyEntity route segment is normally my-entity
     */
    abstract public function getDefaultControllerRouteSegment();

    /**
     * Maps a SimpleFM\Adapter row onto the Entity.
     * @see $this->unserializeField()
     */
    public function unserialize()
    {
        $this->unserializeField('recid', 'recid');
        $this->unserializeField('modid', 'modid');

        foreach ($this->fieldMap[get_class($this)]['writeable'] as $property=>$field) {
            $this->unserializeField($property, $field, true);
        }

        foreach ($this->fieldMap[get_class($this)]['readonly'] as $property=>$field) {
            $this->unserializeField($property, $field, false);
        }
    }

    /**
     * Maps the Entity onto a SimpleFM\Adapter row. The array association should be a
     * fully qualified field name, with the exception of pseudo-fields recid and modid.
     * @see $this->serializeField()
     */
    public function serialize()
    {
        $this->simpleFMAdapterRow = array();

        $this->serializeField('-recid', 'getRecid');
        $this->serializeField('-modid', 'getModid');

        foreach ($this->fieldMap[get_class($this)]['writeable'] as $property=>$field) {
            $this->serializeField($field, $property);
        }

        foreach ($this->fieldMap[get_class($this)]['readonly'] as $property=>$field) {
            $this->serializeField($field, $property);
        }

    }

    /**
     * @return the $entityAsArray
     */
    public function toArray() {

        $this->addPropertyToEntityAsArray('recid');
        $this->addPropertyToEntityAsArray('modid');

        foreach ($this->fieldMap[get_class($this)]['writeable'] as $property=>$field) {
            $this->addPropertyToEntityAsArray($property);
        }

        foreach ($this->fieldMap[get_class($this)]['readonly'] as $property=>$field) {
            $this->addPropertyToEntityAsArray($property);
        }

        return $this->entityAsArray;
    }

    /**
     * For unserialize, optimized layouts are permitted to omit fields defined by the entity.
     * If a required field is omitted, $this->isSerializable is marked false
     * @param string $propertyName
     * @param string $fileMakerFieldName
     * @throws InvalidArgumentException
     */
    protected function unserializeField($propertyName, $fileMakerFieldName, $isWritable = false)
    {
        if (!property_exists($this, $propertyName)){
            throw new InvalidArgumentException($propertyName . ' is not a valid property.');
        }
        if (array_key_exists($fileMakerFieldName, $this->simpleFMAdapterRow)){
            $this->$propertyName = $this->simpleFMAdapterRow[$fileMakerFieldName];
        } elseif ($isWritable) {
            $this->isSerializable = false;
        }
    }

    /**
     * For serialize, all isRequired fields are required except the pseudo-fields recid and modid
     * which are always optional to handle force edit (blank modid) and new (blank recid).
     * @param string $fileMakerFieldName
     * @param string $propertyName
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function serializeField($fileMakerFieldName, $propertyName)
    {
        $getterName = 'get' . ucfirst($propertyName);

        if ($getterName == 'getRecid'){
            $value = $this->getRecid();
            if (!empty($value)){
                $this->simpleFMAdapterRow['-recid'] = $value;
            }
        } elseif ($getterName == 'getModid'){
            $recid = $this->getRecid();
            $modid = $this->getModid();
            if (!empty($modid) && !empty($recid)){
                $this->simpleFMAdapterRow['-modid'] = $modid;
            }
        } else {
            try {
                $this->simpleFMAdapterRow[$fileMakerFieldName] = $this->$getterName();
            } catch (\Exception $e) {
                if (!is_callable($this, $getterName)){
                    throw new InvalidArgumentException($getterName . ' is not a valid getter.', '', $e);
                } else {
                    throw $e;
                }
            }
        }
    }

    /**
     * For toArray, all fields should be mapped
     * @param string $propertyName
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function addPropertyToEntityAsArray($propertyName)
    {
        $getterName = 'get' . ucfirst($propertyName);

        try {
            $this->entityAsArray[$propertyName] = $this->$getterName();
        } catch (\Exception $e) {
            if (!is_callable($this, $getterName)){
                throw new InvalidArgumentException($getterName . ' is not a valid getter.', '', $e);
            } else {
                throw $e;
            }
        }

    }

}
