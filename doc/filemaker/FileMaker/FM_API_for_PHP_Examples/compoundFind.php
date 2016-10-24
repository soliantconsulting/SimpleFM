<html>
<head>
<!-- declare charset as UTF-8 -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
</head>
<body>

<?php
/**
 * compoundFind.php
 * 
 * Copyright © 2005-2006, FileMaker, Inc. All rights reserved.
 * NOTE: Use of this source code is subject to the terms of the FileMaker
 * Software License which accompanies the code. Your use of this source code
 * signifies your agreement to such license terms and conditions. Except as
 * expressly granted in the Software License, no other copyright, patent, or
 * other intellectual property license or right is granted, either expressly or
 * by implication, by FileMaker.
 * 
 * Example PHP script to illustrate how to use compound find to find records in a database.
 * 
 * Requirements:
 *   1. Working FileMaker Server installation
 *   2. 'FMPHP_Sample' database hosted in FileMaker Server
 *
 */
 
// Include FileMaker API
require_once("FileMaker.php");

// Create a new connection to FMPHP_Sample database.
// Location of FileMaker Server is assumed to be on the same machine,
//  thus we assume hostspec is api default of 'http://localhost' as specified
//  in filemaker-api.php.
// If FMSA web server is on another machine, specify 'hostspec' as follows:
//   $fm = new FileMaker('FMPHP_Sample', 'http://10.0.0.1');
$fm = new FileMaker('FMPHP_Sample');

// Create FileMaker_Command_CompoundFind object on layout to search
$compoundFind =& $fm->newCompoundFindCommand('Form View');

// Create first find request on layout
$findreq =& $fm->newFindRequest('Form View');

// Create second find request on layout
$findreq2 =& $fm->newFindRequest('Form View');

// Create third find request on layout
$findreq3 =& $fm->newFindRequest('Form View');

// Specify search criterion for first find request
$findreq->addFindCriterion('Quantity in Stock', '<100');

// Specify search criterion for second find request
$findreq2->addFindCriterion('Quantity in Stock', '0');
$findreq2->setOmit(true);

// Specify search criterion for third find request
$findreq3->addFindCriterion('Cover Photo Credit', 'The Dallas Morning News');
$findreq3->setOmit(true);

// Add find requests to compound find command
$compoundFind->add(1,$findreq);
$compoundFind->add(2,$findreq2);
$compoundFind->add(3,$findreq3);

// Set sort order
$compoundFind->addSortRule('Title', 1, FILEMAKER_SORT_DESCEND);

// Execute compound find command
$result = $compoundFind->execute();

// If an error is found, return a message and exit.
if (FileMaker::isError($result)) {
    echo "Error: " . $result->getMessage(). "<br>";
    exit;
}

// Get records from found set
$records = $result->getRecords();

// Print number of records found
echo 'Found ' . count($records) . " results.<br><br>";

// Print out records
foreach ($records as $record) {
    echo $record->getField('Title') . "<br>";
}
?>
</body>
</html>





