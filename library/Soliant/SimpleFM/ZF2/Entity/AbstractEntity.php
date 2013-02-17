<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Entity;

abstract class AbstractEntity implements EntityInterface
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
     * @param array $simpleFMAdapterRow
     */
    public function __construct($simpleFMAdapterRow = array())
    {
        if (!empty($simpleFMAdapterRow)) $this->unserialize($simpleFMAdapterRow);
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
     * @note Maps a SimpleFM\Adapter row onto the Entity
     */
    abstract public function unserialize($simpleFMAdapterRow = array());
    
    /**
     * @note Return the alias defined for the entity's controller class in the
     * module.config.php to be used as Uri route segment.
     * Example return: work-request
     */
    abstract public function getControllerAlias(); 

    /**
     * Example return: Application\Entity\WorkRequestPointer
     */
    abstract public function getEntityPointerName();

    /**
     * Example return: Application\Entity\WorkRequest
     */
    abstract public function getEntityName();
    
}