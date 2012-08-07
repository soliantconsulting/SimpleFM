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
        //Abrufen der Variable 'active_questionnaire_id' von $_POST daten
    
        if(isset($_POST['active_questionnaire_id'])) {
    
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
        }
    
    
        //'respondent_exists' wird gesetzt, wenn der Benutzer von der Seite Respondent.php kommt.
    
        if (isset($_POST['respondent_exists'])) {
            
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
    
            //Fragenummer setzen
            $question_number = 0;
        }
    
        
        /*
         * 'store_information' wird gesetzt, wenn der Benutzer von der Seite handle_form.php kommt.
         * Wenn gesetzt, müssen wir die Antwort des Benutzers auf die vorherige Frage speichern (gespeichert in $_POST).
         */
    
        if (isset($_POST['store_information'])) {
            
            //Fragedaten speichern
            $question_type = $_POST['question_type'];
            $respondent_recid = $_POST['respondent_id'];
            $last_question = $_POST['last_question'];
            $cur_question = $_POST['cur_question'];
            $question_number = $_POST['question_number'];
    
            /*
             * Antwort in eine Zeichenfolge aus den verschiedenen Formulareingabetypen
             * (Text, Auswahlschaltflächen, Pulldownmenüs oder Markierungsfelder) übersetzen
             */
    
            $translatedAnswer = "";
            if ($question_type == "text" ) {
                $translatedAnswer = $_POST['text_answer'];
            
            } else if ($question_type =="radio" || $question_type =="ranking") {
    
                //Die Optionen Einstufung und Auswahl werden auf gleiche Weise behandelt.
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
            
            //Antwortenden-Datensatz abrufen, damit die Frageantwort wiederhergestellt werden kann
            $respondent_rec = getRespondentRecordFromRespondentID($respondent_recid);
            
            //neue Ausschnittzeile im Ausschnitt 'Responses' im Layout 'Respondent' erstellen
            $new_response = $respondent_rec->newRelatedRecord('Responses');
            
            //Frage-ID und Antwort in der neuen Ausschnittzeile setzen
            $new_response->setField('Responses::Question ID', $cur_question);
            $new_response->setField('Responses::Response', $translatedAnswer);
    
            //Änderung bestätigen
            $result = $new_response->commit();
            
            //Auf Fehler prüfen
            if (FileMaker::isError($result)) {
    
                echo "<p>Fehler: " . $result->getMessage() . "<p>";
                exit;
            }
        }
    
        
        /*
         * 'Continue_to_Survey' wird in zwei Fällen gesetzt:
         *         - wenn der Benutzer von Respondent.php kommt (Benutzer hat noch KEINE Fragen beantwortet)
         *         - wenn der Benutzer von handle_form.php kommt und noch mindestens eine Frage fehlt.
         * Wenn gesetzt, müssen wir das HTML-Formular für die entsprechende Frage stellen
         */
    
        if (isset ($_POST['Continue_to_Survey'])) {
    
            //Aktiven Fragebogen und seine Datensatz-ID abrufen
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
    
            $records = $result->getRecords();
            
            //Die Anzahl der Fragen abrufen und die letzte Frage für künftiges Prüfen speichern
            $number_of_questions = count($records);
            $last_question = $records[$number_of_questions - 1]->getRecordID();
    
            //Frage und Fragetyp des aktuellen Fragedatensatzes abrufen
            $question = $records[$question_number];
            $real_question = $question->getField('question');
            $question_type = $question->getField('Question Type');
            $cur_question = $records[$question_number]->getRecordID();
    
            //Zeile ausgeben, die angibt, bei welcher Frage man sich befindet
            echo "<h4>Frage " . ($question_number + 1) . " von " . $number_of_questions . ":</h4>";
    
            //Frage ausgeben
            echo "<p>".$real_question."</p>";
    
            /*
             * Ist das NICHT die letzte Frage, kommt der Benutzer durch Absenden zurück zu
             * der Seite für die nächste Frage, sonst auf die letzte Übersichtsseite.
             */
    
            if ($cur_question != $last_question) {
                echo '<form action="handle_form.php" method= "POST">';
    
            } else {
                echo '<form action="thankyou.php" method= "POST">';
            }
            
    
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
                if (FileMaker::isError($relatedSet))  {
                    echo "<p>Fehler: " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //der Startmarke für ein HTML-Pulldownmenü ausgegeben werden.
                echo '<select name="pulldown">';
                
                //Jede der möglichen Antworten als Option im HTML-Pulldownmenü anzeigen
                foreach ($relatedSet as $relatedRow) {
                
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
            
            //question_number hochzählen
            $question_number++;
   
            echo '<hr />';
            
            /*
             * Hier werden die versteckten Formularwerte gesetzt, die per $_POST an die nächste Seite übergeben werden.
             * 
             *         'store_information' -- immer gesetzt, weist die nächste Seite an, die Antwort für DIESE Frage zu speichern
             *         'question_number' -- Nummer der nächsten Frage
             *         'question_type' -- das Format der Antwort (Text, Option, Einstufung, Pulldown oder Markierungsfeld)
             *         'respondent_id' -- die Datensatz-ID des Antwortenden-Datensatzes
             *         'cur_question' -- die Datensatz-ID des aktuellen Fragedatensatzes
             *         'last_question' -- die Datensatz-ID des letzten Fragedatensatzes in diesem Fragebogen
             *         'active_questionnaire_id' -- die Datensatz-ID des aktuellen Fragebogens
             */
            
            echo '<input type="hidden" name="store_information" value="store_information"/>';
            echo '<input type="hidden" name="question_number" value="' . $question_number . '">';
            echo '<input type="hidden" name="question_type" value="' . $question_type . '">';              
            echo '<input type="hidden" name="respondent_id" value="' . $respondent_recid . '"/>';
            echo '<input type="hidden" name="cur_question" value="' . $cur_question . '"/>';
            echo '<input type="hidden" name="last_question" value="' . $last_question . '"/>';
            echo '<input type="hidden" name="active_questionnaire_id" value="' . $active_questionnaire_id . '"/>';
    
            
            /*
             * Der Wert der Variablen $handler_action weist die Handler-Seite (handle_form.php
             * oder finalSummary.php) an, ob dies die letzte Frage ist oder nicht.
             */
            
            if ($cur_question != $last_question) {
                $handler_action = "Continue_to_Survey";
            
            } else {
                $handler_action="Questionnaire_Over";
            }
            
            echo '<input type="Submit" name="' . $handler_action . '" value="Senden" />';
            
            //Endemarke für das HTML-Formular ausgeben
            echo '</form>';
        }
    ?>

</body>
</html>

