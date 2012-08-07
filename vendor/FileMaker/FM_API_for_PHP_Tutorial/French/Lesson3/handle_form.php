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
    <title>Merci</title>
</head>
<body>

    <?php include ("dbaccess.php"); ?>

    <h1>Système de questionnaire FileMaker</h1>
    <hr />

    <?php
        
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
    
            if (        empty($respondent_data['Prefix']) 
                    ||  empty($respondent_data['First Name'])
                    ||  empty($respondent_data['Last Name'])
                    ||  empty($respondent_data['Email Address'])
                ) {
                
                //Si des données sont manquantes, demandez-les avec un message.
                echo '<h3>Certaines de vos informations sont manquantes. Retournez à la page concernée et complétez toutes les rubriques.</h3>';
            
            } else {
                
                //Si l'entrée est valide, ajoutez nom, prénom et adresse email dans modèle Respondent
                $newRequest =& $fm->newAddCommand('Respondent', $respondent_data);
                $result = $newRequest->execute();
                
                //vérifiez les erreurs
                if (FileMaker::isError($result)) {
                    
                    echo "<p>Erreur : " . $result->getMessage() . "<p>";
                    exit;
                }
                
                //Affichez un message de fin/remerciement
                echo '<p>Merci pour ces informations.</p>';
            }
        }
    ?>

</body>
</html>

