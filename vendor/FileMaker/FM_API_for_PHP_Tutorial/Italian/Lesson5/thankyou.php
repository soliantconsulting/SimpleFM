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
    <title>Riepilogo risposte</title>
</head>
<body>
    <?php include ("dbaccess.php");?>

    <h1>Sistema questionario FileMaker</h1>
    <hr />

    <?php
        //memorizzare l'id del record dell'intervistato
        $respondent_recid = $_POST['respondent_id'];
    
        /*
         * Viene impostato 'store_information' se l'utente proviene da handle_form.php.
         * Se impostato, memorizzare la risposta dell'utente alla domanda finale (memorizzata in $_POST).
         */
         
        if (isset($_POST['store_information']))
        {
            //Memorizzare i dati della domanda
            $question_type = $_POST['question_type'];
            $cur_question = $_POST['cur_question'];
            
            /*
             * Tradurre la risposta in una stringa di diversi tipi di input 
             * modulo (testo, pulsanti di opzione, menu a discesa o caselle di controllo)
             */
            
            $translatedAnswer = "";
            if ($question_type == "text" ) {
                $translatedAnswer = $_POST['text_answer'];
            
            } else if ($question_type =="radio" || $question_type =="ranking") {
                //La classificazione e le opzioni sono gestite nello stesso modo.
                $translatedAnswer = $_POST['radio_answer'];
            
            }  else if ($question_type == "pulldown") {
                $translatedAnswer = $_POST['pulldown'];
            
            } else if($question_type == "checkbox") {
                if(is_array($_POST['cbanswer'])) {
                    $translatedAnswer = implode("\r", $_POST['cbanswer']);
                } else {
                    $translatedAnswer = $_POST['cbanswer'];    
                }
            }
            
            //ottenere il record dell'intervistato per ripristinare la risposta a questa domanda
            $respondent_rec = getRespondentRecordFromRespondentID($respondent_recid);
            
            //creare una nuova riga nel portale 'Responses' sul formato 'Respondent'
            $new_response = $respondent_rec->newRelatedRecord('Responses');
            
            //impostare id domanda e risposta nella nuova riga del portale
            $new_response->setField('Responses::Question ID', $cur_question);
            $new_response->setField('Responses::Response', $translatedAnswer);
            
            //salvare la modifica
            $result = $new_response->commit();
            
            //controllare se vi è un errore
            if (FileMaker::isError($result)) {
                echo "<p>Errore: " . $result->getMessage() . "<p>";
                exit;
            }
        }
    ?>
    
    <p>Grazie per aver completato il questionario.</p>

</body>

</html>