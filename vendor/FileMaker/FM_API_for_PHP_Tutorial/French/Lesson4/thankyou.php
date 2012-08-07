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

    <title>Récapitulatif des réponses à l'enquête</title>
</head>
<body>
    <?php include ("dbaccess.php");?>

    <h1>Système de questionnaire FileMaker</h1>
    <hr />

    <?php
        
        //sauvegardez l'ID de l'enregistrement de la personne
        $respondent_recid = $_POST['respondent_id'];
    
        /*
         * 'store_information' sera appliquée si l'utilisateur vient de handle_form.php
         * Dans ce cas, conservez la réponse à la dernière question (stockée dans $_POST).
         */
        if (isset($_POST['store_information'])) {
            //Sauvegardez les données de question
            $question_type = $_POST['question_type'];
            $cur_question = $_POST['cur_question'];
            
            /*
             * Convertissez la réponse en une chaîne de divers 
             * types d'entrée (texte, cercles d'option, menus déroulants ou cases à cocher)
             */
            $translatedAnswer = "";
            
            if ($question_type == "text" )  {
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

    ?>

    <p>Merci d'avoir complété ce questionnaire.</p>

</body>

</html>