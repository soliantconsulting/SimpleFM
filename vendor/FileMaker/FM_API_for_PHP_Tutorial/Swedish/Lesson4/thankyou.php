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

    <title>Sammanfattning av frågeformulärssvar</title>
</head>
<body>
    <?php include ("dbaccess.php");?>

    <h1>FileMakers frågeformulärssystem</h1>
    <hr />

    <?php
        
        //lagra post-ID för posten för svarande
        $respondent_recid = $_POST['respondent_id'];
    
        /*
         * 'store_information' kommer att ställas in om användaren kommer från sidan handle_form.php.
         * Om den är inställd behöver vi lagra användarens svar på den sista frågan (lagrad i $_POST).
         */
        if (isset($_POST['store_information'])) {
            //Lagra frågedata
            $question_type = $_POST['question_type'];
            $cur_question = $_POST['cur_question'];
            
            /*
             * Översätt svaret till en sträng från de olika  
             * typerna av formulärindata (text, alternativknappar, rullgardinsmenyer eller kryssrutor)
             */
            $translatedAnswer = "";
            
            if ($question_type == "text" )  {
                $translatedAnswer = $_POST['text_answer'];
            
            } else if ($question_type =="radio" || $question_type =="ranking") {
                //Rangordning och alternativknappar hanteras på samma sätt.
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
            
            //hämta svarandens post så att vi kan återställa detta frågesvar
            $respondent_rec = getRespondentRecordFromRespondentID($respondent_recid);
            
            //skapa en ny portalrad i 'Responses'-portalen i layouten 'Respondent'
            $new_response = $respondent_rec->newRelatedRecord('Responses');
            
            //ställ in fråge-id och svar i den nya portalraden
            $new_response->setField('Responses::Question ID', $cur_question);
            $new_response->setField('Responses::Response', $translatedAnswer);
            
            //utför ändringen 
            $result = $new_response->commit();
            
            //kontrollera om fel finns
            if (FileMaker::isError($result)) {
                echo "<p>Fel: " . $result->getMessage() . "<p>";
                exit;
            }
        }

    ?>

    <p>Tack för att du har fyllt i frågeformuläret.</p>

</body>

</html>