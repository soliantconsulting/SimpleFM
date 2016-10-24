<html>
<head>
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

// If there was a 'recid' parameter passed in, then we're editing a particular record.
// Otherwise, we're creating a new record
 
// Set <title> accordingly
 
$record = null;
?>
<title>
<?php
if (array_key_exists('recid', $_GET)) {
	// 'recid' parameter was passed in, use it to grab record object
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
	    echo "Error: {$result->getMessage()}\n";
	    exit;
	}
	
	echo "Editing " . $record->getField('Title');
} else {
	// no 'recid', so we're creating a new record
	?> 
Create New Record
<?php
}
?>
</title>
<!-- declare charset as UTF-8 -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="style.css">
</head>
<body>
<form action="handleForm.php" method="post">
<table>
<tr><th id="table-title" colspan="3">America 24/7 Collection</th></tr>
<tr><th>Title</th><td><input type="text" size="80" name="Title" value="<?php echo $record != null ? $record->getField('Title') : null; ?>"></td></tr>
<tr><th>Author</th><td><input type="text" size="80" name="Author" value="<?php echo $record != null ? $record->getField('Author') : null; ?>"></td></tr>
<tr><th>Publisher</th><td><input type="text" size="80" name="Publisher" value="<?php echo $record != null ? $record->getField('Publisher') : null; ?>"></td></tr>
<tr><th>Cover Photo Credit</th><td><input type="text" size="80" name="Cover Photo Credit" value="<?php echo $record != null ? $record->getField('Cover Photo Credit') : null; ?>"></td></tr>
<tr><th>Number of Pages</th><td><input type="text" size="80" name="Number of Pages" value="<?php echo $record != null ? $record->getField('Number of Pages') : null; ?>"></td></tr>
<tr><th>Status</th><td><input type="text" size="80" name="Status" value="<?php echo $record != null ? $record->getField('Status') : null; ?>"></td></tr>
<tr><th>Quantity in Stock</th><td><input type="text" size="80" name="Quantity in Stock" value="<?php echo $record != null ? $record->getField('Quantity in Stock') : null; ?>"></td></tr>
<tr><th>Description</th><td><textarea name="Description" cols="80" rows="10"><?php echo $record != null ? $record->getField('Description') : null; ?></textarea></td></tr>

<tr><td colspan=2>
<?php
// output OK (submit) and Cancel buttons
if ($record != null) {
	// if we're editing a record, submit button is labeled "OK"
?>
<input type="hidden" name="recid" value="<?php echo $record->getRecordId(); ?>"> 
<button type="submit" name="action" value="edit">OK</button>
<?php
} else {
	// otherwise, submit button is "Create New Record"
?>
<button type="submit" name="action" value="edit">Create New Record</button>
<?php
}
?>
<button type="submit" name="action" value="cancel">Cancel</button>
</td></tr>
</table>
</form>
</body>
</html>
