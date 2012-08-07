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
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
    <?php include ("dbaccess.php");?>

    <div id="container">

        <h1>FileMaker Questionnaire System</h1>
        <hr />
        <h2>Summary</h2>
        <hr />

        <?php
            //store the record id of the Respondent record
            $respondent_recid = $_POST['respondent_id'];
    
            /*
             * 'store_information' will be set if the user is coming from the handle_form.php page.
             * If set, we need to store the user's response to the final question (stored in $_POST)
             * before we display the summary.
             */
            
            if (isset($_POST['store_information'])) {
                
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
                
                } else if ($question_type == "pulldown") {
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
    
            //store the active_questionnaire_id
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
            
            //Perform a find on the 'questionnaire' layout for the active questionnaire
            $findCommand =& $fm->newFindCommand('questionnaire');
            $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
            $result = $findCommand->execute();
    
            //check for an error
            if (FileMaker::isError($result)) {
                echo "<p>Error: " . $result->getMessage() . "<p>";
                exit;
            }
    
            $records = $result->getRecords();
            $record = $records[0];
    
            
            //get the 'Graphic' field from the record and display it using ContainerBridge.php
            echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
            
            //get the 'Questionnaire Name' field from the record and display it
            echo '<p>Questionnaire Name: ' . $record->getField('Questionnaire Name') . '</p>';
            
            //get the Respondent record for this user
            $respondent_record = getRespondentRecordFromRespondentID($respondent_recid);
            
            //get the respondent's prefix, first, and last name and display it
            echo '<p>Name: ' . $respondent_record->getField('Prefix') . ' ' . $respondent_record->getField('First Name') . ' ' . $respondent_record->getField('Last Name') . '</p>';
        ?>

        <p>Thank you for completing the questionnaire. Here is a summary of your responses:</p> 
        <table>
            <tr> 
                <th>Questions</th>
                <th>Answers</th>
            </tr>

            <?php
            
                /*
                 * This section writes the rows of the summary table, each representing a Question-Answer pair.
                 */
                //get the 'Responses' portal
    
                $response_related_set = $respondent_record->getRelatedSet('Responses');
                //check for an error
                
                if (FileMaker::isError($response_related_set)) {
                    echo "<tr><td>Error: " . $response_related_set->getMessage() . "</td></tr>";
                    exit;
                }
                
                //get and display each Question-Answer pair as a new table row
                foreach ($response_related_set as $response_related_row) {
    
                    $question = $response_related_row->getField('Questions 2::question');
                    $answer = $response_related_row->getField('Responses::Response');
                    
                    //converts any line returns in the answer to commas
                    $answer = str_replace("\n",", ",$answer);
                    
                    echo '<tr><td>' . $question . '</td>';
                    echo '<td>' . $answer . '</td></tr>';
                }
            ?>
        </table>
    </div>
</body>
</html>