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
    <title>Thanks</title>
</head>
<body>

    <?php include ("dbaccess.php"); ?>

    <h1>FileMaker Questionnaire System</h1>
    <hr />

    <?php
        
        //'respondent_exists' will be set if the user is coming from the Respondent.php page
        if (isset($_POST['respondent_exists'])) {
           
           //Grab the user input from the $_POST data
            $respondent_data = array(
                'Prefix'            => $_POST['prefix'],
                'First Name'        => $_POST['first_name'],
                'Last Name'         => $_POST['last_name'],
                'questionnaire_id'  => $_POST['active_questionnaire_id'],
                'Email Address'     => $_POST['email']
            );
    
    
    
            //Validate the user input. 
    
            if (        empty($respondent_data['Prefix']) 
                    ||  empty($respondent_data['First Name'])
                    ||  empty($respondent_data['Last Name'])
                    ||  empty($respondent_data['Email Address'])
                ) {
                
                //If data is missing, prompt them with a message.
                echo '<h3>Some of your information is missing. Please go back and fill out all of the fields.</h3>';
            
            } else {
                
                //If user input is valid, add the first name, last name and the email address in the Respondent layout
                $newRequest =& $fm->newAddCommand('Respondent', $respondent_data);
                $result = $newRequest->execute();
                
                //check for an error
                if (FileMaker::isError($result)) {
                    
                    echo "<p>Error: " . $result->getMessage() . "<p>";
                    exit;
                }
                
                //Display a success/thank you message
                echo '<p>Thank you for your information.</p>';
            }
        }
    ?>

</body>
</html>

