<?php
    /**
    * Esempio FileMaker PHP
    *
    *
    * Copyright 2006, FileMaker, Inc.  Tutti i diritti riservati.
    * NOTA: L'uso di questo codice sorgente è soggetto ai termini della
    * Licenza software FileMaker fornita con il codice. Utilizzando questo codice
    * sorgente l'utente dichiara di aver accettato i termini e le condizioni della
    * licenza. Salvo diversamente concesso espressamente nella Licenza software, 
    * FileMaker non concede altre licenze o diritti di copyright, brevetto o altri 
    * diritti di proprietà intellettuale, né espressi né impliciti.
    *
    */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>Esercitazioni sul questionario</title>
</head>
<body>
    
    <?php include ("dbaccess.php"); ?>
    
    <h1>Sistema questionario FileMaker</h1>
    <h2>Benvenuti alle Esercitazioni sul questionario.</h2>
    <hr />
    <?php 
        /**
         * È necessario l'ID del 'Active Questionnaire' nel database. 
         * Poiché può essere attivo un solo questionario per volta, 
         * è possibile recuperarlo dal database usando un semplice comando 'find all'.
         */
        
        //Creare il comando 'find all' e specificare il formato
        $findCommand =& $fm->newFindAllCommand('Active Questionnaire');
        
        //Eseguire la ricerca e memorizzare il risultato
        $result = $findCommand->execute();
        
        //Controllare se vi è un errore
        if (FileMaker::isError($result)) {
            echo "<p>Errore: " . $result->getMessage() . "</p>";
            exit;
        }
       
       //Memorizzare i record corrispondenti
        $records = $result->getRecords();
        
        //Recuperare e memorizzare il questionnaire_id del questionario attivo
        $record = $records[0];
        $active_questionnaire_id =  $record->getField('questionnaire_id');
        
        /**
         * Per ottenere il nome del questionario attivo, eseguire un'altra ricerca
         * sul formato 'questionnaire' usando $active_questionniare_id.
         */
        
        //creare il comando di find e specificare il formato
        $findCommand =& $fm->newFindCommand('questionnaire');
        
        //Specificare il campo e il valore da confrontare.
        $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
        
        //Eseguire la ricerca
        $result = $findCommand->execute();
        
        //Controllare se vi è un errore
        if (FileMaker::isError($result)) {
            echo "<p>Errore: " . $result->getMessage() . "</p>";
            exit;
        }
        
        //Memorizzare il record corrispondente
        $records = $result->getRecords();
        $record = $records[0];
        
        //Ottenere il campo 'Questionnaire Name' dal record e visualizzarlo
        echo "<p>Grazie per aver compilato " . $record->getField('Questionnaire Name') . "</p>"; 
        
        //Ottenere il campo 'Description' dal record e visualizzarlo
        echo "<p>Descrizione questionario: "  . $record->getField('Description') . "</p>";
        
        //Ottenere il campo 'Graphic' dal record e visualizzarlo con ContainerBridge.php        
        echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
    ?>
    <form id="questionnaire_form" name="Respondent" align= "right" method="post" action="Respondent.php">
        <input type="hidden" name="active_questionnaire_id" value = "<?php echo $active_questionnaire_id; ?>" >
        <hr /> 
        <input type="submit" name="Submit" value="Continua" />
    </form>
</body>
</html>
