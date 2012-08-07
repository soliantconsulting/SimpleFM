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
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
    <?php include ("dbaccess.php");?>

    <div id="container">

        <h1>Sistema questionario FileMaker</h1>
        <hr />
        <h2>Riassunto</h2>
        <hr />

        <?php
            //memorizzare l'id del record dell'intervistato
            $respondent_recid = $_POST['respondent_id'];
    
            /*
             * Viene impostato 'store_information' se l'utente proviene da handle_form.php.
             * If set, we need to store the user's response to the final question (stored in $_POST)
             * prima di visualizzare il riepilogo.
             */
            
            if (isset($_POST['store_information'])) {
                
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
                
                } else if ($question_type == "pulldown") {
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
    
            //memorizzare l'active_questionnaire_id
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
            
            //Eseguire una ricerca sul formato 'questionnaire' per il questionario attivo
            $findCommand =& $fm->newFindCommand('questionnaire');
            $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
            $result = $findCommand->execute();
    
            //controllare se vi è un errore
            if (FileMaker::isError($result)) {
                echo "<p>Errore: " . $result->getMessage() . "<p>";
                exit;
            }
    
            $records = $result->getRecords();
            $record = $records[0];
    
            
            //ottenere il campo 'Graphic' dal record e visualizzarlo con ContainerBridge.php
            echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
            
            //ottenere il campo 'Questionnaire Name' dal record e visualizzarlo
            echo '<p>Nome questionario: ' . $record->getField('Questionnaire Name') . '</p>';
            
            //ottenere il record Intervistato per questo utente
            $respondent_record = getRespondentRecordFromRespondentID($respondent_recid);
            
            //ottenere il prefisso dell'intervistato, il nome e il cognome e visualizzarli
            echo '<p>Nome: ' . $respondent_record->getField('Prefix') . ' ' . $respondent_record->getField('First Name') . ' ' . $respondent_record->getField('Last Name') . '</p>';
        ?>

        <p>Grazie per aver completato il questionario. Ecco un riepilogo delle risposte:"</p> 
        <table>
            <tr> 
                <th>Domande</th>
                <th>Risposte</th>
            </tr>

            <?php
            
                /*
                 * Questa sezione scrive le righe della tabella riassunto, e ciascuna è una coppia domanda-risposta.
                 */
                //ottenere il portale 'Responses'
    
                $response_related_set = $respondent_record->getRelatedSet('Responses');
                //controllare se vi è un errore
                
                if (FileMaker::isError($response_related_set)) {
                    echo "<tr><td>Errore: " . $response_related_set->getMessage() . "</td></tr>";
                    exit;
                }
                
                //ottenere e visualizzare ogni coppia domanda-risposta come una nuova riga di tabella
                foreach ($response_related_set as $response_related_row) {
    
                    $question = $response_related_row->getField('Questions 2::question');
                    $answer = $response_related_row->getField('Responses::Response');
                    
                    //converte tutti i ritorni a capo della risposta in virgole
                    $answer = str_replace("\n",", ",$answer);
                    
                    echo '<tr><td>' . $question . '</td>';
                    echo '<td>' . $answer . '</td></tr>';
                }
            ?>
        </table>
    </div>
</body>
</html>