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
    <title>Grazie</title>
</head>
<body>

    <?php include ("dbaccess.php"); ?>

    <h1>Sistema questionario FileMaker</h1>
    <hr />

    <?php
        
        //Si imposta 'respondent_exists' se l'utente proviene dalla pagina Respondent.php
        if (isset($_POST['respondent_exists'])) {
           
           //Ricavare i dati inseriti dall'utente dai dati $_POST
            $respondent_data = array(
                'Prefix'            => $_POST['prefix'],
                'First Name'        => $_POST['first_name'],
                'Last Name'         => $_POST['last_name'],
                'questionnaire_id'  => $_POST['active_questionnaire_id'],
                'Email Address'     => $_POST['email']
            );
    
    
    
            //Convalidare i dati inseriti dall'utente. 
    
            if (        empty($respondent_data['Prefix']) 
                    ||  empty($respondent_data['First Name'])
                    ||  empty($respondent_data['Last Name'])
                    ||  empty($respondent_data['Email Address'])
                ) {
                
                //Se mancano dati, richiederli con un messaggio.
                echo '<h3>Mancano alcune informazioni. Ritornare indietro e completare tutti i campi.</h3>';
            
            } else {
                
                //Se i dati inseriti dall'utente sono validi, aggiungere il nome, il cognome e l'indirizzo e-mail nel formato Respondent
                $newRequest =& $fm->newAddCommand('Respondent', $respondent_data);
                $result = $newRequest->execute();
                
                //controllare se vi è un errore
                if (FileMaker::isError($result)) {
                    
                    echo "<p>Errore: " . $result->getMessage() . "<p>";
                    exit;
                }
                
                //Visualizzare un messaggio di completamento/ringraziamento
                echo '<p>Grazie per le informazioni.</p>';
            }
        }
    ?>

</body>
</html>

