<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   SimpleFM
 * @copyright Copyright (c) 2007-2016 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 *
 * PREREQUISITES
 *  To run this example file, you will need an environment with PHP7+, Composer, and FileMaker Server 15 running on
 *  localhost with the default FMServer_Sample file hosted.
 *
 * INSTRUCTIONS
 *  In the terminal, run the following commands
 *      cd {project-root}/doc/filemaker
 *      composer install
 *      php -S 0.0.0.0:8080 -t ./ ./simplefm_example.php
 *
 *  The last command starts the internal PHP web server. Now when you visit the browser you should see the SimpleFM
 *  Example page displayed in your browser.
 */

ini_set('display_errors', 1);
ini_set('log_errors', 1);
$errorLogPath = dirname(__FILE__) . '/error.log';
ini_set('error_log', $errorLogPath);
error_reporting(E_ALL);

require_once(__DIR__ . '/vendor/autoload.php');

use Http\Client\Curl\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Soliant\SimpleFM\Client\ResultSet\ResultSetClient;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Connection\Connection;
use Zend\Diactoros\Uri;

/**
 * Configure any Http\Client\HttpClient implementation. In this case we'll use Http\Client\Curl\Client, which is a good
 * default choice for most cases.
 */
$httpClient = new Client();

/**
 * Configure any Psr\Http\Message\UriInterface implementation. In this case we'll use Zend\Diactoros\Uri, which is a
 * good default choice for most cases.
 */
$uri = new Uri('http://localhost');

/**
 * Configure the database name. Do not include the file extension. For example, do NOT write 'FMServer_Sample.fmp12'
 */
$database = 'FMServer_Sample';

/**
 * You can create an optional Soliant\SimpleFM\Authentication\IdentityHandlerInterface, but for this example we don't
 * need it, so we leave it null.
 */
$identityHandler = null;

/**
 * Configure an optional Psr\Log\LoggerInterface implementation to assist with debugging. In this case, we'll write a
 * connection.log to the same directory as this simplefm_example.php file using Monolog\Logger.
 */
$logger = new Logger('connection');
$connectionLogPath = dirname(__FILE__) . '/connection.log';
$logger->pushHandler(new StreamHandler($connectionLogPath));

/**
 * Create a Soliant\SimpleFM\Connection\Connection with all the required dependencies injected.
 */
$connection = new Connection(
    $httpClient,
    $uri,
    $database,
    $identityHandler,
    $logger
);

/**
 * Create a Soliant\SimpleFM\Client\ResultSet\ResultSetClient with the new Connection and a DateTimeZone;
 */
$resultSetClient = new ResultSetClient(
    $connection,
    new DateTimeZone('UTC')
);

/**
 * Now that you have configured a ResultSetClient, you can execute it with a Soliant\SimpleFM\Connection\Command. The
 * Command expects a layout name and a command array. The command array accepts the name/values defined by the XML API
 * syntax for FileMaker Server. Some of the FileMaker XML API commands are a key only (with no value). For example
 * `-find`. For a command with no explicit value, use a null value in the command array.
 * See doc/filemaker/fms15_cwp_guide.pdf, Chapter 5 on page 40 for XML API command documentation.
 */
$command = new Command('Task Details', ['-max' => 2, '-skip' => 1, '-findall' => null]);

/**
 * When the Command is ready, execute it on the Client
 */
$records = $resultSetClient->execute($command);

/**
 * Handle the result:
 *
 * The result formatting examples below are not intended to be followed as best practices. In particular, the
 * stringifyValues function is not a technique you should normally use.
 *
 * See the Best Practices section of the included README.md for more information.
 */
echo "<h1>SimpleFM Example</h1>";

/**
 * Basic info about the response
 */
echo sprintf("<div style='background-color:EEF;padding:1em;margin:1em;border-style:dotted;border-width:thin;'>
    <table>
        <tr><th align='right'>Host URI</th><td>%s</td></tr>
        <tr><th align='right'>Database</th><td>%s</td></tr>
        <tr><th align='right'>Command</th><td>%s</td></tr>
        <tr><th align='right'>Connection Log</th><td>%s</td></tr>
        <tr><th align='right'>Error Log</th><td>%s</td></tr>
        <tr><th align='right'>Total Found Count</th><td>%s</td></tr>
        <tr><th align='right'>Fetch Size</th><td>%s</td></tr>
    </table>
</div>",
    $uri,
    $database,
    (string) $command,
    $connectionLogPath,
    $errorLogPath,
    $records->getTotalCount(),
    count($records)
);

/**
 * Records formatted like a FileMaker Table View
 */
echo "<h2>Table View</h2><table border=1><tr><th>array key</th>";
foreach ($records->first() as $key => $value) {
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

/**
 * Finally, a dump of the raw result
 */
echo "<hr><pre>";
foreach ($records as $record) {
    var_dump($record);
}

/**
 * IMPORTANT NOTE: The formatting code below is only designed to format html output for the above examples. This is
 * not a suggestion for best practices.
 */
function stringifyValue($value) : string
{
    $value = $value === "" ? "&nbsp;" : $value;
    if (is_array($value)) {
        $value = arrayToParagraphs($value);
    } elseif ($value instanceof DateTimeInterface) {
        $value = $value->format('m/d/Y');
    } else {
        $value = nl2br($value);
    }
    return $value;
}

function arrayToParagraphs($value)
{
    $count = count($value);
    if ($count) {
        $collapsedValue = '';
        if (isset($value[0]) && isset($value[0]['record-id'])) {
            // portal
            $tempValue = '';
            foreach ($value as $val) {
                foreach ($val as $k => $v) {
                    if (is_array($v)) {
                        continue;
                    }
                    $tempValue .= $k . ':&nbsp;' . stringifyValue($v) . PHP_EOL;
                }
                $tempValue .= PHP_EOL;
            }
            $collapsedValue .= $tempValue;
        } else {
            // repeating field
            $collapsedValue .= implode(PHP_EOL, $value);
        }
    } else {
        return "&nbsp;";
    }
    return sprintf('<a href="#" title="%s">%d items</a>', $collapsedValue, $count);
}
