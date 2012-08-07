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
    <title>Tack</title>
</head>
<body>

    <?php include ("dbaccess.php"); ?>

    <h1>FileMakers frågeformulärssystem</h1>
    <hr />

    <?php
        
        //'respondent_exists' kommer att ställas in om användaren kommer från sidan Respondent.php
        if (isset($_POST['respondent_exists'])) {
           
           //Ta tag i indata från användaren från data i $_POST 
            $respondent_data = array(
                'Prefix'            => $_POST['prefix'],
                'First Name'        => $_POST['first_name'],
                'Last Name'         => $_POST['last_name'],
                'questionnaire_id'  => $_POST['active_questionnaire_id'],
                'Email Address'     => $_POST['email']
            );
    
    
    
            //Validera indata från användaren. 
    
            if (        empty($respondent_data['Prefix']) 
                    ||  empty($respondent_data['First Name'])
                    ||  empty($respondent_data['Last Name'])
                    ||  empty($respondent_data['Email Address'])
                ) {
                
                //Om data saknas uppmana dem med ett meddelande.
                echo '<h3>Viss information saknas. Gå tillbaka och fyll i alla fält.</h3>';
            
            } else {
                
                //Om indata från användare inte är giltiga lägger du till förnamn, efternamn och e-postadress i layouten Respondent.
                $newRequest =& $fm->newAddCommand('Respondent', $respondent_data);
                $result = $newRequest->execute();
                
                //kontrollera om fel finns
                if (FileMaker::isError($result)) {
                    
                    echo "<p>Fel: " . $result->getMessage() . "<p>";
                    exit;
                }
                
                //Visa ett tack-meddelande
                echo '<p>Tack för din information.</p>';
            }
        }
    ?>

</body>
</html>

