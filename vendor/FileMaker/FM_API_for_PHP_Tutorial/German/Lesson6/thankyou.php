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
    <title>Übersicht der Umfrageantworten</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
    <?php include ("dbaccess.php");?>

    <div id="container">

        <h1>FileMaker-Fragebogen-System</h1>
        <hr />
        <h2>Übersicht</h2>
        <hr />

        <?php
            //die Datensatz-ID des Antwortenden-Datensatzes speichern
            $respondent_recid = $_POST['respondent_id'];
    
            /*
             * 'store_information' wird gesetzt, wenn der Benutzer von der Seite handle_form.php kommt.
             * If set, we need to store the user's response to the final question (stored in $_POST)
             * bevor wir die Übersicht anzeigen.
             */
            
            if (isset($_POST['store_information'])) {
                
                //Fragedaten speichern
                $question_type = $_POST['question_type'];
                $cur_question = $_POST['cur_question'];
                
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
    
            //active_questionnaire_id speichern
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
            
            //Suche im Layout 'questionnaire' nach aktivem Fragebogen durchführen
            $findCommand =& $fm->newFindCommand('questionnaire');
            $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
            $result = $findCommand->execute();
    
            //Auf Fehler prüfen
            if (FileMaker::isError($result)) {
                echo "<p>Fehler: " . $result->getMessage() . "<p>";
                exit;
            }
    
            $records = $result->getRecords();
            $record = $records[0];
    
            
            //Abrufen des Felds 'Graphic' aus dem Datensatz und Anzeige mithilfe von ContainerBridge.php
            echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
            
            //Abrufen des Felds 'Questionnaire Name' aus dem Datensatz und Anzeige
            echo '<p>Fragebogenname: ' . $record->getField('Questionnaire Name') . '</p>';
            
            //Antwortenden-Datensatz für diesen Benutzer abrufen
            $respondent_record = getRespondentRecordFromRespondentID($respondent_recid);
            
            //Präfix, Vor- und Nachname des Antwortenden abrufen und anzeigen
            echo '<p>Name: ' . $respondent_record->getField('Prefix') . ' ' . $respondent_record->getField('First Name') . ' ' . $respondent_record->getField('Last Name') . '</p>';
        ?>

        <p>Vielen Dank, dass Sie den Fragebogen ausgefüllt haben. Hier sehen Sie eine Übersicht Ihrer Antworten:"</p> 
        <table>
            <tr> 
                <th>Fragen</th>
                <th>Antworten</th>
            </tr>

            <?php
            
                /*
                 * Dieser Abschnitt schreibt die Zeilen der Übersichtstabelle, jede stellt ein Frage-Antwort-Paar dar.
                 */
                //'Responses'-Ausschnitt abrufen
    
                $response_related_set = $respondent_record->getRelatedSet('Responses');
                //Auf Fehler prüfen
                
                if (FileMaker::isError($response_related_set)) {
                    echo "<tr><td>Fehler: " . $response_related_set->getMessage() . "</td></tr>";
                    exit;
                }
                
                //jedes Frage-Antwort-Paar abrufen und als neue Tabellenzeile anzeigen
                foreach ($response_related_set as $response_related_row) {
    
                    $question = $response_related_row->getField('Questions 2::question');
                    $answer = $response_related_row->getField('Responses::Response');
                    
                    //konvertiert etwaige Zeilenenden in der Antwort zu Kommata
                    $answer = str_replace("\n",", ",$answer);
                    
                    echo '<tr><td>' . $question . '</td>';
                    echo '<td>' . $answer . '</td></tr>';
                }
            ?>
        </table>
    </div>
</body>
</html>