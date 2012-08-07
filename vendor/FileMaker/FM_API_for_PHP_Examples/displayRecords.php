<html>
<head>
<title>Display All Records</title>
<!-- declare charset as UTF-8 -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="style.css">
</head>
<body>
<table id="main">
<tr><th id="table-title" colspan="3">America 24/7 Collection</th></tr>
<tr><th>Title</th><th>Publisher</th><th>Qty Available</th></tr>
<?php
/**
 * displayRecords.php
 * 
 * Copyright © 2005-2006, FileMaker, Inc. All rights reserved.
 * NOTE: Use of this source code is subject to the terms of the FileMaker
 * Software License which accompanies the code. Your use of this source code
 * signifies your agreement to such license terms and conditions. Except as
 * expressly granted in the Software License, no other copyright, patent, or
 * other intellectual property license or right is granted, either expressly or
 * by implication, by FileMaker.
 * 
 * Example PHP script to illustrate how to display records in a database using PHP API.
 * 
 * Requirements:
 *   1. Working FileMaker Server installation
 *   2. 'FMPHP_Sample' database hosted in FileMaker Server
 *
 */
 
// Include FileMaker API
require_once ('FileMaker.php');

// Create a new connection to FMPHP_Sample database.
// Location of FileMaker Server is assumed to be on the same machine,
//  thus we assume hostspec is api default of 'http://localhost' as specified
//  in filemaker-api.php.
// If FMSA web server is on another machine, specify 'hostspec' as follows:
//   $fm = new FileMaker('FMPHP_Sample', 'http://10.0.0.1');
$fm = new FileMaker('FMPHP_Sample');

// Create FileMaker_Command_Find on layout to search
$findCommand =& $fm->newFindAllCommand('Form View');

// Sort records in descending 'Title' order
$findCommand->addSortRule('Title', 1, FILEMAKER_SORT_ASCEND);

// Execute find command
$result = $findCommand->execute();

if (FileMaker::isError($result)) {
    echo "Error: " . $result->getMessage() . "\n";
    exit;
}

// Get array of found records
$records = $result->getRecords();

// Print out found records

// Setup row count variable to alternate row background color
$row = 0;

foreach ($records as $record) {
	// if $row is odd, set class of <tr> to alt-row-color
	if ($row % 2 == 0) {
		echo "<tr class=\"alt-row-color\">";
	} else {
		echo "<tr>";
	}
    	echo "<td class=\"align-left\"><a href=\"viewRecord.php?recid=" . $record->getRecordId() . "\">" . $record->getField('Title') . "</td>";
    	echo "<td>" . $record->getField('Publisher') . "</td>";
    	echo "<td>" . $record->getField('Quantity in Stock') . "</td>";
    	echo "</tr>";
    	$row++;
}
?>
<tr><td colspan="3"><a href="editRecord.php">Create New Record</a></tr>
</table>
</body>
</html>
