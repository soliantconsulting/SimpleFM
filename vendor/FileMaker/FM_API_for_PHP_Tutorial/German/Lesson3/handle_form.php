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
    <title>Vielen Dank</title>
</head>
<body>

    <?php include ("dbaccess.php"); ?>

    <h1>FileMaker-Fragebogen-System</h1>
    <hr />

    <?php
        
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
    
            if (        empty($respondent_data['Prefix']) 
                    ||  empty($respondent_data['First Name'])
                    ||  empty($respondent_data['Last Name'])
                    ||  empty($respondent_data['Email Address'])
                ) {
                
                //Wenn Daten fehlen, Meldung anzeigen.
                echo '<h3>Einige der Angaben fehlen. Bitte gehen Sie zurück und füllen Sie alle Felder aus.</h3>';
            
            } else {
                
                //Wenn die Benutzereingabe gültig ist, Vornamen, Nachnamen und E-Mail-Adresse in Layout Respondent hinzufügen.
                $newRequest =& $fm->newAddCommand('Respondent', $respondent_data);
                $result = $newRequest->execute();
                
                //Auf Fehler prüfen
                if (FileMaker::isError($result)) {
                    
                    echo "<p>Fehler: " . $result->getMessage() . "<p>";
                    exit;
                }
                
                //Erfolg/Danke-Meldung anzeigen
                echo '<p>Vielen Dank für Ihre Angaben.</p>';
            }
        }
    ?>

</body>
</html>

