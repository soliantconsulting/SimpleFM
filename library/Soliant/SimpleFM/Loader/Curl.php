<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2015 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */
namespace Soliant\SimpleFM\Loader;

use SimpleXMLElement;
use Soliant\SimpleFM\Adapter;
use Soliant\SimpleFM\Exception\LoaderException;

class Curl extends AbstractLoader
{
    /**
     * @param Adapter $adapter
     * @return SimpleXMLElement
     * @throws LoaderException
     */
    public function load()
    {
        $this->prepare();
        $url = $this->postUrl;
        $curlHandle = curl_init($url);
        $curlError = [];

        curl_setopt($curlHandle, CURLOPT_USERPWD, $this->credentials);
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $this->args);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, $this->adapter->getHostConnection()->getSslVerifyPeer());

        ob_start();
        $success = curl_exec($curlHandle);
        if (!$success) {
            $curlError['type'] = 'CURL';
            $curlError['code'] = curl_errno($curlHandle);
            $curlError['message'] = 'Curl error: ' . curl_strerror($curlError['code']);
        }
        curl_close($curlHandle);
        $data = trim(ob_get_contents());
        ob_end_clean();


        return $this->handleReturn($data, $curlError);
    }
}
