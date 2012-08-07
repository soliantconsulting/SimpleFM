<?php
    /**
    * FileMaker PHP-Beispiel
    *
    *
    * Copyright 2006 FileMaker, Inc. Alle Rechte vorbehalten.
    * HINWEIS: Die Verwendung des Quellcodes unterliegt den Bestimmungen der
    * FileMaker-Softwarelizenz, die dem Quellcode beliegt. Durch Ihre Verwendung
    * des Quellcodes erklären Sie sich mit diesen Lizenzbestimmungen einverstanden.
    * Mit Ausnahme der ausdrücklich in der Softwarelizenz gewährten Rechte werden
    * keine anderen Urheberrechts-, Patent- oder anderen Lizenzen/Rechte an geistigem
    * Eigentum von FileMaker, Inc. gewährt, weder ausdrücklich noch stillschweigend.
    *
    */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>Fragen</title>
</head>
<body>

    <?php include ("dbaccess.php"); ?>

    <h1>FileMaker-Fragebogen-System</h1>
    <hr />
    <?php
        //'respondent_exists' wird gesetzt, wenn der Benutzer von der Seite Respondent.php kommt.
    
        if (isset($_POST['respondent_exists']))  {
            
            //Abrufen der Benutzereingabe von $_POST daten    
            $respondent_data = array(
                                        'Prefix'            => $_POST['prefix'],
                                        'First Name'        => $_POST['first_name'],
                                        'Last Name'         => $_POST['last_name'],
                                        'questionnaire_id'  => $_POST['active_questionnaire_id'],
                                        'Email Address'     => $_POST['email']
                                    );
    
            //Benutzereingabe validieren.
            if (    empty($respondent_data['Prefix']) 
                ||  empty($respondent_data['First Name'])
                ||  empty($respondent_data['Last Name'])
                ||  empty($respondent_data['Email Address'])) {
                
                //Wenn Daten fehlen, Meldung anzeigen.
                echo '<h3>Einige der Angaben fehlen. Bitte gehen Sie zurück und füllen Sie alle Felder aus.</h3>';
                exit;
    
            } else {
                
                //Wenn die Benutzereingabe gültig ist, Vornamen, Nachnamen und E-Mail-Adresse in Layout Respondent hinzufügen.
                $newRequest =& $fm->newAddCommand('Respondent', $respondent_data);
                $result = $newRequest->execute();
                
                //Auf Fehler prüfen
                if (FileMaker::isError($result)) {
                    echo "<p>Fehler: " . $result->getMessage() . "<p>";
                    exit;
                }
                
                $records = $result->getRecords();
                $record = $records[0];
                $respondent_recid = $record->getField('Respondent ID');
            }
    
            
            /*
             * Jetzt müssen wir die erste Frage im Fragebogen abrufen und sie dem Benutzer anzeigen.
             */
    
            //Aktiven Fragebogen und seine Datensatz-ID abrufen
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
            $active_questionnaire = $fm->getRecordById('questionnaire',$active_questionnaire_id);    

            //Suche im Layout 'Questions' nach Fragen durchführen, die zu diesem Fragebogen gehören    
            $findCommand =& $fm->newFindCommand('Questions');    
            $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);    
            $result = $findCommand->execute();
            
            //Auf Fehler prüfen
            if (FileMaker::isError($result)) {
                echo "<p>Fehler: " . $result->getMessage() . "<p>";
                exit;
            }
    
    
    
            //Frage und Fragetyp des ersten Fragedatensatzes abrufen    
            $records = $result->getRecords();
            $question = $records[0];    
            $real_question = $question->getField('question');    
            $question_type = $question->getField('Question Type');    
            $cur_question = $records[0]->getRecordID();
    
            //Frage ausgeben    
            echo "<p>".$real_question."</p>";    
            echo '<form action="thankyou.php" method= "POST">';            
    
            /*
             * Entsprechendes HTML-Formularelement basierend auf dem Wert von $question_type ausgeben
             */
    
            if ($question_type == "text" ) {
    
                //Texteingabe anzeigen    
                echo '<input type="text" name="text_answer" size="60" value=""/>';
                
            } else if ($question_type =="radio" || $question_type =="ranking") {
                
                /*
                 * Wenn question_type Auswahlschaltflächen verlangt, muss eine Liste
                 * erlaubter Antworten für diese Frage abgerufen werden.
                 * Hinweis: Fragen vom Typ 'radio' und 'ranking' werden identisch implementiert
                 * und beide verwenden Auswahlschaltflächen für die Benutzereingabe.
                 */
    
                //Ausschnitt 'question_answers' abrufen
    
                $relatedSet = $question->getRelatedSet('question_answers');
    
                //Auf Fehler prüfen
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>Fehler: " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //Jede der möglichen Antworten als HTML-Auswahlschaltfläche anzeigen
                foreach ($relatedSet as $relatedRow) {
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<input type= "radio" name= "radio_answer" value= "'. $possible_answer .'">' . $possible_answer . '<br/>'; 
                }
    
            } else if ($question_type == "pulldown") {
    
                /*
                 * Wenn question_type ein Pulldownmenü verlangt, muss eine Liste
                 * erlaubter Antworten für diese Frage abgerufen werden.
                 */
                
                //Ausschnitt 'question_answers' abrufen
                $relatedSet = $question->getRelatedSet('question_answers');
                
                //Auf Fehler prüfen
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>Fehler: " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //der Startmarke für ein HTML-Pulldownmenü ausgegeben werden.
                echo '<select name="pulldown">';
                
                //Jede der möglichen Antworten als Option im HTML-Pulldownmenü anzeigen
                foreach ($relatedSet as $relatedRow)  {
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<option value="' . $possible_answer .'">' . $possible_answer . '</option>'; 
                 }
                
                //Endemarke für ein HTML-Pulldownmenü ausgeben
    
                echo '</select>';
    
            } else if($question_type == "checkbox") {
               
                /*
                 * Wenn question_type Markierungsfelder verlangt, muss eine Liste
                 * erlaubter Antworten für diese Frage abgerufen werden.
                 */
                
                //Ausschnitt 'question_answers' abrufen
                $relatedSet = $question->getRelatedSet('question_answers');
                
                //Auf Fehler prüfen
                
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>Fehler: " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //der möglichen Antworten als HTML-Markierungsfeld angezeigt werden
                foreach ($relatedSet as $relatedRow) {
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<input type= "checkbox" name="cbanswer[]" value= "' . $possible_answer . '"/ >' . $possible_answer . '<br/>';
                }
    
            } else {
                //Wenn $question_type nicht definiert ist oder nicht erkannt wird, standardmäßig eine HTML-Texteingabe verwenden
                echo '<input type="text" name="text_answer" size="60" value=""/>';
            }
            
            echo '<hr />';
            
            /*
             * Hier werden die versteckten Formularwerte gesetzt, die per $_POST an die nächste Seite übergeben werden.
             * 
             *         'store_information' -- immer gesetzt, weist die nächste Seite an, die Antwort für DIESE Frage zu speichern
             *         'question_type' -- das Format der Antwort (Text, Option, Einstufung, Pulldown oder Markierungsfeld)
             *         'respondent_id' -- die Datensatz-ID des Antwortenden-Datensatzes
             *         'cur_question' -- die Datensatz-ID des aktuellen Fragedatensatzes
             */
           
           echo '<input type="hidden" name="store_information" value="store_information"/>';
            echo '<input type="hidden" name="question_type" value="' . $question_type . '">';              
            echo '<input type="hidden" name="respondent_id" value="' . $respondent_recid . '"/>';
            echo '<input type="hidden" name="cur_question" value="' . $cur_question . '"/>';
            echo '<input type="Submit" name="submit" value="Senden" />';
            
            //Endemarke für das HTML-Formular ausgeben
            echo '</form>';
        }
    ?>

</body>

</html>

