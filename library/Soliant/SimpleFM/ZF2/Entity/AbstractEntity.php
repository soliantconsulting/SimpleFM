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
     * @param array $simpleFMAdapterRow
     */
    public function __construct($simpleFMAdapterRow = array())
    {
        $this->simpleFMAdapterRow = $simpleFMAdapterRow;
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
     * @note FileMaker internal modid
     * @return the $modid
     */
    public function getModid()
    {
        return (string) $this->modid;
    }
    
    /**
     * @note Can be a concrete field e.g. $this->name,
     * or return derived value based on business logic
     */
    abstract public function getName();
    
    /**
     * @note default FileMaker layout for the Entity
     * which should include all the writable fields
     */
    abstract public static function getDefaultWriteLayoutName();
    
    /**
     * @note Maps the Entity onto a SimpleFM\Adapter row.
     * The array association should be a fully qualified field name,
     * with the exception of recid and modid, which must have a leading
     * dash as shown here:
     * $simpleFMAdapterRow["-recid"] = $this->getRecid();
     * $simpleFMAdapterRow["-modid"] = $this->getModid();
     */
    abstract public function serialize();
    
    /**
     * @note Maps a SimpleFM\Adapter row onto the Entity
     */
    abstract public function unserialize();
    
    
    /**
     * @note for unserialize, optimized layouts are permitted to omit fields defined by the entity
     * @param string $propertyName
     * @param string $fileMakerFieldName
     * @throws InvalidArgumentException
     */
    protected function mapFmFieldOntoProperty($propertyName, $fileMakerFieldName)
    {
        if (!property_exists($this, $propertyName)){
            throw new InvalidArgumentException($propertyName . ' is not a valid property.');
        }
        if (array_key_exists($fileMakerFieldName, $this->simpleFMAdapterRow)){
            $this->$propertyName = $this->simpleFMAdapterRow[$fileMakerFieldName];
        }
    }
    
    /**
     * @note for serialize, all defined fields are required
     * @param string $fileMakerFieldName
     * @param string $getterName
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function mapPropertyOntoFmField($fileMakerFieldName, $getterName)
    {
        try {
            $simpleFMAdapterRow[$fileMakerFieldName] = $this->$getterName();
        } catch (\Exception $e) {
            if (!is_callable($this, $getterName)){
                throw new InvalidArgumentException($getterName . ' is not a valid getter.', '', $e);
            } else {
                throw $e;
            }
        }
    }
    
    
}