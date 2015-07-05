<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2015 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */
namespace Soliant\SimpleFM\Loader;

use Soliant\SimpleFM\Adapter;
use SimpleXMLElement;

class FileGetContents extends AbstractLoader
{

    /**
     * @param Adapter $adapter
     * @return SimpleXMLElement
     */
    public function load()
    {
        $this->prepare();
        libxml_use_internal_errors(true);
        
        $opts = array(
            'ssl'=> array(
                'verify_peer' => $this->adapter->getHostConnection()->getSslVerifyPeer(),
            ),
        );

        /**
         * Temporarily turn off error_reporting and capture any errors for handling later
         */
        $context  = stream_context_create($opts);
        $errorLevel = error_reporting();
        error_reporting(0);
        $data = file_get_contents($this->commandUrl, false, $context);
        error_reporting($errorLevel);
        return $this->handleReturn($data);
    }
}
