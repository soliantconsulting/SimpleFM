<html>
<head>
<!-- declare charset as UTF-8 -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
</head>
<body>
<?php
/**
 * listDatabases.php
 * 
 * Copyright © 2005-2006, FileMaker, Inc. All rights reserved.
 * NOTE: Use of this source code is subject to the terms of the FileMaker
 * Software License which accompanies the code. Your use of this source code
 * signifies your agreement to such license terms and conditions. Except as
 * expressly granted in the Software License, no other copyright, patent, or
 * other intellectual property license or right is granted, either expressly or
 * by implication, by FileMaker.
 * 
 * Example PHP script to illustrate how to list databases on the server.
 * 
 * Requirements:
 *   1. Working FileMaker Server installation
 *   2. 'FMPHP_Sample' database hosted in FileMaker Server
 *
 */
 
// Include FileMaker API
require_once ('FileMaker.php');

// Create a new connection to server without specifying database or hostspec.
$fm = new FileMaker();

// Set 'hostspec' property using setProperty()
$fm->setProperty('hostspec', 'http://localhost');

$databases = $fm->listDatabases();

// If an error is found, return a message and exit.
if (FileMaker::isError($databases)) {
    printf("Error %s: %s\n", $databases->getCode());
    "<br>";
    printf($databases->getMessage());
    exit;
}

// Print out layout names
foreach ($databases as $db) {
    echo "$db<br>";
}

?>
</body>
</html>
