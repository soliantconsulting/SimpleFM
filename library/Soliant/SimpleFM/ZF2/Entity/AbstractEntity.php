<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2015 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Entity;

use Exception;
use Soliant\SimpleFM\Exception\InvalidArgumentException;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Form\Annotation;

abstract class AbstractEntity implements ArraySerializableInterface
{
    /**
     * @Annotation\Exclude
     * @var int
     */
    protected $recid;

    /**
     * @Annotation\Exclude
     * @var int
     */
    protected $modid;

    /**
     * @Annotation\Exclude
     * @var array
     */
    protected $simpleFMAdapterRow;

    /**
     * @Annotation\Exclude
     * @var array
     */
    protected $entityAsArray;

    /**
     * This property is marked true by the constructor and may be updated by unserializeField()
     * to allow serialization logic to avoid unintentional nullification of existing field values.
     * @Annotation\Exclude
     * @var boolean
     */
    protected $isSerializable;

    /**
     * @param array $simpleFMAdapterRow
     */
    public function __construct($simpleFMAdapterRow = array())
    {
        $this->simpleFMAdapterRow = $simpleFMAdapterRow;
        $this->isSerializable = true;
        if (!empty($this->simpleFMAdapterRow)) {
            $this->unserialize();
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (method_exists($this, 'getName')) {
            return (string)$this->getName();
        } else {
            return '<toString is unconfigured>';
        }
    }

    /**
     * FileMaker internal recid
     * @return string
     */
    public function getRecid()
    {
        return (string)$this->recid;
    }

    /**
     * @param int $recid
     * @return $this
     */
    public function setRecid($recid)
    {
        $this->recid = (int)$recid;
        return $this;
    }

    /**
     * FileMaker internal modid
     * @return string
     */
    public function getModid()
    {
        return (string)$this->modid;
    }

    /**
     * @param int $modid
     */
    public function setModid($modid)
    {
        $this->modid = (int)$modid;
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
     * @return string|null
     */
    abstract public function getDefaultWriteLayoutName();

    /**
     * The route segment for the entity controller.
     * Example: MyEntity route segment is normally my-entity
     * @return string|null
     */
    abstract public function getDefaultControllerRouteSegment();

    /**
     * Maps a SimpleFM\Adapter row onto the Entity.
     * @see $this->unserializeField()
     * @return void
     */
    public function unserialize()
    {
        $this->unserializeField('recid', 'recid');
        $this->unserializeField('modid', 'modid');

        foreach ($this->getFieldMapWriteable() as $property => $field) {
            $this->unserializeField($property, $field, true);
        }

        foreach ($this->getFieldMapReadOnly() as $property => $field) {
            $this->unserializeField($property, $field, false);
        }
    }

    /**
     * Maps the Entity onto a SimpleFM\Adapter row. The array association should be a
     * fully qualified field name, with the exception of pseudo-fields recid and modid.
     * @see $this->serializeField()
     * @return array
     * @throws Exception
     */
    public function serialize()
    {
        $this->simpleFMAdapterRow = array();

        $this->serializeField('-recid', 'recid');
        $this->serializeField('-modid', 'modid');

        foreach ($this->getFieldMapWriteable() as $property => $field) {
            $this->serializeField($field, $property);
        }

        return $this->simpleFMAdapterRow;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getArrayCopy()
    {
        $this->addPropertyToEntityAsArray('recid');
        $this->addPropertyToEntityAsArray('modid');

        foreach ($this->getFieldMapMerged() as $property => $field) {
            $this->addPropertyToEntityAsArray($property);
        }

        return $this->entityAsArray;
    }

    /**
     * @deprecated
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function exchangeArray(array $data)
    {
        $this->recid = empty($data['recid']) ? '' : $data['recid'];
        $this->modid = empty($data['modid']) ? '' : $data['modid'];
        foreach ($this->getFieldMapWriteable() as $field => $column) {
            $this->$field = empty($data[$field]) ? '' : $data[$field];
        }
        $this->serialize();
        return $this;
    }

    /**
     * For unserialize, optimized layouts are permitted to omit fields defined by the entity.
     * If a required field is omitted, $this->isSerializable is marked false
     * @param string $propertyName
     * @param string $fileMakerFieldName
     * @param bool $isWritable
     * @throws InvalidArgumentException
     */
    protected function unserializeField($propertyName, $fileMakerFieldName, $isWritable = false)
    {
        if (!property_exists($this, $propertyName)) {
            throw new InvalidArgumentException($propertyName . ' is not a valid property.');
        }
        if (array_key_exists($fileMakerFieldName, $this->simpleFMAdapterRow)) {
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

        if ($getterName == 'getRecid') {
            $value = $this->getRecid();
            if (!empty($value)) {
                $this->simpleFMAdapterRow['-recid'] = $value;
            }
        } elseif ($getterName == 'getModid') {
            $recid = $this->getRecid();
            $modid = $this->getModid();
            if (!empty($modid) && !empty($recid)) {
                $this->simpleFMAdapterRow['-modid'] = $modid;
            }
        } else {
            try {
                $this->simpleFMAdapterRow[$fileMakerFieldName] = $this->$getterName();
            } catch (\Exception $e) {
                if (!is_callable($this, $getterName)) {
                    throw new InvalidArgumentException($getterName . ' is not a valid getter.', '', $e);
                } else {
                    throw $e;
                }
            }
        }
    }

    /**
     * For getArrayCopy, all fields should be mapped
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
            if (!is_callable($this, $getterName)) {
                throw new InvalidArgumentException($getterName . ' is not a valid getter.', '', $e);
            } else {
                throw $e;
            }
        }

    }
}
