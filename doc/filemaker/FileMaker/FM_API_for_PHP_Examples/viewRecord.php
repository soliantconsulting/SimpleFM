<html>
<?php
/**
 * viewRecord.php
 * 
 * Copyright © 2005-2006, FileMaker, Inc. All rights reserved.
 * NOTE: Use of this source code is subject to the terms of the FileMaker
 * Software License which accompanies the code. Your use of this source code
 * signifies your agreement to such license terms and conditions. Except as
 * expressly granted in the Software License, no other copyright, patent, or
 * other intellectual property license or right is granted, either expressly or
 * by implication, by FileMaker.
 * 
 * Example PHP script to illustrate how to view a particular record in a database using PHP API.
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

// Since we're passed in recid via param (i.e. viewRecord.php?recid=n), use
// FileMaker::getRecordById() to directly get record object with recid accessed
// via $_GET[] array
$record = $fm->getRecordById('Form View', $_GET['recid']);

if (FileMaker::isError($record)) {
    echo "<body>Error: " . $record->getMessage(). "</body>";
    exit;
}
?>
<head>
<title><?php echo $record->getField('Title'); ?></title>
<!-- declare charset as UTF-8 -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="style.css">
</head>
<body>
<table>
<tr><th id="table-title" colspan="3">America 24/7 Collection</th></tr>
<tr><th>Title</th><td><?php echo $record->getField('Title'); ?></td></tr>
<tr><th>Author</th><td><?php echo $record->getField('Author'); ?></td></tr>
<tr><th>Publisher</th><td><?php echo $record->getField('Publisher'); ?></td></tr>
<tr><th>Cover Photo Credit</th><td><?php echo $record->getField('Cover Photo Credit'); ?></td></tr>
<tr><th>Number of Pages</th><td><?php echo $record->getField('Number of Pages'); ?></td></tr>
<tr><th>Status</th><td><?php echo $record->getField('Status'); ?></td></tr>
<tr><th>Quantity in Stock</th><td><?php echo $record->getField('Quantity in Stock'); ?></td></tr>
<tr><th>Description</th><td><?php echo $record->getField('Description'); ?></td></tr>
<tr><th>Cover Image</th><td><?php if ($record->getField('Cover Image')) {?> <IMG src="containerBridge.php?path=<?php echo urlencode($record->getField('Cover Image')); ?>"> <?php } ?></td></tr>
<tr><td colspan="2" style="text-align: center"><a href="editRecord.php?recid=<?php echo $record->getRecordId(); ?>">Edit this record</a></td></tr>
<tr><td colspan="2" style="text-align: center"><a href="deleteRecord.php?recid=<?php echo $record->getRecordId(); ?>">Delete this record</a></td></tr>
<tr><td colspan="2" style="text-align: center"><a href="displayRecords.php">Back to Record List</a></td></tr>
</table>
</body>
</html>
