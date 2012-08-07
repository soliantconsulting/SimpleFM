<?php

    /**
    * FileMaker PHP Example
    *
    *
    * Copyright 2006, FileMaker, Inc.  All rights reserved.
    * NOTE: Use of this source code is subject to the terms of the FileMaker
    * Software License which accompanies the code. Your use of this source code
    * signifies your agreement to such license terms and conditions. Except as
    * expressly granted in the Software License, no other copyright, patent, or
    * other intellectual property license or right is granted, either expressly or
    * by implication, by FileMaker.
    *
    */
    
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>Questionnaire Tutorial</title>
</head>
<body>
    <?php include ("dbaccess.php"); ?>
    
    <h1>FileMaker Questionnaire System</h1>
    <h2>Welcome to the Questionnaire Tutorial.</h2>
    <hr />
    
    <?php
    
        /**
         * We need the ID of the 'Active Questionnaire' in the database. 
         * Since there can only be one active questionnaire at a time, 
         * this can be retrieved from the database by using a simple 'find all' command.
         */
        
        
        //Create the 'find all' command and specify the layout
        $findCommand =& $fm->newFindAllCommand('Active Questionnaire');
        
        //Perform the find and store the result
        $result = $findCommand->execute();
        
        //Check for an error
        if (FileMaker::isError($result)) {
            echo "<p>Error: " . $result->getMessage() . "</p>";
            exit;
        }
        
        //Store the matching records
        $records = $result->getRecords();
        
        //Retrieve and store the questionnaire_id of the active questionnaire
        $record = $records[0];
        $active_questionnaire_id =  $record->getField('questionnaire_id');
        
        /**
         * To get the name of the active questionnaire, we can perform another find
         * on the 'questionnaire' layout using the $active_questionniare_id.
         */
        
        //create the find command and specify the layout
        $findCommand =& $fm->newFindCommand('questionnaire');
        
        //Specify the field and value to match against.
        $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
       
       //Perform the find
        $result = $findCommand->execute();
        
        //Check for an error
        if (FileMaker::isError($result)) {
            echo "<p>Error: " . $result->getMessage() . "</p>";
            exit;
        }
       
       //Store the matching record
        $records = $result->getRecords();
        $record = $records[0];
        
        //Get the 'Questionnaire Name' field from the record and display it
        echo "<p>Thanks for taking the " . $record->getField('Questionnaire Name') . "</p>"; 
        
        //Get the 'Description' field from the record and display it
        echo "<p>Questionnaire Description: "  . $record->getField('Description') . "</p>";
        
        //Get the 'Graphic' field from the record and display it using ContainerBridge.php        
        echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
    ?>
    
</body>
</html>
