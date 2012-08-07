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
    <title>Personne interrogée</title>
</head>
<body>
<?php include ("dbaccess.php"); ?>
    <h1>Système de questionnaire FileMaker</h1>
    <hr />
    <form action="handle_form.php"  method="post">
        <h2>Saisissez vos informations :</h2>
        <p>Préfixe*: 
        <?php
            
            /*
             * Le modèle 'Respondent' contient la rubrique 'Prefix' qui utilise 
             * une liste de valeurs (appelée 'préfixes) qui permet de limiter la réponse
             * à 4 options : "Dr.", "M.", "Mme" et "Mlle". Ici, nous allons
             * récupérer cette liste depuis le modèle et afficher de façon dynamique les 
             * valeurs sous forme d'un ensemble de cercles d'option HTML.
             */
            
            //récupérez le modèle
            $layout =& $fm->getLayout('Respondent');
            
            //recherchez la liste de valeurs souhaitée (dans ce cas 'prefixes')
            $values = $layout->getValueList('prefixes');
            
            foreach($values as $value) {
                //affichez chaque option de la liste de valeurs sous forme de cercle d'option
                echo '<input type= "radio" name= "prefix" value= "'. $value .'">' . $value . ' ';
            }
         ?>
        </p>
        <p>Prénom*: <input name="first_name" type="text" size="20" /></p>
        <p>Nom*: <input name="last_name" type="text" size="20" /></p>
        <p>Adresse email*: <input name="email" type="text" size="32" /> </p>
        <hr />
        <input type="hidden" name="respondent_exists" value= "true" >
        <input type="hidden" name="active_questionnaire_id" value="<?php echo $_POST['active_questionnaire_id']; ?>" >
        <input type="hidden" name="Continue_to_Survey" value="true" /> 
        <input type="submit" name="submit" value= "Envoyer" />
        <input type="reset" value="Réinitialiser" />
    </form>
</body>
</html>