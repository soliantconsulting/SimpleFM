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
    
    /**
     * Die Datei ist für Erstellung/Initialis. des FileMaker-Objekts verantwortlich.
     * Mit dem Objekt können Sie Daten in der Datenbank ändern. Schließen Sie hierzu 
     * die Datei in die PHP-Datei ein, die auf die FileMaker-Datenbank zugreifen muss.
     */
    
    //FileMaker PHP API einschließen
    require_once ('FileMaker.php');    
    
    //FileMaker-Objekt erstellen
    $fm = new FileMaker();    
    
    //FileMaker-Datenbank angeben
    $fm->setProperty('database', 'questionnaire');
    
    //Host angeben
    $fm->setProperty('hostspec', 'http://localhost'); //temporär auf einem lokalen Server bereitgestellt
    
    /**
     * Verwenden Sie für den Zugriff auf die Datenbank das Standard-Administratorkonto,
     * das kein Passwort besitzt. Authentifizierung ändern: Öffnen Sie die Datenbank in
     * FileMaker Pro und wählen Verwalten > Konten und Zugriffsrechte (Menü "Datei").
    */
    
    $fm->setProperty('username', 'web');
    $fm->setProperty('password', 'web');
    
    /**
     * Die Fkt. führt eine Suche im Layout 'Respondent' mit dem über $respondent_id
     * übergeb. Wert durch. Wenn vorh., wird 1. entspr. Datens. zurückg., sonst null.
     */

    function getRespondentRecordFromRespondentID($respondent_id) {
        global $fm;
        
        //Layout angeben
        $find = $fm->newFindCommand('Respondent');
        
        //Geben Sie das Feld und den Wert für den Abgleich an. In diesem Fall ist 'Respondent ID' das Feld
        // und $respondent_id der Wert.
        $find->addFindCriterion('Respondent ID', $respondent_id);
        
        //Suche durchführen
        $results = $find->execute();
        
        //Auf Fehler überprüfen
        if (!FileMaker::isError($results)) 
        {    
            //Keine Fehler, erstes entsprechendes Ergebnis zurückgeben
            $records = $results->getRecords();
            return $records[0];
        
        } else {
            //Fehler aufgetreten, null zurückgeben (d. h. keine Datensätze gefunden)
            return null;
        }
    }
?>
