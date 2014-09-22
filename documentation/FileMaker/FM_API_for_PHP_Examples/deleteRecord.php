<html>
<head>
<title>Delete Record</title>
</head>
<?php
/**
 * deleteRecord.php
 * 
 * Copyright © 2005-2006, FileMaker, Inc. All rights reserved.
 * NOTE: Use of this source code is subject to the terms of the FileMaker
 * Software License which accompanies the code. Your use of this source code
 * signifies your agreement to such license terms and conditions. Except as
 * expressly granted in the Software License, no other copyright, patent, or
 * other intellectual property license or right is granted, either expressly or
 * by implication, by FileMaker.
 *
 * This is a script to delete a record given recid via GET parameter.
 */

// Turn on output buffering so that we can set Location: HTTP Header later on
ob_start();

require_once("FileMaker.php");

// Create FileMaker object
// Location of FileMaker Server is assumed to be on the same machine,
//  thus we assume hostspec is api default of 'http://localhost' as specified
//  in filemaker-api.php.
// If FMSA web server is on another machine, specify 'hostspec' as follows:
//   $fm = new FileMaker('FMPHP_Sample', 'http://10.0.0.1');
$fm = new FileMaker('FMPHP_Sample');

// declare $rec
$rec = null;

// check to see that 'recid' got passed in
if (array_key_exists('recid', $_GET)) {
	// get record object
	$rec = $fm->getRecordById('Form View', $_GET['recid']);
	// delete record	
	if (!$rec->delete()) {
	    echo 'Record deletion failed';
	    exit;
	}
}

// set Location: HTTP header to force redirect
header("Location: displayRecords.php");

// End output buffering and flush output
ob_end_flush();
?>