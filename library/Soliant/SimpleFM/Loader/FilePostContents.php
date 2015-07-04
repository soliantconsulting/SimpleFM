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

class FilePostContents extends AbstractLoader
{

    /**
     * @return string
     */
    protected function createPostURL()
    {
        $protocol = $this->adapter->getHostConnection()->getProtocol();
        $hostname = $this->adapter->getHostConnection()->getHostname();
        $port = $this->adapter->getHostConnection()->getPort();
        $uri = $this->adapter->getUri();

        return "$protocol://$hostname:$port$uri";
    }

    /**
     * @param Adapter $adapter
     * @return SimpleXMLElement
     */
    public function load(Adapter $adapter)
    {
        $this->adapter = $adapter;

        self::prepare();

        libxml_use_internal_errors(true);
        $authheader = empty($this->credentials) ? '' : 'Authorization: Basic ' . base64_encode($this->credentials) . PHP_EOL;

        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'User-Agent: SimpleFM' . PHP_EOL .
                    $authheader .
                    'Accept: text/xml,text/html,text/plain' . PHP_EOL .
                    'Content-type: application/x-www-form-urlencoded' . PHP_EOL .
                    'Content-length: ' . strlen($this->args) . PHP_EOL .
                    PHP_EOL,
                'content' => $this->args,
            ),
//             'ssl'=> array(
//                 'verify_peer' => $this->adapter->getSslverifypeer(),
//             ),
        );

        $context = stream_context_create($opts);
        $errorLevel = error_reporting();
        error_reporting(0);
        $errorMessage = null;
        if (!$data = file_get_contents(self::createPostURL(), false, $context)) {
            $errorArray = error_get_last();
            $errorMessage = $errorArray['message'];
        }
        error_reporting($errorLevel);
        return $this->handleReturn($data, $errorMessage);
    }
}
