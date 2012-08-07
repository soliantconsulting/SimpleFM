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
    <title>Didacticiel Questionnaire</title>
</head>
<body>
    
    <?php include ("dbaccess.php"); ?>
    
    <h1>Système de questionnaire FileMaker</h1>
    <h2>Bienvenue dans le didacticiel Questionnaire.</h2>
    <hr />
    <?php 
        
        /**
         * L'ID du 'Active Questionnaire' dans la base de données est nécessaire. 
         * Dans la mesure où un seul questionnaire peut être actif à la fois, 
         * il peut être récupéré à l'aide d'une simple commande 'find all'.
         */
        
        //Créez la commande 'find all' et spécifiez le modèle
        $findCommand =& $fm->newFindAllCommand('Active Questionnaire');
        
        //Lancez la recherche et conservez le résultat
        $result = $findCommand->execute();
        
        //Vérifiez les erreurs
        if (FileMaker::isError($result)) {
            echo "<p>Erreur : " . $result->getMessage() . "</p>";
            exit;
        }
        
        //Conservez les enregistrements trouvés
        $records = $result->getRecords();
        
        //Récupérez et conservez la variable questionnaire_id du questionnaire actif
        $record = $records[0];
        $active_questionnaire_id =  $record->getField('questionnaire_id');
        
        /**
         * Pour récupérer le nom du questionnaire actif, vous pouvez effectuer une autre recherche
         * sur le modèle 'questionnaire' en utilisant $active_questionniare_id.
         */
        
        //créez la commande find et spécifiez le modèle
        $findCommand =& $fm->newFindCommand('questionnaire');
        
        //Indiquez la rubrique et la valeur à rechercher.
        $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
       
        //Exécutez la recherche
        $result = $findCommand->execute();
        
        //Vérifiez les erreurs
        if (FileMaker::isError($result)) {
            echo "<p>Erreur : " . $result->getMessage() . "</p>";
            exit;
        }
        //Conservez l'enregistrement trouvé
        $records = $result->getRecords();
        $record = $records[0];
        
        //Récupérez la rubrique 'Questionnaire Name' et affichez-la
        echo "<p>Merci " . $record->getField('Questionnaire Name') . "</p>"; 
        
        //Récupérez la rubrique 'Description' et affichez-la
        echo "<p>Description du Questionnaire : "  . $record->getField('Description') . "</p>";
        
        //Récupérez la rubrique 'Graphic' et affichez-la avec ContainerBridge.php        
        echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
    ?>
    <form id="questionnaire_form" name="Respondent" align= "right" method="post" action="Respondent.php">
        <input type="hidden" name="active_questionnaire_id" value = "<?php echo $active_questionnaire_id; ?>" >
        <hr /> 
        <input type="submit" name="Submit" value="Continuer" />
    </form>
</body>
</html>
