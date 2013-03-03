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
    abstract public static function getDefaultWriteLayoutName();
    
    /**
     * Maps a SimpleFM\Adapter row onto the Entity.
     * @see $this->unserializeField()
     */
    public function unserialize()
    {
    	$this->unserializeField('recid', 'recid');
    	$this->unserializeField('modid', 'modid');
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
    }
    
    
    /**
     * For unserialize, optimized layouts are permitted to omit fields defined by the entity.
     * If a required field is omitted, $this->isSerializable is marked FALSE
     * @param string $propertyName
     * @param string $fileMakerFieldName
     * @throws InvalidArgumentException
     */
    protected function unserializeField($propertyName, $fileMakerFieldName, $isWritable=FALSE)
    {
        if (!property_exists($this, $propertyName)){
            throw new InvalidArgumentException($propertyName . ' is not a valid property.');
        }
        if (array_key_exists($fileMakerFieldName, $this->simpleFMAdapterRow)){
            $this->$propertyName = $this->simpleFMAdapterRow[$fileMakerFieldName];
        } elseif ($isWritable) {
            $this->isSerializable = FALSE;
        }
    }
    
    /**
     * For serialize, all isRequired fields are required except the pseudo-fields recid and modid
     * which are always optional to handle force edit (blank modid) and new (blank recid).
     * @param string $fileMakerFieldName
     * @param string $getterName
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function serializeField($fileMakerFieldName, $getterName)
    {
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
    
    
}