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
    <title>ありがとうございます</title>
</head>
<body>

    <?php include ("dbaccess.php"); ?>

    <h1>FileMaker アンケートシステム</h1>
    <hr />

    <?php
        
        //ユーザが Respondent.php ページから来た場合には、'respondent_exists' が設定される
        if (isset($_POST['respondent_exists'])) {
           
           //$_POST データからユーザ入力を取得
            $respondent_data = array(
                'Prefix'            => $_POST['prefix'],
                'First Name'        => $_POST['first_name'],
                'Last Name'         => $_POST['last_name'],
                'questionnaire_id'  => $_POST['active_questionnaire_id'],
                'Email Address'     => $_POST['email']
            );
    
    
    
            //ユーザ入力を検証します。 
    
            if (        empty($respondent_data['Prefix']) 
                    ||  empty($respondent_data['First Name'])
                    ||  empty($respondent_data['Last Name'])
                    ||  empty($respondent_data['Email Address'])
                ) {
                
                //データがない場合には、メッセージで要求します。
                echo '<h3>一部の情報が不足しています。戻ってフィールドに入力してください。</h3>';
            
            } else {
                
                //ユーザ入力が有効な場合には、Respondent レイアウトに名前、姓および電子メールアドレスを追加
                $newRequest =& $fm->newAddCommand('Respondent', $respondent_data);
                $result = $newRequest->execute();
                
                //エラーをチェック
                if (FileMaker::isError($result)) {
                    
                    echo "<p>エラー:  " . $result->getMessage() . "<p>";
                    exit;
                }
                
                //成功/感謝のメッセージを表示
                echo '<p>情報を入力いただき、ありがとうございます。</p>';
            }
        }
    ?>

</body>
</html>

