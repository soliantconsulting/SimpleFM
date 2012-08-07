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
    <title>調査に対する回答の概要</title>
</head>
<body>
    <?php include ("dbaccess.php");?>

    <h1>FileMaker アンケートシステム</h1>
    <hr />

    <?php
        //回答者レコードのレコード ID を保存
        $respondent_recid = $_POST['respondent_id'];
    
        /*
         * ユーザが handle_form.php ページから来た場合には、'store_information' が設定されます。
         * 設定されている場合には、($_POST に保存されている) 最後の質問に対するユーザの応答を保存する必要があります。
         */
         
        if (isset($_POST['store_information']))
        {
            //質問データを保存
            $question_type = $_POST['question_type'];
            $cur_question = $_POST['cur_question'];
            
            /*
             * 応答を、様々な入力タイプ (テキスト、ラジオボタン、
             * プルダウンメニュー、またはチェックボックス) から文字列に翻訳
             */
            
            $translatedAnswer = "";
            if ($question_type == "text" ) {
                $translatedAnswer = $_POST['text_answer'];
            
            } else if ($question_type =="radio" || $question_type =="ranking") {
                //ランキングおよびラジオオプションは、同じ方法で処理されます。
                $translatedAnswer = $_POST['radio_answer'];
            
            }  else if ($question_type == "pulldown") {
                $translatedAnswer = $_POST['pulldown'];
            
            } else if($question_type == "checkbox") {
                if(is_array($_POST['cbanswer'])) {
                    $translatedAnswer = implode("\r", $_POST['cbanswer']);
                } else {
                    $translatedAnswer = $_POST['cbanswer'];    
                }
            }
            
            //この質問の応答を復元できるように、回答者レコードを取得
            $respondent_rec = getRespondentRecordFromRespondentID($respondent_recid);
            
            //'Respondent' レイアウトの 'Responses' ポータルに新しいポータル行を作成
            $new_response = $respondent_rec->newRelatedRecord('Responses');
            
            //新しいポータル行に質問 ID と回答を設定
            $new_response->setField('Responses::Question ID', $cur_question);
            $new_response->setField('Responses::Response', $translatedAnswer);
            
            //変更を確定
            $result = $new_response->commit();
            
            //エラーをチェック
            if (FileMaker::isError($result)) {
                echo "<p>エラー:  " . $result->getMessage() . "<p>";
                exit;
            }
        }
    ?>
    
    <p>アンケートにすべてお答えいただき、ありがとうございました。</p>

</body>

</html>