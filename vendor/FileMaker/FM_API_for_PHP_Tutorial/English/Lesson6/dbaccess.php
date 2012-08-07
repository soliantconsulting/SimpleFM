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
    
    /**
     * This file is responsible for creating and initializing the FileMaker object.
     * This object allows you to manipulate data in the database. To do so, simply 
     * include this file in the PHP file that needs access to the FileMaker database.
     */
    
    //include the FileMaker PHP API
    require_once ('FileMaker.php');
    
    
    //create the FileMaker Object
    $fm = new FileMaker();
    
    
    //Specify the FileMaker database
    $fm->setProperty('database', 'questionnaire');
    
    //Specify the Host
    $fm->setProperty('hostspec', 'http://localhost'); //temporarily hosted on local server
    
    /**
     * To gain access to the questionnaire database, use the default administrator account,
     * which has no password. To change the authentication settings, open the database in 
     * FileMaker Pro and select "Manage > Accounts & Privileges" from the "File" menu. 
    */
    
    $fm->setProperty('username', 'web');
    $fm->setProperty('password', 'web');
    
    /**
     * This function performs a Find on the 'Respondent' layout using the value passed in
     * via $respondent_id. If found, it returns the first matching record. Otherwise, it returns null.
     */
    
    function getRespondentRecordFromRespondentID($respondent_id) {
        global $fm;
        
        //Specify the layout
        $find = $fm->newFindCommand('Respondent');
        
        //Specify the field and value to match against. In this case, 'Respondent ID' is the field
        // and $respondent_id is the value.
        $find->addFindCriterion('Respondent ID', $respondent_id);
        
        //Perform the find
        $results = $find->execute();
        
        //Check for errors
        if (!FileMaker::isError($results)) {    
            
            //No errors, return first matching result
            $records = $results->getRecords();
            return $records[0];
        
        } else {
            //There was an error, return null (i.e. no matches found)
            return null;
        }
    }
?>
