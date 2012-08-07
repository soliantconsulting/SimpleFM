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
    <title>質問</title>
</head>
<body>

    <?php include ("dbaccess.php"); ?>

    <h1>FileMaker アンケートシステム</h1>
    <hr />
    <?php
        //ユーザが Respondent.php ページから来た場合には、'respondent_exists' が設定される
    
        if (isset($_POST['respondent_exists']))  {
            
            //$_POST データからユーザ入力を取得    
            $respondent_data = array(
                                        'Prefix'            => $_POST['prefix'],
                                        'First Name'        => $_POST['first_name'],
                                        'Last Name'         => $_POST['last_name'],
                                        'questionnaire_id'  => $_POST['active_questionnaire_id'],
                                        'Email Address'     => $_POST['email']
                                    );
    
            //ユーザ入力を検証します。
            if (    empty($respondent_data['Prefix']) 
                ||  empty($respondent_data['First Name'])
                ||  empty($respondent_data['Last Name'])
                ||  empty($respondent_data['Email Address'])) {
                
                //データがない場合には、メッセージで要求します。
                echo '<h3>一部の情報が不足しています。戻ってフィールドに入力してください。</h3>';
                exit;
    
            } else {
                
                //ユーザ入力が有効な場合には、Respondent レイアウトに名前、姓および電子メールアドレスを追加
                $newRequest =& $fm->newAddCommand('Respondent', $respondent_data);
                $result = $newRequest->execute();
                
                //エラーをチェック
                if (FileMaker::isError($result)) {
                    echo "<p>エラー:  " . $result->getMessage() . "<p>";
                    exit;
                }
                
                $records = $result->getRecords();
                $record = $records[0];
                $respondent_recid = $record->getField('Respondent ID');
            }
    
            
            /*
             * ここでは、アンケートの中の最初の質問を取得して、ユーザに表示する必要があります。
             */
    
            //アクティブなアンケート、およびそのレコード ID を取得します。
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
            $active_questionnaire = $fm->getRecordById('questionnaire',$active_questionnaire_id);    

            //'Questions' レイアウト上で、このアンケートに属する質問を検索    
            $findCommand =& $fm->newFindCommand('Questions');    
            $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);    
            $result = $findCommand->execute();
            
            //エラーをチェック
            if (FileMaker::isError($result)) {
                echo "<p>エラー:  " . $result->getMessage() . "<p>";
                exit;
            }
    
    
    
            //最初の質問レコードの質問および質問のタイプを取得します。    
            $records = $result->getRecords();
            $question = $records[0];    
            $real_question = $question->getField('question');    
            $question_type = $question->getField('Question Type');    
            $cur_question = $records[0]->getRecordID();
    
            //質問を出力します。    
            echo "<p>".$real_question."</p>";    
            echo '<form action="thankyou.php" method= "POST">';            
    
            /*
             * $question_type の値に基づいて、適切な HTML フォームエレメントを出力
             */
    
            if ($question_type == "text" ) {
    
                //テキスト入力を表示    
                echo '<input type="text" name="text_answer" size="60" value=""/>';
                
            } else if ($question_type =="radio" || $question_type =="ranking") {
                
                /*
                 * question_type がラジオボタンを呼び出す場合には、この質問に対する受け入れ可能な
                 * 応答のリストを取得する必要があります。
                 * 注意:'radio' および 'ranking' タイプの質問は同じ方法で実装されており、どちらも
                 * ユーザ入力のためにラジオボタンを使用します。
                 */
    
                //ポータル 'question_answers' を取得
    
                $relatedSet = $question->getRelatedSet('question_answers');
    
                //エラーをチェック
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>エラー:  " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //可能な答えのそれぞれを HTML ラジオボタンとして表示
                foreach ($relatedSet as $relatedRow) {
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<input type= "radio" name= "radio_answer" value= "'. $possible_answer .'">' . $possible_answer . '<br/>'; 
                }
    
            } else if ($question_type == "pulldown") {
    
                /*
                 * question_type がプルダウンメニューを呼び出す場合には、以下のものを取得する必要があります:
                 * 応答のリストを取得する必要があります。
                 */
                
                //ポータル 'question_answers' を取得
                $relatedSet = $question->getRelatedSet('question_answers');
                
                //エラーをチェック
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>エラー:  " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //HTML プルダウンメニューの開始タグを印刷
                echo '<select name="pulldown">';
                
                //可能な答えのそれぞれを HTML プルダウンメニューのオプションとして表示
                foreach ($relatedSet as $relatedRow)  {
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<option value="' . $possible_answer .'">' . $possible_answer . '</option>'; 
                 }
                
                //HTML プルダウンメニューの終了タグを出力
    
                echo '</select>';
    
            } else if($question_type == "checkbox") {
               
                /*
                 * question_type がチェックボックスを呼び出す場合には、以下のものを取得する必要があります:
                 * 応答のリストを取得する必要があります。
                 */
                
                //ポータル 'question_answers' を取得
                $relatedSet = $question->getRelatedSet('question_answers');
                
                //エラーをチェック
                
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>エラー:  " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //可能な答えのそれぞれを HTML チェックボックスとして表示
                foreach ($relatedSet as $relatedRow) {
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<input type= "checkbox" name="cbanswer[]" value= "' . $possible_answer . '"/ >' . $possible_answer . '<br/>';
                }
    
            } else {
                //$question_type が未定義または認識できない場合には、HTML テキスト入力をデフォルトとする
                echo '<input type="text" name="text_answer" size="60" value=""/>';
            }
            
            echo '<hr />';
            
            /*
             * ここでは、$_POST 経由で次のページに渡される、非表示のフォーム値を設定します。
             * 
             *         'store_information' -- 常に設定し、この質問に対する応答を保存するよう次のページに指示
             *         'question_type' -- 応答の形式 (テキスト、ラジオ、ランキング、プルダウン、またはチェックボックス)
             *         'respondent_id' -- 回答者レコードのレコード ID
             *         'cur_question' -- 現在の質問レコードのレコード ID
             */
           
           echo '<input type="hidden" name="store_information" value="store_information"/>';
            echo '<input type="hidden" name="question_type" value="' . $question_type . '">';              
            echo '<input type="hidden" name="respondent_id" value="' . $respondent_recid . '"/>';
            echo '<input type="hidden" name="cur_question" value="' . $cur_question . '"/>';
            echo '<input type="Submit" name="submit" value="送信" />';
            
            //HTML フォームの終了タグを出力
            echo '</form>';
        }
    ?>

</body>

</html>

