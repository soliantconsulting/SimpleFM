<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\Loader;

use Soliant\SimpleFM\Adapter;

interface LoaderInterface
{
    
    /**
     * @param array $simpleFMAdapterRow
     * @return SimpleXMLElement
     */
    public function load(Adapter $adapter);
    
}

