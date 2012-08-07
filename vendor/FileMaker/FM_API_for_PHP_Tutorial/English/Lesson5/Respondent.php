<?php
    /**
    * FileMaker PHP Example
    *
    *
    * Copyright 2006, FileMaker, Inc.  All rights reserved.
    * NOTE: Use of this source code is subject to the terms of the FileMaker
    * Software License which accompanies the code. Your use of this source code
    * signifies your agreement to such license terms and conditions. Except as
    * expressly granted in the Software License, no other copyright, patent, or
    * other intellectual property license or right is granted, either expressly or
    * by implication, by FileMaker.
    *
    */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>Respondent</title>
</head>
<body>
<?php include ("dbaccess.php"); ?>
    <h1>FileMaker Questionnaire System</h1>
    <hr />
    <form action="handle_form.php"  method="post">
        <h2>Enter your information:</h2>
        <p>Prefix*: 
        <?php
            
            /*
             * The 'Respondent' layout has a field named 'Prefix' which uses a 
             * value list (named 'prefixes) to limit the user's response to
             * 4 options: "Dr.", "Mr.", "Mrs.", and "Ms.". Here, we are going to
             * retrieve this list from the layout and dynamically display the 
             * values as a set of HTML radio buttons.
             */
            
            //get the layout
            $layout =& $fm->getLayout('Respondent');
            
            //get the desired value list (in this case, 'prefixes')
            $values = $layout->getValueList('prefixes');
            
            foreach($values as $value) {
                //display each option from the value list as a radio button
                echo '<input type= "radio" name= "prefix" value= "'. $value .'">' . $value . ' ';
            }
         ?>
        </p>
        <p>First Name*: <input name="first_name" type="text" size="20" /></p>
        <p>Last Name*: <input name="last_name" type="text" size="20" /></p>
        <p>Email Address*: <input name="email" type="text" size="32" /> </p>
        <hr />
        <input type="hidden" name="respondent_exists" value= "true" >
        <input type="hidden" name="active_questionnaire_id" value="<?php echo $_POST['active_questionnaire_id']; ?>" >
        <input type="hidden" name="Continue_to_Survey" value="true" /> 
        <input type="submit" name="submit" value= "Submit" />
        <input type="reset" value="Reset" />
    </form>
</body>
</html>