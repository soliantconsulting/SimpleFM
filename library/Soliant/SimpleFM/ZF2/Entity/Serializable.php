<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2012 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Entity;

interface Serializable
{

    /**
     * @note Maps a SimpleFM\Adapter row onto the Entity.
     */
    public function unserialize($array = array());

    /**
     * @note Maps the Entity onto a SimpleFM\Adapter row.
     */
    public function serialize();
    
}

