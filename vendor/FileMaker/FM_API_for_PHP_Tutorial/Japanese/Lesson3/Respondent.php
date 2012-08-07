<?php
    /**
    * FileMaker PHP の例
    *
    *
    * Copyright 2006, FileMaker, Inc.  All rights reserved.
    * このソースコードの使用については、コードに付随する FileMaker 
    * ソフトウェアライセンスの条件が適用されます。このソースコードを
    * 使用した場合、本ライセンスの条項や条件に同意したことになります。
    * ソフトウェアライセンスで明示的に許諾されていない限り、それ以外の
    * 著作権、特許権、または他の知的財産のライセンスまたは権利は、
    * 明示的であれ黙示的であれ、FileMaker から許諾されていません。
    *
    */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>回答者</title>
</head>
<body>
    <?php include ("dbaccess.php"); ?>
    <h1>FileMaker アンケートシステム</h1>
    <hr />
    <form action="handle_form.php"  method="post">
        <h2>情報を入力してください:</h2>
        <p>敬称*: 
        
        <?php
        
            /*
             * 'Respondent' レイアウトには ('prefixes という名前の) 値リストを使用する 
             * 'Prefix' というフィールドがあり、ユーザの応答を 4 つのオプション、
             * "Dr."、"Mr."、"Mrs."、および "Ms." に制限します。ここでは、
             * レイアウトからこのリストを取得し、値を HTML のラジオボタンの
             * セットとして動的に表示します。
             */
            
            //レイアウトを取得
            $layout =& $fm->getLayout('Respondent');
            
            //希望する値リストを取得 (この場合は 'prefixes')
            $values = $layout->getValueList('prefixes');
            
            foreach($values as $value) {
                //値リストからの各オプションをラジオボタンとして表示
                echo '<input type= "radio" name= "prefix" value= "'. $value .'">' . $value . ' ';
            }
        ?>
        
        </p>
        <p>姓*: <input name="last_name" type="text" size="20" /></p>
        <p>名*: <input name="first_name" type="text" size="20" /></p>
        <p>メールアドレス*: <input name="email" type="text" size="32" /> </p>
        <hr />
        <input type="hidden" name="respondent_exists" value= "true" >
        <input type="hidden" name="active_questionnaire_id" value="<?php echo $_POST['active_questionnaire_id']; ?>" >
        <input type="submit" name="submit" value= "送信" />
        <input type="reset" value="リセット" />
    </form>
</body>
</html>