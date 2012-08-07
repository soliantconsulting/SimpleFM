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
    
    /**
     * Den här funktionen utför en sökning i layouten 'Respondent' med hjälp av det värde som 
     * skickas via $respondent_id. Om det hittas, returneras den första matchande posten. Annars returneras noll. 
     */

    function getRespondentRecordFromRespondentID($respondent_id) {
        global $fm;
        
        //Ange layout
        $find = $fm->newFindCommand('Respondent');
        
        //Ange fält och värde för matchning. I det här fallet är fältet 'Respondent ID'
        // och värdet är $respondent_id.
        $find->addFindCriterion('Respondent ID', $respondent_id);
        
        //Utför sökningen
        $results = $find->execute();
        
        //Felkontroll
        if (!FileMaker::isError($results)) 
        {    
            //Inga fel, returnera första matchande resultat.
            $records = $results->getRecords();
            return $records[0];
        
        } else {
            //Det fanns fel, returnera noll (dvs inga matchningar hittades)
            return null;
        }
    }
?>
