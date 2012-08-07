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
    <title>Survey Answers Summary</title>
</head>
<body>
    <?php include ("dbaccess.php");?>

    <h1>FileMaker Questionnaire System</h1>
    <hr />

    <?php
        //store the record id of the Respondent record
        $respondent_recid = $_POST['respondent_id'];
    
        /*
         * 'store_information' will be set if the user is coming from the handle_form.php page.
         * If set, we need to store the user's response to the final question (stored in $_POST).
         */
         
        if (isset($_POST['store_information']))
        {
            //Store the question data
            $question_type = $_POST['question_type'];
            $cur_question = $_POST['cur_question'];
            
            /*
             * Translate the response into a string from the various 
             * form input types (text, radio buttons, pulldown menus, or checkboxes)
             */
            
            $translatedAnswer = "";
            if ($question_type == "text" ) {
                $translatedAnswer = $_POST['text_answer'];
            
            } else if ($question_type =="radio" || $question_type =="ranking") {
                //Ranking and Radio options are handled the same way.
                $translatedAnswer = $_POST['radio_answer'];
            
            }  else if ($question_type == "pulldown") {
                $translatedAnswer = $_POST['pulldown'];
            
            } else if($question_type == "checkbox") {
                if(is_array($_POST['cbanswer'])) {
                    $translatedAnswer = implode("\r", $_POST['cbanswer']);
                } else {
                    $translatedAnswer = $_POST['cbanswer'];    
                }
            }
            
            //get the respondent record so that we can restore this question response
            $respondent_rec = getRespondentRecordFromRespondentID($respondent_recid);
            
            //create a new portal row in the 'Responses' portal on the 'Respondent' layout
            $new_response = $respondent_rec->newRelatedRecord('Responses');
            
            //set the question id and answer in the new portal row
            $new_response->setField('Responses::Question ID', $cur_question);
            $new_response->setField('Responses::Response', $translatedAnswer);
            
            //commit the chage
            $result = $new_response->commit();
            
            //check for an error
            if (FileMaker::isError($result)) {
                echo "<p>Error: " . $result->getMessage() . "<p>";
                exit;
            }
        }
    ?>
    
    <p>Thank you for completing the questionnaire.</p>

</body>

</html>