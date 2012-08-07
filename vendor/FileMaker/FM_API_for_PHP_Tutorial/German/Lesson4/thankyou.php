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
</head>
<body>
    <?php include ("dbaccess.php");?>

    <h1>FileMaker-Fragebogen-System</h1>
    <hr />

    <?php
        
        //die Datensatz-ID des Antwortenden-Datensatzes speichern
        $respondent_recid = $_POST['respondent_id'];
    
        /*
         * 'store_information' wird gesetzt, wenn der Benutzer von der Seite handle_form.php kommt.
         * Wenn gesetzt, müssen wir die Antwort des Benutzers auf die letzte Frage speichern (gespeichert in $_POST).
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
            
            if ($question_type == "text" )  {
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

    ?>

    <p>Vielen Dank, dass Sie den Fragebogen ausgefüllt haben.</p>

</body>

</html>