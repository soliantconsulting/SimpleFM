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
    public function __construct($simpleFMAdapterRow = array())
    {
        $this->simpleFMAdapterRow = $simpleFMAdapterRow;
        $this->isSerializable = TRUE;
        if (!empty($this->simpleFMAdapterRow)) $this->unserialize();
    }

    /**
     * @return the name value for the object
     */

    public function __toString()
    {
        if (method_exists($this, 'getName')) {
            return (string) $this->getName();
        } else {
            return '<toString is unconfigured>';
        }
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
     * An array of properties and FileMaker field names each maps to which are writeable/serializable .
     * List fields in this map which the web app can modify, such as text and number fields. Normally
     * it will be most convenient to define the array directly in the method implementation.
     * Example: return array ('myEntityName' => 'My Entity Name', 'status' => 'SomeTableOccurance::Status');
     * @return array
     */
    abstract public function getFieldMapWriteable();

    /**
     * An array of properties and FileMaker field names each maps to which are readonly. List fields
     * in this map which cannot be updated by the web app, such a s primary keys and calc fields.
     * Normally it will be most convenient to define the array directly in the method implementation.
     * Example: return array ('id' => 'PrimaryKey', 'total' => 'Total');
     * @return array
     */
    abstract public function getFieldMapReadonly();

    /**
     * Utility function combines both field maps into a single map.
     * @return array
     */
    public function getFieldMapMerged()
    {
        return array_merge($this->getFieldMapWriteable(), $this->getFieldMapReadOnly());
    }

    /**
     * Default FileMaker layout for the Entity. This layout should usually at least include all the
     * writable fields, but it may also include readonly fields and portals/associations.
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

        foreach ($this->getFieldMapWriteable() as $property=>$field) {
            $this->unserializeField($property, $field, true);
        }

        foreach ($this->getFieldMapReadOnly() as $property=>$field) {
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

        $this->serializeField('-recid', 'recid');
        $this->serializeField('-modid', 'modid');

        foreach ($this->getFieldMapMerged() as $property=>$field) {
            $this->serializeField($field, $property);
        }

        return $this->simpleFMAdapterRow;
    }

    /**
     * @return the $entityAsArray
     */
    public function toArray() {

        $this->addPropertyToEntityAsArray('recid');
        $this->addPropertyToEntityAsArray('modid');

        foreach ($this->getFieldMapMerged() as $property=>$field) {
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
