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

?>
