<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2015 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\Loader;

require_once('AbstractLoader.php');

use Soliant\SimpleFM\Adapter;
use SimpleXMLElement;
use Soliant\SimpleFM\Exception\LoaderException;

class FileGetContents extends AbstractLoader
{

    /**
     * @param Adapter $adapter
     * @return SimpleXMLElement
     */
    public function load(Adapter $adapter)
    {
        $this->adapter = $adapter;

        $this->prepare();

        libxml_use_internal_errors(true);
        
        $opts = array(
            'ssl'=> array(
                'verify_peer' => $this->adapter->getHostConnection()->getSslVerifyPeer(),
            ),
        );
        
        $context  = stream_context_create($opts);
        $errorLevel = error_reporting();
        error_reporting(0);
        $errorMessage = null;
        if (!$data = file_get_contents($this->commandURL, false, $context)) {
            $errorArray = error_get_last();
            $errorMessage = $errorArray['message'];
        };
        error_reporting($errorLevel);
        return $this->handleReturn($data, $errorMessage);
    }
}
