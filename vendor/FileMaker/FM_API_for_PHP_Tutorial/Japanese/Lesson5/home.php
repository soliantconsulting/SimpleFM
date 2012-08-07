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
    <title>アンケートチュートリアル</title>
</head>
<body>
    <?php include ("dbaccess.php"); ?>
    
    <h1>FileMaker アンケートシステム</h1>
    <h2>アンケートチュートリアルへようこそ。</h2>
    <hr />
    <?php 
        
        /**
         * データベースの 'Active Questionnaire' の ID が必要です。
         * アクティブなのは一度に 1 つのアンケートだけなので、これは単に 
         * 'find all' コマンドを使用してデータベースから取得することができます。
         */
        
        //'find all' コマンドを作成して、レイアウトを指定
        $findCommand =& $fm->newFindAllCommand('Active Questionnaire');
        
        //検索を実行して、結果を保存
        $result = $findCommand->execute();
        
        //エラーをチェック
        if (FileMaker::isError($result)) {
            echo "<p>エラー:  " . $result->getMessage() . "</p>";
            exit;
        }
        
        //一致するレコードを保存
        $records = $result->getRecords();
        
        //アクティブなアンケートの questionnaire_id を取得して保存
        $record = $records[0];
        $active_questionnaire_id =  $record->getField('questionnaire_id');
        
        /**
         * アクティブなアンケートを取得するには、$active_questionniare_id を使用し、
         * 'questionnaire' レイアウトで別の検索を実行します。
         */
        
        //find コマンドを作成して、レイアウトを指定
        $findCommand =& $fm->newFindCommand('questionnaire');
        
        //フィールドおよび一致する値を指定します。
        $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
        
        //検索の実行
        $result = $findCommand->execute();
        
        //エラーをチェック
        if (FileMaker::isError($result)) {
            echo "<p>エラー:  " . $result->getMessage() . "</p>";
            exit;
        }
        
        //一致するレコードを保存
        $records = $result->getRecords();
        $record = $records[0];
        
        //レコードから 'Questionnaire Name' フィールドを取得して表示
        echo "<p> " . $record->getField('Questionnaire Name') . "</p>"; 
        
        //レコードから 'Description' フィールドを取得して表示
        echo "<p>アンケートについての説明: "  . $record->getField('Description') . "</p>";
        
        //ContainerBridge.php を使用して、レコードから 'Graphic' フィールドを取得し、表示してください。        
        echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
    ?>
    <form id="questionnaire_form" name="Respondent" align= "right" method="post" action="Respondent.php">
        <input type="hidden" name="active_questionnaire_id" value = "<?php echo $active_questionnaire_id; ?>" >
        <hr /> 
        <input type="submit" name="Submit" value="続行" />
    </form>
</body>
</html>
