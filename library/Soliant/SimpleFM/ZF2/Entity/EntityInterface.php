<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Entity;

interface EntityInterface
{
    /**
     * @param array $simpleFMAdapterRow
     */
    public function __construct($simpleFMAdapterRow = array());
    
    /**
     * @note FileMaker internal recid
     * @return the $recid
     */
    public function getRecid();

    /**
     * @note FileMaker internal modid
     * @return the $modid
     */
    public function getModid();
    
    /**
     * @note Can be a concrete field e.g. $this->name, 
     * or return derived value based on business logic
     */
    public function getName(); 
    
    /**
     * @note Maps a SimpleFM\Adapter row onto the Entity
     */
    public function unserialize($simpleFMAdapterRow = array());
    
    /**
     * @note Return the alias defined for the entity's controller class in the
     * module.config.php to be used as Uri route segment.
     * Example return: work-request
     */
    public function getControllerAlias(); 

    /**
     * Example return: Application\Entity\WorkRequestPointer
     */
    public function getEntityPointerName();

    /**
     * Example return: Application\Entity\WorkRequest
     */
    public function getEntityName();
    
}