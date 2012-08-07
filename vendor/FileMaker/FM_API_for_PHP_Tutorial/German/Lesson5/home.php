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
    <title>Fragebogen-Lehrgang</title>
</head>
<body>
    <?php include ("dbaccess.php"); ?>
    
    <h1>FileMaker-Fragebogen-System</h1>
    <h2>Willkommen beim Fragebogen-Lehrgang.</h2>
    <hr />
    <?php 
        
        /**
         * ID des ‘Active Questionnaire’ wird in der Datenbank benötigt. 
         * Da jeweils nur ein Fragebogen gleichzeitig aktiv sein kann, 
         * kann dieser über einen einfachen 'find all'-Befehl abgerufen werden.
         */
        
        //'find all'-Befehl erstellen und das Layout angeben
        $findCommand =& $fm->newFindAllCommand('Active Questionnaire');
        
        //Führen Sie die Suche durch und speichern Sie das Ergebnis.
        $result = $findCommand->execute();
        
        //Auf Fehler prüfen
        if (FileMaker::isError($result)) {
            echo "<p>Fehler: " . $result->getMessage() . "</p>";
            exit;
        }
        
        //Speichern Sie die entsprechenden Datensätze.
        $records = $result->getRecords();
        
        //Rufen Sie die questionnaire_id des aktiven Fragebogens ab und speichern Sie sie.
        $record = $records[0];
        $active_questionnaire_id =  $record->getField('questionnaire_id');
        
        /**
         * Um den Namen des aktiven Fragebogens abzurufen, können wir eine weitere Suche
         * im Layout 'questionnaire' mit $active_questionniare_id durchführen.
         */
        
        //Das Befehl find erstellen und das Layout angeben
        $findCommand =& $fm->newFindCommand('questionnaire');
        
        //Geben Sie das Feld und den Wert für den Abgleich an.
        $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
        
        //Suche durchführen
        $result = $findCommand->execute();
        
        //Auf Fehler prüfen
        if (FileMaker::isError($result)) {
            echo "<p>Fehler: " . $result->getMessage() . "</p>";
            exit;
        }
        
        //Entsprechenden Datensatz speichern
        $records = $result->getRecords();
        $record = $records[0];
        
        //Abrufen des Felds 'Questionnaire Name' aus dem Datensatz und Anzeige
        echo "<p>Vielen Dank für Ihre Teilnahme " . $record->getField('Questionnaire Name') . "</p>"; 
        
        //Abrufen des Felds 'Description' aus dem Datensatz und Anzeige
        echo "<p>Fragebogen-Beschreibung: "  . $record->getField('Description') . "</p>";
        
        //Abrufen des Felds 'Graphic' aus dem Datensatz und Anzeige mithilfe von ContainerBridge.php        
        echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
    ?>
    <form id="questionnaire_form" name="Respondent" align= "right" method="post" action="Respondent.php">
        <input type="hidden" name="active_questionnaire_id" value = "<?php echo $active_questionnaire_id; ?>" >
        <hr /> 
        <input type="submit" name="Submit" value="Weiter" />
    </form>
</body>
</html>
