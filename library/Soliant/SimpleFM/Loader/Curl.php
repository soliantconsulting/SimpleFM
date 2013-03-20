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
use Soliant\SimpleFM\Exception\LoaderException;

class Curl extends AbstractLoader
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


        $curlHandle = curl_init(self::createPostURL());

        curl_setopt($curlHandle, CURLOPT_USERPWD, $this->credentials);
        curl_setopt($curlHandle, CURLOPT_POST, TRUE);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $this->args);

        ob_start();

        if (!curl_exec($curlHandle)) {
            ob_end_clean();
            throw new LoaderException('cURL was unable to connect.');
        }

        curl_close($curlHandle);

        $data = trim(ob_get_contents());

        ob_end_clean();

        libxml_use_internal_errors(true);

        return simplexml_load_string($data);

    }

}
