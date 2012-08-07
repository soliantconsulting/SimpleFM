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
    <title>Intervistato</title>
</head>
<body>
    <?php include ("dbaccess.php"); ?>
    <h1>Sistema questionario FileMaker</h1>
    <hr />
    <form action="handle_form.php"  method="post">
        <h2>Inserire le informazioni:</h2>
        <p>Prefisso*: 
            <?php
            /*
             * Il formato 'Respondent' comprende un campo chiamato 'Prefix' che utilizza 
             * una lista valori (chiamata 'prefissi') per limitare la risposta dell'utente a
             * 4 opzioni: "Dott.", "Sig." e  "Sig.a". Qui stiamo per
             * recuperare questa lista dal formato e visualizzare dinamicamente i 
             * valori come set di pulsanti di opzione HTML.
             */
            //ottenere il formato
            $layout =& $fm->getLayout('Respondent');
            //ottenere la lista valori desiderata (in questo caso, 'prefixes')
            $values = $layout->getValueList('prefixes');
            foreach($values as $value)
            {
                //visualizzare ogni opzione della lista valori come pulsante di opzione
                echo '<input type= "radio" name= "prefix" value= "'. $value .'">' . $value . ' ';
            }
            ?>
        </p>
        <p>Nome*: <input name="first_name" type="text" size="20" /></p>
        <p>Cognome*: <input name="last_name" type="text" size="20" /></p>
        <p>Indirizzo e-mail*: <input name="email" type="text" size="32" /> </p>
        <hr />
        <input type="hidden" name="respondent_exists" value= "true" >
        <input type="hidden" name="active_questionnaire_id" value="<?php echo $_POST['active_questionnaire_id']; ?>" >
        <input type="submit" name="submit" value= "Invia" />
        <input type="reset" value="Reimposta" />
    </form>
</body>
</html>