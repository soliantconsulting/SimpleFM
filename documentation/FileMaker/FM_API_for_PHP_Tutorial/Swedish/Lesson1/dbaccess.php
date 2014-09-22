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
    
    /**
     * Den här filen är ansvarig för att skapa och initiera FileMaker-objektet.
     * Med detta objekt kan du manipulera data i databasen. Vill du göra det så 
     * inkluderar du helt enkelt den här filen i PHP-filen som behöver åtkomst till FileMaker-databasen.
     */
    
    //inkludera FileMaker PHP API
    require_once ('FileMaker.php');
    
    
    //skapa FileMaker-objektet
    $fm = new FileMaker();
    
    
    //Ange FileMaker-databasen
    $fm->setProperty('database', 'questionnaire');
    
    //Ange värden
    $fm->setProperty('hostspec', 'http://localhost'); //temporärt med en lokal server som värd
    
    /**
     * När du vill få åtkomst till databasen med frågeformulär använder du kontot för 
     * standardadminstratören vilket inte har något lösenord. Om du vill ändra inställningarna för verifiering öppnar du databasen i 
     * FileMaker Pro och markerar "Hantera > Konton och behörigheter" i menyn "Arkiv". 
    */
    
    $fm->setProperty('username', 'web');
    $fm->setProperty('password', 'web');

?>
