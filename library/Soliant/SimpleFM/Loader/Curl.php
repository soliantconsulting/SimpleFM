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
use Soliant\SimpleFM\Exception\LoaderException;
use SimpleXMLElement;

class Curl extends AbstractLoader
{

    /**
     * @return string
     */
    protected function createPostURL()
    {
        $protocol = $this->adapter->getProtocol();
        $hostname = $this->adapter->getHostname();
        $port = $this->adapter->getPort();
        $uri = $this->adapter->getUri();

        return "$protocol://$hostname:$port$uri";
    }

    /**
     * @param Adapter $adapter
     * @return SimpleXMLElement
     * @throws LoaderException
     */
    public function load(Adapter $adapter)
    {
        $this->adapter = $adapter;

        self::prepare();

        $url = self::createPostURL();

        $curlHandle = curl_init($url);

        curl_setopt($curlHandle, CURLOPT_USERPWD, $this->credentials);
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $this->args);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, $this->adapter->getSslverifypeer());

        ob_start();

        if (!curl_exec($curlHandle)) {
            ob_end_clean();
            throw new LoaderException('cURL was unable to connect to ' . $url . '.');
        }

        curl_close($curlHandle);

        $data = trim(ob_get_contents());

        ob_end_clean();

        libxml_use_internal_errors(true);

        return simplexml_load_string($data);

    }
}
