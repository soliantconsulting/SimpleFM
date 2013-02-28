<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\Loader;

require_once('AbstractLoader.php');

use Soliant\SimpleFM\Loader\AbstractLoader;
use Soliant\SimpleFM\Adapter;

class FileGetContents extends AbstractLoader
{
    
    /**
     * @return SimpleXMLElement
     */
    public function load(Adapter $adapter)
    {
        $this->adapter = $adapter;
        
        self::prepare();
        
        libxml_use_internal_errors(true);
        
        return simplexml_load_string(file_get_contents($this->commandURL));
    
    }
    
}
