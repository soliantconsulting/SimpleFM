<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Entity;

interface SerializableEntityInterface extends EntityInterface
{
    /**
     * @note Maps the Entity onto a SimpleFM\Adapter row.
     * The array association should be a fully qualified field name,
     * with the exception of recid and modid, which must have a leading
     * dash as shown here:
     * $simpleFMAdapterRow["-recid"] = $this->getRecid();
     * $simpleFMAdapterRow["-modid"] = $this->getModid();
     */
    public function serialize();
    
}

