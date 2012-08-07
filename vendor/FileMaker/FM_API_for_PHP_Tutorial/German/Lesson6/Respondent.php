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
    <title>Antwortender</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
    <?php include ("dbaccess.php"); ?>
    
    <div id="container">
        <h1>FileMaker-Fragebogen-System</h1>
        <hr />
        <form action="handle_form.php"  method="post">
            <h2>Geben Sie Ihre Informationen an:</h2>
            <p>Präfix*: 
            <?php
                
                /*
                 * Layout 'Respondent' hat ein Feld 'Prefix', das eine
                 * Werteliste (Name 'präfixe') verwendet, um die Benutzerangaben auf 4 Optionen
                 * zu beschränken: "Dr.", "Hr.", "Frau." und "Prof.". Hier rufen wir jetzt
                 * diese Liste aus dem Layout ab und zeigen die Werte dynamisch
                 * als Set von HTML-Auswahlschaltflächen an.
                 */
                
                //Layout abrufen
                $layout =& $fm->getLayout('Respondent');
                
                //Gewünschte Werteliste abrufen (hier 'prefixes')
                $values = $layout->getValueList('prefixes');
               
               foreach($values as $value) {
                    //Jede Option aus der Werteliste als Auswahlschaltfläche anzeigen
                    echo '<input type= "radio" name= "prefix" value= "'. $value .'">' . $value . ' ';
                }
            ?>
            </p>
            <p>Vorname*: <input name="first_name" type="text" size="20" /></p>
            <p>Nachname*: <input name="last_name" type="text" size="20" /></p>
            <p>E-Mail-Adresse*: <input name="email" type="text" size="32" /> </p>
            <hr />
            <input type="hidden" name="respondent_exists" value= "true" >
            <input type="hidden" name="active_questionnaire_id" value="<?php echo $_POST['active_questionnaire_id']; ?>" >
            <input type="hidden" name="Continue_to_Survey" value="true" /> 
            <input type="submit" name="submit" value= "Senden" />
            <input type="reset" value="Zurücksetzen" />
        </form>
    </div>
</body>
</html>