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
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
    <?php include ("dbaccess.php");?>

    <div id="container">

        <h1>Système de questionnaire FileMaker</h1>
        <hr />
        <h2>Récapitulatif</h2>
        <hr />

        <?php
            //sauvegardez l'ID de l'enregistrement de la personne
            $respondent_recid = $_POST['respondent_id'];
    
            /*
             * 'store_information' sera appliquée si l'utilisateur vient de handle_form.php
             * If set, we need to store the user's response to the final question (stored in $_POST)
             * avant d'afficher le récapitulatif.
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
    
            //sauvegardez active_questionnaire_id
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
            
            //Effectuez une recherche sur 'questionnaire' pour le questionnaire actif
            $findCommand =& $fm->newFindCommand('questionnaire');
            $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
            $result = $findCommand->execute();
    
            //vérifiez les erreurs
            if (FileMaker::isError($result)) {
                echo "<p>Erreur : " . $result->getMessage() . "<p>";
                exit;
            }
    
            $records = $result->getRecords();
            $record = $records[0];
    
            
            //récupérez la rubrique 'Graphic' et affichez-la avec ContainerBridge.php
            echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
            
            //récupérez la rubrique 'Questionnaire Name' dans l'enregistrement et affichez-la
            echo '<p>Nom Questionnaire : ' . $record->getField('Questionnaire Name') . '</p>';
            
            //récupérez l'enregistrement de cet utilisateur
            $respondent_record = getRespondentRecordFromRespondentID($respondent_recid);
            
            //récupérez le préfixe, le prénom et le nom de la personne et affichez-les
            echo '<p>Nom : ' . $respondent_record->getField('Prefix') . ' ' . $respondent_record->getField('First Name') . ' ' . $respondent_record->getField('Last Name') . '</p>';
        ?>

        <p>Merci d'avoir complété à ce questionnaire. Voici un récapitulatif de vos réponses :</p> 
        <table>
            <tr> 
                <th>Questions</th>
                <th>Réponses</th>
            </tr>

            <?php
            
                /*
                 * Cette section permet de compléter les rangées, une par paire Question-Réponse.
                 */
                //récupérez la table externe 'Responses'
    
                $response_related_set = $respondent_record->getRelatedSet('Responses');
                //vérifiez les erreurs
                
                if (FileMaker::isError($response_related_set)) {
                    echo "<tr><td>Erreur : " . $response_related_set->getMessage() . "</td></tr>";
                    exit;
                }
                
                //récupérez et affichez chaque paire Question-Réponse dans une nouvelle colonne
                foreach ($response_related_set as $response_related_row) {
    
                    $question = $response_related_row->getField('Questions 2::question');
                    $answer = $response_related_row->getField('Responses::Response');
                    
                    //convertissez tous les retours à la ligne de la réponse en virgules
                    $answer = str_replace("\n",", ",$answer);
                    
                    echo '<tr><td>' . $question . '</td>';
                    echo '<td>' . $answer . '</td></tr>';
                }
            ?>
        </table>
    </div>
</body>
</html>