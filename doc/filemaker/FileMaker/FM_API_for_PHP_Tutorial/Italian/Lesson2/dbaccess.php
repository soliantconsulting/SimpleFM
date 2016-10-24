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
    
    /**
     * Questo file serve a creare e a inizializzare l'oggetto FileMaker.
     * Questo oggetto consente di manipolare i dati nel database. Per far ciò, basta 
     * includere questo file nel file PHP che deve accedere al database FileMaker.
     */

    //includere l'API PHP FileMaker
    require_once ('FileMaker.php');
    
    
    //creare l'oggetto FileMaker
    $fm = new FileMaker();
    
    
    //Specificare il database FileMaker
    $fm->setProperty('database', 'questionnaire');
    
    //Specificare l'host
    $fm->setProperty('hostspec', 'http://localhost'); //temporaneamente ospitato su server locale
    /**
     * Accedere al database del questionario con l'account amministratore predefinito,
     * senza password. Per cambiare i valori di autenticazione, aprire il database in 
     * FileMaker Pro e selezionare "Gestisci > Account e privilegi" dal menu "File".
    */
    
    $fm->setProperty('username', 'web');
    $fm->setProperty('password', 'web');

?>
