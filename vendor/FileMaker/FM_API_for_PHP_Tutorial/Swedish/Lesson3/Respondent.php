<?php
    /**
    * FileMaker PHP-exempel
    *
    *
    * Copyright 2006, FileMaker, Inc. Med ensamrätt.
    * OBS! Denna källkod får endast användas i enlighet med villkoren i FileMakers 
    * programvarulicens som följer med koden. Om du använder denna källkod
    * innebär det att du accepterar dessa licensvillkor. Utom för det
    * som uttryckligen medges i programvarulicensen görs inga övriga åtaganden från
    * FileMaker gällande copyright, patent eller annan intellektuell egendom, varken 
    * uttryckligen eller underförstått.
    *
    */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>Svarande</title>
</head>
<body>
    <?php include ("dbaccess.php"); ?>
    <h1>FileMakers frågeformulärssystem</h1>
    <hr />
    <form action="handle_form.php"  method="post">
        <h2>Ange din information:</h2>
        <p>Prefix*: 
        
        <?php
        
            /*
             * Layouten 'Respondent' har ett fält med namnet 'Prefix' som använder en 
             * värdelista (med namnet 'prefix) för att begränsa användarens svar på 
             * 4 alternativ: "Dr.", "Hr.", "Fru." och "Fr.". Här ska vi 
             * hämta listan från layouten och dynamiskt visa värden som en 
             * uppsättning HTML-alternativknappar.
             */
            
            //hämta layouten
            $layout =& $fm->getLayout('Respondent');
            
            //hämta önskad värdelista (i det här fallet , 'prefixes')
            $values = $layout->getValueList('prefixes');
            
            foreach($values as $value) {
                //visa varje alternativ i värdelistan som en alternativknapp
                echo '<input type= "radio" name= "prefix" value= "'. $value .'">' . $value . ' ';
            }
        ?>
        
        </p>
        <p>Förnamn*: <input name="first_name" type="text" size="20" /></p>
        <p>Efternamn*: <input name="last_name" type="text" size="20" /></p>
        <p>Prefix*: <input name="email" type="text" size="32" /> </p>
        <hr />
        <input type="hidden" name="respondent_exists" value= "true" >
        <input type="hidden" name="active_questionnaire_id" value="<?php echo $_POST['active_questionnaire_id']; ?>" >
        <input type="submit" name="submit" value= "Skicka" />
        <input type="reset" value="Återställ" />
    </form>
</body>
</html>