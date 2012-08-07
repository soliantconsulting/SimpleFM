<?php

/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 * 
 * @package SimpleFM
 * @copyright  Copyright (c) 2007-2012 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author jsmall@soliantconsulting.com
 */

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

require_once('SimpleFMAdapter.php');

// define a constant named DEBUG as true to instruct SimpleFMAdapter return a formated error message when errors occur.
define('DEBUG',TRUE);

// The FMServer_Sample is included on stock FileMaker Server installs; you just need to create some credentials

// initialize the adapter and set it with your host params array
$adapter = new SimpleFmAdapter();
$adapter->setHostParams(
    array(
        'host'=>'valhalla.soliantconsulting.com',
        'dbname'=>'FMServer_Sample',
        'username'=>'weby',
        'password'=>'weby' 
    )
);

// optionally set/reset your call params with an array
$adapter->setCallParams(
    array(
        'layoutname'=>'English_List_View',
        'commandstring'=>'-findany'
    )
);

// optionally set/reset your credentials as an array
$adapter->setCredentials(
    array(
        'username'=>'webx',
        'password'=>'webx'
    )
);

// or update the credentials with the individual setters
$adapter->setUsername('web');
$adapter->setPassword('web');

// there are individual getters and settes for every property except there is no getter for password
// experiment with the getters and setters to modify the adapter and set new queries for execution
$adapter->setLayoutname('English_Portal_View');

// you can define commands using the standard url command syntax
$adapter->setCommandstring('-max=3&-skip=21&-findall');

// or you can get and set the adapter command array
// this is useful because it lets you modify existing and add new commands without disrupting the rest of the command properties
//$commandarray = $adapter->getCommandarray();
//$commandarray['-max'] = 40 ; // change the -max value
//$commandarray['-skip'] = 10 ; // add a -skip command
//$adapter->setCommandarray($commandarray); // set it back on the adapter

/*
// experiment with dumping out the command string and command array
//note how the setters keep them in sync

$commandarray = $adapter->getCommandarray();
$commandstring = $adapter->getCommandstring();
echo '<pre>';
var_dump($commandarray);
echo($commandstring);
die();

*/

// try setting the Boolean rowsbyrecid property
// this makes the rows associative by the FileMaker recid instead of a default indexed array
$adapter->setRowsbyrecid(FALSE);


// once your adapter is all ready to fire, use execute
$result = $adapter->execute();

/*
echo '<pre>';
var_dump($result);
die();
*/

// here are all the elements simpleFM returns.
$url = $result['url'];                 //string
$error = $result['error'];             //int
$errortext = $result['errortext'];     //string
$count = $result['count'];             //int
$fetchsize = $result['fetchsize'];     //int
$rows = $result['rows'];            //array


// here are some examples of what you can do with the query results.
if ($error===0){

    if (DEBUG===true) {
        echo "<div style='background-color:EEF;padding:1em;margin:1em;border-style:dotted;border-width:thin;'>";
        echo "Command&nbsp;URL: $url<br/>";
        echo "Error: $error <br/>";
        echo "Error Text: $errortext<br/>";
        echo "Found Count: $count<br/>";
        echo "Fetch Size: $fetchsize<br/>";
        echo "</div>";
    }
    
    // Here we introspect the fetched data and display it like a FileMaker Table View
    echo "<h2>Table View</h2><table border=1><tr>";
        $indexed = array_values($rows);
        foreach ($indexed[0] as $key => $value) { echo "<th>$key</th>"; }
        echo "</tr>";
        foreach ($rows as $data) { echo "<tr>";
            foreach ($data as $value) { 
                $value = $value===""?"&nbsp;":$value; 
                echo "<td>$value</td>"; }
        echo "</tr>";}    
    echo "</table>";
    
    // Here we introspect the fetched data and display it like a FileMaker Form in List View 
    echo "<h2>Form List View</h2>";
    foreach ($rows as $i => $data) {
        echo "<table border=1>";
        foreach ($data as $key => $value) {
            $value = $value===""?"&nbsp;":$value;
            echo "<tr><th>$key</th><td>$value</td></tr>";
        }
        echo "</table><br/>";
    }
    
    // Note: The FMServer_Sample file doesnt have portals in it, but SimpleFM supports portals.
    // portals are returned as named child arrays to every record in the fetched set.
    // simpleFM returns N portals for every record in the found set (there can be more than one portal on a layout)
    
    // Assuming you set the adapter to setRowsbyrecid(TRUE), here is syntax that would echo the data from a portal by parent recid, portal name, child recid, and portal fieldname
    // echo $rows[154]['Portal_TO_Name']['rows'][335932]['app_rnk_SCH__Schools::schoolName'];
    
    // Assuming you left the adapter rowsbyrecid FALSE (the default setting), here is syntax that would echo the data from a portal by parent index, portalname, child index and fieldname.
    // Note that index, recid and modid are available on every parent and child record, regardless of the setting on rowsbyrecid
    // echo $rows[0]['Portal_TO_Name']['rows'][0]['recid'].'<br/>';
    // echo $rows[0]['Portal_TO_Name']['rows'][0]['schoolName'];

    if (DEBUG===true) {
        echo "<hr><pre>";
        var_dump($result);
    }


}//end if
