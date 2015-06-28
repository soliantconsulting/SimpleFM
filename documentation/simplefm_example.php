<?php

/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   SimpleFM
 * @copyright Copyright (c) 2007-2015 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

require_once('../library/Soliant/SimpleFM/Adapter.php');
require_once('../library/Soliant/SimpleFM/HostConnection.php');
require_once('../library/Soliant/SimpleFM/Loader/FilePostContents.php');

use Soliant\SimpleFM\Adapter;
use Soliant\SimpleFM\HostConnection;

/**
 * The hostName can either be an IP address or any valid network name you have configured and hosting the
 * FileMaker XML API. FMServer_Sample.fmp12 is included with FileMaker Server 12; the default userName is
 * Admin with blank password. You should always leave off the file extension when configuring dbName.
 */
$hostConnection = new HostConnection(
    'localhost',
    'FMServer_Sample',
    'Admin',
    ''
);

/**
 * Initialize the adapter with the hostParams array for your environment.
 */
$adapter = new Adapter($hostConnection);

/**
 * At runtime, you can update HostConnection on an adapter that has already been instantiated via the HostConnection's
 * fluent interface
 */
$adapter->getHostConnection()
    ->setHostName('localhost')
    ->setDbName('FMServer_Sample')
    ->setPassword('someUsername')
    ->setUserName('somePassword');

/**
 * After you have initialized a SimpleFMAdapter with valid credentials, there are a number of ways to make calls with
 * it. The simplest is to setCallParams with a layoutname and a commandstring. The commandstring follows the XML RPC
 * syntax for FileMaker Server. See /documentation/fms12_cwp_xml_en.pdf, Appendix A on page 43 for details.
 */
$adapter->setCallParams(
    array(
        'layoutName'    => 'Tasks',
        'commandString' => '-max=10&-skip=5&-findall'
    )
);

/**
 * You may also update just the credentials at runtime via the HostConnection's fluent interface
 */
$adapter->getHostConnection()
    ->setPassword('Admin')
    ->setUserName('');

/**
 * There are individual getters and settes for every property (except there is no getter for the password property)
 * Experiment with the getters and setters to modify the adapter and set new queries for execution
 */
$adapter->setLayoutName('Projects');

/**
 * As already mentioned, for basic usage, you can define commands using the FileMaker XML url api syntax.
 * See /documentation/fms12_cwp_xml_en.pdf
 */
$adapter->setCommandString('-findall');

/**
 * For more fine-grained control, you can also interact with the adapter's commandarray.
 * This is useful because it lets you modify existing commands on the adpater, and add new commands
 * without blowing away existing command properties. For example:

    $commandArray = $adapter->getCommandArray();
    $commandArray['-max']  = 40 ;             // change -max value
    $commandArray['-skip'] = 10 ;             // add a -skip command
    $adapter->setCommandArray($commandArray); // set it back on the adapter

 *
 */

/**
 * Experiment with dumping out the command string and command array and notice that it doesn't matter
 * which method you use for setting commands. They both affect the same properties of the adapter. For example:

    $commandArray  = $adapter->getCommandArray();
    $commandString = $adapter->getCommandString();
    echo '<pre>';
    var_dump($commandArray);
    echo($commandString);
    die();

 *
 */

/**
 * SimpleFMAdapter also provides a Boolean rowsbyrecid property which makes the returned rows of data associative
 * by FileMaker recid instead of the default behavior which is rows as an arbitrarily indexed array.
 */
$adapter->setRowsByRecId(false);

/**
 * Once your adapter is ready, use execute to make the host request.
 */
$result = $adapter->execute();

/**
 * These are the elements simpleFM returns in the result array.
 */
$url       = $result['url'];           // string
$error     = $result['error'];         // int
$errorText = $result['errortext'];     // string
$errorType = $result['errortype'];     // string
$count     = $result['count'];         // int
$fetchSize = $result['fetchsize'];     // int
$rows      = $result['rows'];          // array


 /**
 * Handle the result:
 *
 * Below are some very basic examples of what you can do with the query results. These examples are designed to be
 * a flexible way to view raw results, and are probably not the way you would normally handle results in an OOP
 * solution (see Best practices in the included README.md).
 */

/**
 * Output some basic meta info about the request
 */
echo "<div style='background-color:EEF;padding:1em;margin:1em;border-style:dotted;border-width:thin;'>";
echo "Command URL: $url<br/>";
echo "Error: $error <br/>";
echo "Error Text: $errorText<br/>";
echo "Error Type: $errorType <br/>";
echo "Found Count: $count<br/>";
echo "Fetch Size: $fetchSize<br/>";
echo "</div>";

if ($error === 0) {
    /**
     * Format the result rows like a FileMaker Table View
     */
    echo "<h2>Table View</h2><table border=1><tr>";
        $indexed = array_values($rows);
    foreach ($indexed[0] as $key => $value) {
        echo "<th>$key</th>";
    }
        echo "</tr>";
    foreach ($rows as $data) {
        echo "<tr>";
        foreach ($data as $value) {
            $value = $value === "" ? "&nbsp;" : $value;
            if (is_array($value)) {
                if (isset($value['parentindex'])) {
                    // portal
                    $tempValue = '';
                    foreach ($value as $k => $v) {
                        if (is_array($v)) {
                            continue;
                        }
                        $tempValue .= $k . ':&nbsp;' . $v . '<br>';
                    }
                    $value = $tempValue;
                } else {
                    // repeating field
                    $value = implode("<br>", $value);
                }
            } else {
                $value = nl2br($value);
            }
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

    /**
     * Format the result rows like a FileMaker Form in List View
     */
    echo "<h2>Form List View</h2>";
    foreach ($rows as $i => $data) {
        echo "<table border=1>";
        foreach ($data as $key => $value) {
            $value = $value === "" ? "&nbsp;" : $value;
            if (is_array($value)) {
                if (isset($value['parentindex'])) {
                    // portal
                    $tempValue = '';
                    foreach ($value as $k => $v) {
                        if (is_array($v)) {
                            continue;
                        }
                        $tempValue .= $k . ':&nbsp;' . $v . '<br>';
                    }
                    $value = $tempValue;
                } else {
                    // repeating field
                    $value = implode("<br>", $value);
                }
            } else {
                $value = nl2br($value);
            }
            echo "<tr><th>$key</th><td>$value</td></tr>";
        }
        echo "</table><br/>";
    }
}

/**
 * Finally, a dump of the raw result
 */
echo "<hr><pre>";
var_dump($result);
