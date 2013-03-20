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

class FilePostContents extends AbstractLoader
{

    protected function createPostURL()
    {
        $protocol = $this->adapter->getProtocol();
        $hostname = $this->adapter->getHostname();
        $port = $this->adapter->getPort();
        $fmresultsetUri = $this->adapter->getFmresultsetUri();

        return "$protocol://$hostname:$port$fmresultsetUri";
    }

    /**
     * @return SimpleXMLElement
     */
    public function load(Adapter $adapter)
    {
        $this->adapter = $adapter;

        self::prepare();

        libxml_use_internal_errors(true);
        $authheader = empty($this->credentials) ? '' : 'Authorization: Basic '.base64_encode($this->credentials) . PHP_EOL;

        $opts = array('http' =>
                array(
                        'method'  => 'POST',
                        'header'  => 'User-Agent: SimpleFM' . PHP_EOL .
                        $authheader .
                        'Accept: text/xml,text/html,text/plain' . PHP_EOL .
                        'Content-type: application/x-www-form-urlencoded' . PHP_EOL .
                        'Content-length: ' . strlen($this->args) .  PHP_EOL .
                        PHP_EOL,
                        'content' => $this->args
                )
        );

        $context  = stream_context_create($opts);


        return simplexml_load_string(file_get_contents(self::createPostURL(), FALSE , $context));

    }

}
