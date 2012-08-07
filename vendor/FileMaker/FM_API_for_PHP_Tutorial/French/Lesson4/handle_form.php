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
        //'respondent_exists' s'affichera si l'utilisateur vient de la page Respondent.php
    
        if (isset($_POST['respondent_exists']))  {
            
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
    
            
            /*
             * A présent, nous devons définir la première question et l'afficher.
             */
    
            //Récupérez le questionnaire actif et son ID de l'enregistrement.
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
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
    
    
    
            //Récupérez la question et le type de question du premier enregistrement Question.    
            $records = $result->getRecords();
            $question = $records[0];    
            $real_question = $question->getField('question');    
            $question_type = $question->getField('Question Type');    
            $cur_question = $records[0]->getRecordID();
    
            //Imprimez la question.    
            echo "<p>".$real_question."</p>";    
            echo '<form action="thankyou.php" method= "POST">';            
    
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
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>Erreur : " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //imprimez la balise de début pour un menu déroulant HTML
                echo '<select name="pulldown">';
                
                //affichez chaque réponse possible sous forme d'option du menu déroulant HTML
                foreach ($relatedSet as $relatedRow)  {
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
            
            echo '<hr />';
            
            /*
             * Nous définissons les valeurs masquées insérées sur la page suivante via $_POST.
             * 
             *         'store_information' -- défini, indique à la page suivante d'enregistrer la réponse à CETTE question
             *         'question_type' -- format de réponse (texte, cercle, évaluation, menu déroulant ou case à cocher)
             *         'respondent_id' -- ID de l'enregistrement de la personne interrogée
             *         'cur_question' -- ID de l'enregistrement de la question en cours
             */
           
           echo '<input type="hidden" name="store_information" value="store_information"/>';
            echo '<input type="hidden" name="question_type" value="' . $question_type . '">';              
            echo '<input type="hidden" name="respondent_id" value="' . $respondent_recid . '"/>';
            echo '<input type="hidden" name="cur_question" value="' . $cur_question . '"/>';
            echo '<input type="Submit" name="submit" value="Envoyer" />';
            
            //imprimez la balise de fin du formulaire HTML
            echo '</form>';
        }
    ?>

</body>

</html>

