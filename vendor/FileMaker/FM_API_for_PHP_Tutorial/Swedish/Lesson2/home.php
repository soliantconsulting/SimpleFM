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
    <title>Instruktioner för frågeformulär</title>
</head>
<body>
    <?php include ("dbaccess.php"); ?>
    
    <h1>FileMakers frågeformulärssystem</h1>
    <h2>Välkommen till instruktionerna för vårt frågeformulär.</h2>
    <hr />
    
    <?php
    
        /**
         * Vi behöver ID för 'Active Questionnaire' i databasen. 
         * Eftersom det endast kan finnas ett aktivt frågeformulär i taget 
         * kan detta hämtas från databasen med hjälp av kommandot 'find all'.
         */
        
        
        //Skapa kommandot 'sök alla' och ange layout
        $findCommand =& $fm->newFindAllCommand('Active Questionnaire');
        
        //Utför sökningen och lagra resultatet.
        $result = $findCommand->execute();
        
        //Kontrollera om fel finns
        if (FileMaker::isError($result)) {
            echo "<p>Fel: " . $result->getMessage() . "</p>";
            exit;
        }
        
        //Lagra matchande poster
        $records = $result->getRecords();
        
        //Hämta och lagra questionnaire_id för aktivt frågeformulär
        $record = $records[0];
        $active_questionnaire_id =  $record->getField('questionnaire_id');
        
        /**
         * Om du vill få namnet på aktivt frågeformulär kan vi utföra en annan sökning
         * i layouten 'questionnaire' med hjälp av $active_questionniare_id.
         */
        
        //skapar du Kommandat find och anger layout
        $findCommand =& $fm->newFindCommand('questionnaire');
        
        //Ange fält och värde för matchning.
        $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
       
       //Utför sökningen
        $result = $findCommand->execute();
        
        //Kontrollera om fel finns
        if (FileMaker::isError($result)) {
            echo "<p>Fel: " . $result->getMessage() . "</p>";
            exit;
        }
       
       //Lagra matchande post
        $records = $result->getRecords();
        $record = $records[0];
        
        //Hämta fältet 'Questionnaire Name' från posten och visa det
        echo "<p>Tack för att du  " . $record->getField('Questionnaire Name') . "</p>"; 
        
        //Hämta fältet 'Description' från posten och visa det
        echo "<p>Beskrivning av frågeformulär: "  . $record->getField('Description') . "</p>";
        
        //Hämta fältet 'Graphic' från posten och visa det med hjälp av ContainerBridge.php        
        echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
    ?>
    
</body>
</html>
