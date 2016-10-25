<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   SimpleFM
 * @copyright Copyright (c) 2007-2016 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

require_once(__DIR__ . '/../../vendor/autoload.php');

use Http\Client\Curl\Client;
use Soliant\SimpleFM\Client\ResultSet\ResultSetClient;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Connection\Connection;
use Zend\Diactoros\Uri;

/**
 * The hostName can either be an IP address or any valid network name you have configured and hosting the FileMaker XML
 * API. FMServer_Sample.fmp12 is included with FileMaker Server 15 with guest access enabled by default. You should
 * always leave off the file extension when configuring dbName.
 */
$connection = new Connection(
    new Client(),
    new Uri('http://localhost'),
    'FMServer_Sample'
);

$resultSetClient = new ResultSetClient($connection, new DateTimeZone('UTC'));

/**
 * After you have initialized a SimpleFM Connection with valid credentials, you can execute it with a Command. The
 * Command expects a layout name and a command array. The command array follows the name/values defined by the XML API
 * syntax for FileMaker Server. See doc/filemaker/fms15_cwp_guide.pdf, Chapter 5 on page 40 for details.
 */

$records = $resultSetClient->execute(new Command('Task Details', ['-max' => 2, '-skip' => 1, '-findall' => null]));

/**
 * These are the elements simpleFM returns in the result array.
 */
$url = 'http://localhost/fmi/xml/yadda'; //$records->getDebugUrl();
$errorCode = 0; //$records->getErrorCode();
$errorMessage = 'No Error'; //$records->getErrorMessage();
$errorType = 'FileMaker'; //$records->getErrorType();
$count = 12;
$fetchSize = 2;

/**
 * Handle the result:
 *
 * While there is nothing wrong with is per se, the result formatting examples below are not intended to be followed as
 * examples. In particular, the stringifyValues function is not normally a technique you should use.
 *
 * See the Best Practices section of the included README.md for more information.
 */

/**
 * Output some basic meta info about the request
 */
echo sprintf("<div style='background-color:EEF;padding:1em;margin:1em;border-style:dotted;border-width:thin;'>
    Command URL: %s<br/>
    Error:  %s<br/>
    Error Text: %s<br/>
    Error Type:  %s<br/>
    Found Count: %s<br/>
    Fetch Size: %s<br/>
</div>",
$url,
$errorCode,
$errorMessage,
$errorType,
$count,
$fetchSize
);

if ($errorCode === 0) {
    /**
     * Format the result rows like a FileMaker Table View
     */
    echo "<h2>Table View</h2><table border=1><tr><th>array key</th>";
    $indexed = array_values($records);
    foreach ($indexed[0] as $key => $value) {
        echo "<th>$key</th>";
    }
    echo "</tr>";
    foreach ($records as $key => $data) {
        echo "<tr><td>$key</td>";
        foreach ($data as $value) {
            echo sprintf("<td>%s</td>", stringifyValue($value));
        }
        echo "</tr>";
    }
    echo "</table>";

    /**
     * Format the result rows like a FileMaker Form in List View
     */
    echo "<h2>Form List View</h2>";
    foreach ($records as $i => $data) {
        echo "<table border=1>";
        echo "<tr><th>array key</th><td>$i</td></tr>";
        foreach ($data as $key => $value) {
            echo sprintf("<tr><th>%s</th><td>%s</td></tr>", $key, stringifyValue($value));
        }
        echo "</table><br/>";
    }
}

/**
 * Finally, a dump of the raw result
 */
echo "<hr><pre>";
var_dump($records);


/**
 * IMPORTANT NOTE: The formatting code below is only designed to format html output for the above examples. This is
 * not a suggestion for best practices.
 */
function stringifyValue($value) : string
{
    $value = $value === "" ? "&nbsp;" : $value;
    if (is_array($value)) {
        if (isset($value[0]) && isset($value[0]['record-id'])) {
            $tempValue = '';
            foreach ($value as $val) {
                // portal
                foreach ($val as $k => $v) {
                    if (is_array($v)) {
                        continue;
                    }
                    $tempValue .= $k . ':&nbsp;' . stringifyValue($v) . '<br>';
                }
                $tempValue .= '<hr>';
            }
            $value = $tempValue;
        } else {
            // repeating field
            $value = implode("<br>", $value);
        }
    } elseif ($value instanceof DateTimeImmutable) {
        $value = $value->format('m/d/Y');
    } else {
        $value = nl2br($value);
    }
    return $value;
}

