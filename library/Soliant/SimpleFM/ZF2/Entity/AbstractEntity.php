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
    
}