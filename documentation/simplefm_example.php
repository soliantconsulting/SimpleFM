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

foreach (require(__DIR__ . '/../library/autoload_classmap.php') as $classPath) {
    require_once($classPath);
}

use Soliant\SimpleFM\Adapter;
use Soliant\SimpleFM\HostConnection;
use Soliant\SimpleFM\Result\FmResultSet;

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
 * By default SimpleFM will use \Soliant\SimpleFM\Loader\FilePostContents to communicate with FileMaker Server.
 * In most cases the default is the best option, and there is no need to specify a loader explicitly.
 * If you need to use one of the alternative loaders, you instantiate is with the Adapter, and then add it with
 * the Adapter::setLoader method.
 */
// $loader = new \Soliant\SimpleFM\Loader\Curl($adapter)
// $loader = new \Soliant\SimpleFM\Loader\FileGetContents($adapter)
// $adapter->setLoader($loader);

/**
 * At runtime, you can update HostConnection on an adapter that has already been instantiated via the HostConnection's
 * fluent interface
 */
$adapter->getHostConnection()
    ->setHostName('localhost')
    ->setDbName('FMServer_Sample')
    ->setUserName('someUsername')
    ->setPassword('somePassword');

/**
 * After you have initialized a SimpleFMAdapter with valid credentials, there are a number of ways to make calls with
 * it. The simplest is to setCallParams with a layoutname and a commandstring. The commandstring follows the XML RPC
 * syntax for FileMaker Server. See /documentation/fms12_cwp_xml_en.pdf, Appendix A on page 43 for details.
 */
$adapter->setLayoutName('Tasks')
    ->setCommandString('-max=2&-skip=1&-findall');

/**
 * You may also update just the credentials at runtime via the HostConnection's fluent interface
 */
$adapter->getHostConnection()
    ->setUserName('Admin')
    ->setPassword('');

/**
 * Experiment with the getters and setters to modify the Adapter and set new commands for execution
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
    $commandArray['-max']  = 1 ;             // change -max value
    $commandArray['-skip'] = 3 ;             // add a -skip command
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
 * SimpleFMAdapter also provides a Boolean rowsByRecid option which makes the returned rows of data associative
 * by FileMaker recid instead of the default behavior which is rows as an arbitrarily indexed array.
 */
// $adapter->setRowsByRecId(true);

/**
 * Once your adapter is ready, use execute to make the host request.
 * @var FmResultSet $result
 */
$result = $adapter->execute();

/**
 * These are the elements simpleFM returns in the result array.
 */
$url          = $result->getDebugUrl();
$errorCode    = $result->getErrorCode();
$errorMessage = $result->getErrorMessage();
$errorType    = $result->getErrorType();
$count        = $result->getCount();
$fetchSize    = $result->getFetchSize();
$rows         = $result->getRows();

/**
 * Handle the result:
 *
 * IMPORTANT NOTE: The formatting code below is designed to demonstrate output from the above examples.
 *
 * While there is nothing wrong with is per se, the result formatting examples below are tailored for
 * this formatting use case, and is more complicated than will be required in many normal use cases.
 *
 * See the Best Practices section of the included README.md for more information.
 */

/**
 * Output some basic meta info about the request
 */
echo <<<HEADER
<div style='background-color:EEF;padding:1em;margin:1em;border-style:dotted;border-width:thin;'>
    Command URL: $url<br/>
    Error: $errorCode <br/>
    Error Text: $errorMessage<br/>
    Error Type: $errorType <br/>
    Found Count: $count<br/>
    Fetch Size: $fetchSize<br/>
</div>
HEADER;

if ($errorCode === 0) {
    /**
     * Format the result rows like a FileMaker Table View
     */
    echo "<h2>Table View</h2><table border=1><tr><th>array key</th>";
        $indexed = array_values($rows);
    foreach ($indexed[0] as $key => $value) {
        echo "<th>$key</th>";
    }
        echo "</tr>";
    foreach ($rows as $key => $data) {
        echo "<tr><td>$key</td>";
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
        echo "<tr><th>array key</th><td>$i</td></tr>";
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
