<?php
    /**
    * Exemple FileMaker PHP
    *
    *
    * Copyright 2006, FileMaker, Inc.  Tous droits réservés.
    * REMARQUE : Toute utilisation de ce code est soumise aux termes de
    * la licence de FileMaker fourni avec le code. L'utilisation de ce code source
    * implique l'acceptation des termes et conditions de cette licence. Sauf
    * explicitement autorisé par la licence, aucun copyright, brevet ou
    * licence ou droit de propriété intellectuelle n'est accordé, explicitement ou
    * implicitement par FileMaker.
    *
    */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>

<head>
    <title>Questions</title>
</head>

<body>

    <?php include ("dbaccess.php"); ?>

    <h1>Système de questionnaire FileMaker</h1>
    <hr />

    <?php
        //Récupérez la variable 'active_questionnaire_id' dans les données $_POST
    
        if(isset($_POST['active_questionnaire_id'])) {
    
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
        }
    
    
        //'respondent_exists' s'affichera si l'utilisateur vient de la page Respondent.php
    
        if (isset($_POST['respondent_exists'])) {
            
            //Récupérez l'entrée de l'utilisateur depuis les données $_POST
            $respondent_data = array( 
                                        'Prefix'            => $_POST['prefix'],
                                        'First Name'        => $_POST['first_name'],
                                        'Last Name'         => $_POST['last_name'],
                                        'questionnaire_id'  => $_POST['active_questionnaire_id'],
                                        'Email Address'     => $_POST['email']
                                    );
    
            //Validez l'entrée de l'utilisateur. 
            if (    empty($respondent_data['Prefix']) 
                ||  empty($respondent_data['First Name'])
                ||  empty($respondent_data['Last Name'])
                ||  empty($respondent_data['Email Address'])) {
                
                //Si des données sont manquantes, demandez-les avec un message.
                echo '<h3>Certaines de vos informations sont manquantes. Retournez à la page concernée et complétez toutes les rubriques.</h3>';
                exit;
            } else {
                
                //Si l'entrée est valide, ajoutez nom, prénom et adresse email dans modèle Respondent
                $newRequest =& $fm->newAddCommand('Respondent', $respondent_data);
                $result = $newRequest->execute();
                
                //vérifiez les erreurs
                if (FileMaker::isError($result)) {
                    echo "<p>Erreur : " . $result->getMessage() . "<p>";
                    exit;
                }
                
                $records = $result->getRecords();
                $record = $records[0];
                $respondent_recid = $record->getField('Respondent ID');
            }
    
            //Définissez le numéro de la question
            $question_number = 0;
        }
    
        
        /*
         * 'store_information' sera appliquée si l'utilisateur vient de handle_form.php
         * Dans ce cas, conservez la réponse à la question précédente (stockée dans $_POST).
         */
    
        if (isset($_POST['store_information'])) {
            
            //Sauvegardez les données de question
            $question_type = $_POST['question_type'];
            $respondent_recid = $_POST['respondent_id'];
            $last_question = $_POST['last_question'];
            $cur_question = $_POST['cur_question'];
            $question_number = $_POST['question_number'];
    
            /*
             * Convertissez la réponse en une chaîne de divers 
             * types d'entrée (texte, cercles d'option, menus déroulants ou cases à cocher)
             */
    
            $translatedAnswer = "";
            if ($question_type == "text" ) {
                $translatedAnswer = $_POST['text_answer'];
            
            } else if ($question_type =="radio" || $question_type =="ranking") {
    
                //Les options d'évaluation et de cercle sont gérées de façon identique.
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
            
            //récupérez l'enregistrement de la personne pour restaurer la réponse
            $respondent_rec = getRespondentRecordFromRespondentID($respondent_recid);
            
            //créez une nouvelle rangée dans la table externe 'Responses' sur le modèle 'Respondent'
            $new_response = $respondent_rec->newRelatedRecord('Responses');
            
            //définissez l'ID de la question et la réponse dans la nouvelle rangée
            $new_response->setField('Responses::Question ID', $cur_question);
            $new_response->setField('Responses::Response', $translatedAnswer);
    
            //envoyez la modification
            $result = $new_response->commit();
            
            //vérifiez les erreurs
            if (FileMaker::isError($result)) {
    
                echo "<p>Erreur : " . $result->getMessage() . "<p>";
                exit;
            }
        }
    
        
        /*
         * 'Continue_to_Survey' est appliqué dans deux cas :
         *         - si l'utilisateur vient de Respondent.php (il n'a répondu à AUCUNE question)
         *         - si l'utilisateur vient de handle_form.php et qu'il reste au moins une question.
         * Dans ce cas, nous devons créer le formulaire HTML pour la question appropriée
         */
    
        if (isset ($_POST['Continue_to_Survey'])) {
    
            //Récupérez le questionnaire actif et son ID de l'enregistrement.
            $active_questionnaire = $fm->getRecordById('questionnaire',$active_questionnaire_id);

            //Recherchez dans le modèle 'Questions' les questions de ce questionnaire
            $findCommand =& $fm->newFindCommand('Questions');
            $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
            $result = $findCommand->execute();
            
            //vérifiez les erreurs
            if (FileMaker::isError($result)) {
                echo "<p>Erreur : " . $result->getMessage() . "<p>";
                exit;
            }
    
            $records = $result->getRecords();
            
            //Récupérez le numéro et conservez la dernière question pour vérification future.
            $number_of_questions = count($records);
            $last_question = $records[$number_of_questions - 1]->getRecordID();
    
            //Récupérez la question et son type dans l'enregistrement de la question en cours.
            $question = $records[$question_number];
            $real_question = $question->getField('question');
            $question_type = $question->getField('Question Type');
            $cur_question = $records[$question_number]->getRecordID();
    
            //Imprimez une ligne qui indique la question en cours
            echo "<h4>Question " . ($question_number + 1) . " sur " . $number_of_questions . ":</h4>";
    
            //Imprimez la question.
            echo "<p>".$real_question."</p>";
    
            /*
             * S'il ne s'agit PAS de la dernière question, l'envoi renvoie l'utilisateur à
             * cette page pour la question suivante. Dans le cas contraire, envoyez-le à la page de récapitulation
             */
    
            if ($cur_question != $last_question) {
                echo '<form action="handle_form.php" method= "POST">';
    
            } else {
                echo '<form action="thankyou.php" method= "POST">';
            }
            
    
            /*
             * Imprimez l'élément du formulaire HTML correspondant à la valeur $question_type
             */
    
            if ($question_type == "text" ) {
                //affichez une entrée de texte
                echo '<input type="text" name="text_answer" size="60" value=""/>';
            
            } else if ($question_type =="radio" || $question_type =="ranking") {
                
                /*
                 * Si question_type appelle des cercles d'options, nous devons récupérer
                 * la liste des réponses acceptables pour cette question.
                 * Remarque : Les questions de type 'radio' et 'ranking' sont définies de façon identique
                 * et les deux types utilisent les cercles d'option pour la saisie.
                 */
    
                //Récupérez la table externe 'question_answers'
                $relatedSet = $question->getRelatedSet('question_answers');
    
                //vérifiez les erreurs
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>Erreur : " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //affichez chacune des réponses possibles sous forme de cercle d'option HTML
                foreach ($relatedSet as $relatedRow) {
    
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<input type= "radio" name= "radio_answer" value= "'. $possible_answer .'">' . $possible_answer . '<br/>'; 
                }
            } else if ($question_type == "pulldown") {
    
                /*
                 * Si question_type appelle un menu déroulant, nous devons récupérer
                 * la liste des réponses acceptables pour cette question.
                 */
                 
                //Récupérez la table externe 'question_answers'
                $relatedSet = $question->getRelatedSet('question_answers');
                
                //vérifiez les erreurs
                if (FileMaker::isError($relatedSet))  {
                    echo "<p>Erreur : " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //imprimez la balise de début pour un menu déroulant HTML
                echo '<select name="pulldown">';
                
                //affichez chaque réponse possible sous forme d'option du menu déroulant HTML
                foreach ($relatedSet as $relatedRow) {
                
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<option value="' . $possible_answer .'">' . $possible_answer . '</option>'; 
                }
                
                //imprimez la balise de fin pour un menu déroulant HTML
                echo '</select>';
            
            } else if($question_type == "checkbox") {
                
                /*
                 * Si question_type appelle des cases à cocher, nous devons récupérer
                 * la liste des réponses acceptables pour cette question.
                 */
                
                //Récupérez la table externe 'question_answers'
                $relatedSet = $question->getRelatedSet('question_answers');
                
                //vérifiez les erreurs
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>Erreur : " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //affichez chaque réponse possible sous forme de case à cocher HTML
                foreach ($relatedSet as $relatedRow) {
    
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<input type= "checkbox" name="cbanswer[]" value= "' . $possible_answer . '"/ >' . $possible_answer . '<br/>';
                }
            } else {
                //Si $question_type est inconnu ou non reconnu, l'entrée par défaut est texte HTML
                echo '<input type="text" name="text_answer" size="60" value=""/>';
            }
            
            //incrémentez question_number
            $question_number++;
   
            echo '<hr />';
            
            /*
             * Nous définissons les valeurs masquées insérées sur la page suivante via $_POST.
             * 
             *         'store_information' -- défini, indique à la page suivante d'enregistrer la réponse à CETTE question
             *         'question_number' -- le numéro de la question suivante
             *         'question_type' -- format de réponse (texte, cercle, évaluation, menu déroulant ou case à cocher)
             *         'respondent_id' -- ID de l'enregistrement de la personne interrogée
             *         'cur_question' -- ID de l'enregistrement de la question en cours
             *         'last_question' -- ID de l'enregistrement de la dernière question de ce questionnaire
             *         'active_questionnaire_id' -- ID de l'enregistrement du questionnaire en cours
             */
            
            echo '<input type="hidden" name="store_information" value="store_information"/>';
            echo '<input type="hidden" name="question_number" value="' . $question_number . '">';
            echo '<input type="hidden" name="question_type" value="' . $question_type . '">';              
            echo '<input type="hidden" name="respondent_id" value="' . $respondent_recid . '"/>';
            echo '<input type="hidden" name="cur_question" value="' . $cur_question . '"/>';
            echo '<input type="hidden" name="last_question" value="' . $last_question . '"/>';
            echo '<input type="hidden" name="active_questionnaire_id" value="' . $active_questionnaire_id . '"/>';
    
            
            /*
             * La valeur $handler_action indique à la page handle_form.php ou finalSummary.php 
             * s'il s'agit ou non de la dernière question.
             */
            
            if ($cur_question != $last_question) {
                $handler_action = "Continue_to_Survey";
            
            } else {
                $handler_action="Questionnaire_Over";
            }
            
            echo '<input type="Submit" name="' . $handler_action . '" value="Envoyer" />';
            
            //imprimez la balise de fin du formulaire HTML
            echo '</form>';
        }
    ?>

</body>
</html>

