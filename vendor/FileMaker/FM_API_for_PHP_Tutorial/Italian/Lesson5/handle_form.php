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
    <title>Domande</title>
</head>

<body>

    <?php include ("dbaccess.php"); ?>

    <h1>Sistema questionario FileMaker</h1>
    <hr />

    <?php
        //Ricavare la variabile 'active_questionnaire_id' dai dati $_POST
    
        if(isset($_POST['active_questionnaire_id'])) {
    
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
        }
    
    
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
            if (    empty($respondent_data['Prefix']) 
                ||  empty($respondent_data['First Name'])
                ||  empty($respondent_data['Last Name'])
                ||  empty($respondent_data['Email Address'])) {
                
                //Se mancano dati, richiederli con un messaggio.
                echo '<h3>Mancano alcune informazioni. Ritornare indietro e completare tutti i campi.</h3>';
                exit;
            } else {
                
                //Se i dati inseriti dall'utente sono validi, aggiungere il nome, il cognome e l'indirizzo e-mail nel formato Respondent
                $newRequest =& $fm->newAddCommand('Respondent', $respondent_data);
                $result = $newRequest->execute();
                
                //controllare se vi è un errore
                if (FileMaker::isError($result)) {
                    echo "<p>Errore: " . $result->getMessage() . "<p>";
                    exit;
                }
                
                $records = $result->getRecords();
                $record = $records[0];
                $respondent_recid = $record->getField('Respondent ID');
            }
    
            //Impostare il numero domanda
            $question_number = 0;
        }
    
        
        /*
         * Viene impostato 'store_information' se l'utente proviene da handle_form.php.
         * Se impostato, si deve memorizzare la risposta dell'utente alla domanda prec. (memorizzata in $_POST).
         */
    
        if (isset($_POST['store_information'])) {
            
            //Memorizzare i dati della domanda
            $question_type = $_POST['question_type'];
            $respondent_recid = $_POST['respondent_id'];
            $last_question = $_POST['last_question'];
            $cur_question = $_POST['cur_question'];
            $question_number = $_POST['question_number'];
    
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
    
        
        /*
         * 'Continue_to_Survey' è impostato in due casi:
         *         - l'utente proviene da Respondent.php (non ha ancora risposto a NESSUNA domanda)
         *         - l'utente proviene da handle_form.php ed è rimasta ancora almeno una domanda.
         * Se è impostato, si deve creare il modulo HTML per la domanda relativa
         */
    
        if (isset ($_POST['Continue_to_Survey'])) {
    
            //Ottenere il questionario attivo e il relativo id record.
            $active_questionnaire = $fm->getRecordById('questionnaire',$active_questionnaire_id);

            //Eseguire una ricerca sul formato 'Questions' per cercare le domande relative a questo questionario
            $findCommand =& $fm->newFindCommand('Questions');
            $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
            $result = $findCommand->execute();
            
            //controllare se vi è un errore
            if (FileMaker::isError($result)) {
                echo "<p>Errore: " . $result->getMessage() . "<p>";
                exit;
            }
    
            $records = $result->getRecords();
            
            //Ottenere il numero di domande e memorizzare l'ultima per controlli futuri.
            $number_of_questions = count($records);
            $last_question = $records[$number_of_questions - 1]->getRecordID();
    
            //Ottenere la domanda e il tipo di domanda del record domanda corrente.
            $question = $records[$question_number];
            $real_question = $question->getField('question');
            $question_type = $question->getField('Question Type');
            $cur_question = $records[$question_number]->getRecordID();
    
            //Stampare una riga che indica la domanda in corso
            echo "<h4>Domanda " . ($question_number + 1) . " di " . $number_of_questions . ":</h4>";
    
            //Stampare la domanda.
            echo "<p>".$real_question."</p>";
    
            /*
             * Se NON è l'ultima domanda, l'invio del modulo riporta l'utente a questa pagina 
             * per la domanda successiva; altrimenti, inviarlo alla pagina di riepilogo finale
             */
    
            if ($cur_question != $last_question) {
                echo '<form action="handle_form.php" method= "POST">';
    
            } else {
                echo '<form action="thankyou.php" method= "POST">';
            }
            
    
            /*
             * Emettere l'elemento modulo HTML adeguato in base al valore di $question_type
             */
    
            if ($question_type == "text" ) {
                //visualizzare un'immissione di testo
                echo '<input type="text" name="text_answer" size="60" value=""/>';
            
            } else if ($question_type =="radio" || $question_type =="ranking") {
                
                /*
                 * Se question_type richiede pulsanti di opzione, è necessario ricavare una
                 * lista delle risposte accettabili a questa domanda.
                 * Nota: Le domande del tipo 'radio' e 'ranking' vengono implementate 
                 * in modo identico e ambedue usano pulsanti di opzione per inserire i dati utente.
                 */
    
                //Ottenere il portale 'question_answers'
                $relatedSet = $question->getRelatedSet('question_answers');
    
                //controllare se vi è un errore
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>Errore: " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //visualizzare ognuna delle risposte possibili come pulsanti di opzione HTML
                foreach ($relatedSet as $relatedRow) {
    
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<input type= "radio" name= "radio_answer" value= "'. $possible_answer .'">' . $possible_answer . '<br/>'; 
                }
            } else if ($question_type == "pulldown") {
    
                /*
                 * Se question_type richiede un menu a discesa, è necessario ricavare una
                 * lista delle risposte accettabili a questa domanda.
                 */
                 
                //Ottenere il portale 'question_answers'
                $relatedSet = $question->getRelatedSet('question_answers');
                
                //controllare se vi è un errore
                if (FileMaker::isError($relatedSet))  {
                    echo "<p>Errore: " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //stampare il tag di inizio di un menu a discesa HTML
                echo '<select name="pulldown">';
                
                //visualizzare ogni possibile risposta come opzione nel menu a discesa HTML
                foreach ($relatedSet as $relatedRow) {
                
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<option value="' . $possible_answer .'">' . $possible_answer . '</option>'; 
                }
                
                //stampare il tag di fine di un menu a discesa HTML
                echo '</select>';
            
            } else if($question_type == "checkbox") {
                
                /*
                 * Se question_type richiede caselle di controllo, è necessario ricavare una
                 * lista delle risposte accettabili a questa domanda.
                 */
                
                //Ottenere il portale 'question_answers'
                $relatedSet = $question->getRelatedSet('question_answers');
                
                //controllare se vi è un errore
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>Errore: " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //visualizzare ogni possibile risposta come casella di controllo HTML
                foreach ($relatedSet as $relatedRow) {
    
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<input type= "checkbox" name="cbanswer[]" value= "' . $possible_answer . '"/ >' . $possible_answer . '<br/>';
                }
            } else {
                //Se $question_type non è definito o riconosciuto, si immette un testo HTML
                echo '<input type="text" name="text_answer" size="60" value=""/>';
            }
            
            //incrementare il question_number
            $question_number++;
   
            echo '<hr />';
            
            /*
             * Qui impostiamo i valori nascosti da trasferire alla pagina successiva con $_POST.
             * 
             *         'store_information' -- sempre impostato, indica alla pag. succ. di salvare la risposta a QUESTA domanda
             *         'question_number' -- il numero della domanda successiva
             *         'question_type' -- la formattazione della risposta (testo, pulsanti di opzione, classificazione, menu a discesa o casella di controllo)
             *         'respondent_id' -- l'id del record dell'intervistato
             *         'cur_question' -- l'id del record della domanda corrente
             *         'last_question' -- l'id del record dell'ultima domanda di questo questionario
             *         'active_questionnaire_id' -- l'id del record del questionario corrente
             */
            
            echo '<input type="hidden" name="store_information" value="store_information"/>';
            echo '<input type="hidden" name="question_number" value="' . $question_number . '">';
            echo '<input type="hidden" name="question_type" value="' . $question_type . '">';              
            echo '<input type="hidden" name="respondent_id" value="' . $respondent_recid . '"/>';
            echo '<input type="hidden" name="cur_question" value="' . $cur_question . '"/>';
            echo '<input type="hidden" name="last_question" value="' . $last_question . '"/>';
            echo '<input type="hidden" name="active_questionnaire_id" value="' . $active_questionnaire_id . '"/>';
    
            
            /*
             * Il valore della variabile $handler_action indica la pagina del gestore (handle_form.php oppure finalSummary.php) 
             * indipendentemente dal fatto che questa sia o meno l'ultima domanda.
             */
            
            if ($cur_question != $last_question) {
                $handler_action = "Continue_to_Survey";
            
            } else {
                $handler_action="Questionnaire_Over";
            }
            
            echo '<input type="Submit" name="' . $handler_action . '" value="Invia" />';
            
            //stampare il tag di fine per il modulo HTML
            echo '</form>';
        }
    ?>

</body>
</html>

