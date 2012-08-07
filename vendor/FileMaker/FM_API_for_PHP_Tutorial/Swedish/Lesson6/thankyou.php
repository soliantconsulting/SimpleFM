<?php
    /**
    * FileMaker PHP-exempel
    *
    *
    * Copyright 2006, FileMaker, Inc. Med ensamrätt.
    * OBS! Denna källkod får endast användas i enlighet med villkoren i FileMakers 
    * programvarulicens som följer med koden. Om du använder denna källkod
    * innebär det att du accepterar dessa licensvillkor. Utom för det
    * som uttryckligen medges i programvarulicensen görs inga övriga åtaganden från
    * FileMaker gällande copyright, patent eller annan intellektuell egendom, varken 
    * uttryckligen eller underförstått.
    *
    */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>

<head>
    <title>Sammanfattning av frågeformulärssvar</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
    <?php include ("dbaccess.php");?>

    <div id="container">

        <h1>FileMakers frågeformulärssystem</h1>
        <hr />
        <h2>Statistik</h2>
        <hr />

        <?php
            //lagra post-ID för posten för svarande
            $respondent_recid = $_POST['respondent_id'];
    
            /*
             * 'store_information' kommer att ställas in om användaren kommer från sidan handle_form.php.
             * If set, we need to store the user's response to the final question (stored in $_POST)
             * innan vi visar sammanfattningen.
             */
            
            if (isset($_POST['store_information'])) {
                
                //Lagra frågedata
                $question_type = $_POST['question_type'];
                $cur_question = $_POST['cur_question'];
                
                /*
                 * Översätt svaret till en sträng från de olika  
                 * typerna av formulärindata (text, alternativknappar, rullgardinsmenyer eller kryssrutor)
                 */
                
                $translatedAnswer = "";
                if ($question_type == "text" ) {
                    $translatedAnswer = $_POST['text_answer'];
                
                } else if ($question_type =="radio" || $question_type =="ranking") {
                    //Rangordning och alternativknappar hanteras på samma sätt.
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
                
                //hämta svarandens post så att vi kan återställa detta frågesvar
                $respondent_rec = getRespondentRecordFromRespondentID($respondent_recid);
                
                //skapa en ny portalrad i 'Responses'-portalen i layouten 'Respondent'
                $new_response = $respondent_rec->newRelatedRecord('Responses');
                
                //ställ in fråge-id och svar i den nya portalraden
                $new_response->setField('Responses::Question ID', $cur_question);
                $new_response->setField('Responses::Response', $translatedAnswer);
                
                //utför ändringen 
                $result = $new_response->commit();
                
                //kontrollera om fel finns
                if (FileMaker::isError($result)) {
                    echo "<p>Fel: " . $result->getMessage() . "<p>";
                    exit;
                }
            }
    
            //lagra active_questionnaire_id
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
            
            //Utför en sökning i layouten 'questionnaire' för aktivt frågeformulär
            $findCommand =& $fm->newFindCommand('questionnaire');
            $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
            $result = $findCommand->execute();
    
            //kontrollera om fel finns
            if (FileMaker::isError($result)) {
                echo "<p>Fel: " . $result->getMessage() . "<p>";
                exit;
            }
    
            $records = $result->getRecords();
            $record = $records[0];
    
            
            //hämta fältet 'Graphic' från posten och visa det med hjälp av ContainerBridge.php
            echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
            
            //hämta fältet 'Questionnaire Name' från posten och visa det
            echo '<p>Frågeformulärsnamn: ' . $record->getField('Questionnaire Name') . '</p>';
            
            //hämta svarandens post för den här användaren
            $respondent_record = getRespondentRecordFromRespondentID($respondent_recid);
            
            //hämta svarandens prefix, förnamn och efternamn och visa det 
            echo '<p>Namn: ' . $respondent_record->getField('Prefix') . ' ' . $respondent_record->getField('First Name') . ' ' . $respondent_record->getField('Last Name') . '</p>';
        ?>

        <p>Tack för att du har fyllt i frågeformuläret. Här följer en sammanfattning av dina svar:"</p> 
        <table>
            <tr> 
                <th>Frågor</th>
                <th>Svar</th>
            </tr>

            <?php
            
                /*
                 * Det här avsnittet skriver raderna i sammanfattningstabellen där var och en representerar ett par av fråga-svar.
                 */
                //hämta portalen 'Responses' 
    
                $response_related_set = $respondent_record->getRelatedSet('Responses');
                //kontrollera om fel finns
                
                if (FileMaker::isError($response_related_set)) {
                    echo "<tr><td>Fel: " . $response_related_set->getMessage() . "</td></tr>";
                    exit;
                }
                
                //hämta och visa alla par med fråga-svar som en ny tabellrad
                foreach ($response_related_set as $response_related_row) {
    
                    $question = $response_related_row->getField('Questions 2::question');
                    $answer = $response_related_row->getField('Responses::Response');
                    
                    //konverterar radreturer i svaret till kommatecken
                    $answer = str_replace("\n",", ",$answer);
                    
                    echo '<tr><td>' . $question . '</td>';
                    echo '<td>' . $answer . '</td></tr>';
                }
            ?>
        </table>
    </div>
</body>
</html>