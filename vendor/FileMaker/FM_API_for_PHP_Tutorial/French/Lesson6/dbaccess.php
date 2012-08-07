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
    
    /**
     * Ce fichier permet de créer et d'initialiser l'objet FileMaker.
     * Il permet de manipuler les données de la base de données. Pour ce faire, il 
     * suffit d'inclure ce fichier dans le fichier PHP qui accède à la base de données FileMaker.
     */
    
    //d'inclure l'API FileMaker PHP
    require_once ('FileMaker.php');
    
    
    //de créer l'objet FileMaker
    $fm = new FileMaker();
    
    
    //Spécifier la base de données FileMaker
    $fm->setProperty('database', 'questionnaire');
    
    //Spécifier l'hôte
    $fm->setProperty('hostspec', 'http://localhost'); //hébergé temporairement sur le serveur local
    
    /**
     * Pour accéder au questionnaire, utilisez le compte administrateur par défaut sans
     * mot de passe. Pour modifier les paramètres d'authentif., ouvrez la base de données dans 
     * FileMaker Pro et sélectionnez "Gérer > Comptes et Privilèges" dans "Fichier". 
    */
    
    $fm->setProperty('username', 'web');
    $fm->setProperty('password', 'web');
    
    /**
     * La fonction effectue une recherche sur le modèle 'Respondent' avec valeur récup.
     * via $respondent_id. Si des enregistr. correspondent, le 1er s'affiche. Dans le cas contraire, rien ne s'affiche.
     */
    
    function getRespondentRecordFromRespondentID($respondent_id) {
        global $fm;
        
        //Spécifiez le modèle
        $find = $fm->newFindCommand('Respondent');
        
        //Indiquez la rubrique et la valeur à rechercher. Dans ce cas, 'Respondent ID' correspond à la rubrique
        // et $respondent_id représente la valeur.
        $find->addFindCriterion('Respondent ID', $respondent_id);
        
        //Exécutez la recherche
        $results = $find->execute();
        
        //Vérifiez les erreurs
        if (!FileMaker::isError($results)) {    
            
            //S'il n'y a pas d'erreur, affichez le premier résultat correspondant
            $records = $results->getRecords();
            return $records[0];
        
        } else {
            //En cas d'erreur, n'affichez aucun résultat (aucune valeur trouvée)
            return null;
        }
    }
?>
