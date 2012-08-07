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
        //$_POST データから 'active_questionnaire_id' 変数を取得
    
        if(isset($_POST['active_questionnaire_id'])) {
    
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
        }
    
    
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
    
            //質問番号を設定
            $question_number = 0;
        }
    
        
        /*
         * ユーザが handle_form.php ページから来た場合には、'store_information' が設定されます。
         * 設定されている場合には、($_POST に保存されている) 前の質問に対するユーザの応答を保存する必要があります。
         */
    
        if (isset($_POST['store_information'])) {
            
            //質問データを保存
            $question_type = $_POST['question_type'];
            $respondent_recid = $_POST['respondent_id'];
            $last_question = $_POST['last_question'];
            $cur_question = $_POST['cur_question'];
            $question_number = $_POST['question_number'];
    
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
            
            } else if ($question_type == "pulldown") {
    
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
    
        
        /*
         * 以下の 2 つの場合には、'Continue_to_Survey' が設定されます:
         *         - ユーザが Respondent.php から来た場合 (ユーザはどの質問にもまだ答えていない)
         *         - ユーザが handle_form.php から来ており、少なくとも 1 つの質問が残っている場合。
         * 設定されている場合には、適切な質問のための HTML フォームを作成する必要がある
         */
    
        if (isset ($_POST['Continue_to_Survey'])) {
    
            //アクティブなアンケート、およびそのレコード ID を取得します。
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
    
            $records = $result->getRecords();
            
            //質問の番号を取得し、将来のチェックのために最後の質問を保存します。
            $number_of_questions = count($records);
            $last_question = $records[$number_of_questions - 1]->getRecordID();
    
            //現在の質問レコードの質問および質問のタイプを取得します。
            $question = $records[$question_number];
            $real_question = $question->getField('question');
            $question_type = $question->getField('Question Type');
            $cur_question = $records[$question_number]->getRecordID();
    
            //現在どの質問であるかを示す行を出力
            echo "<h4>質問 " . ($question_number + 1) . " / " . $number_of_questions . ":</h4>";
    
            //質問を出力します。
            echo "<p>".$real_question."</p>";
    
            /*
             * これが最後の質問ではない場合には、フォームの送信によって、ユーザを、次の質問のために
             * このページに戻す必要があります。そうでなければ、ユーザを最後の概要ページに送ります。
             */
    
            if ($cur_question != $last_question) {
                echo '<form action="handle_form.php" method= "POST">';
    
            } else {
                echo '<form action="thankyou.php" method= "POST">';
            }
            
    
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
                if (FileMaker::isError($relatedSet))  {
                    echo "<p>エラー:  " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //HTML プルダウンメニューの開始タグを印刷
                echo '<select name="pulldown">';
                
                //可能な答えのそれぞれを HTML プルダウンメニューのオプションとして表示
                foreach ($relatedSet as $relatedRow) {
                
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
            
            //question_number を増やす
            $question_number++;
   
            echo '<hr />';
            
            /*
             * ここでは、$_POST 経由で次のページに渡される、非表示のフォーム値を設定します。
             * 
             *         'store_information' -- 常に設定し、この質問に対する応答を保存するよう次のページに指示
             *         'question_number' -- 次の質問の番号
             *         'question_type' -- 応答の形式 (テキスト、ラジオ、ランキング、プルダウン、またはチェックボックス)
             *         'respondent_id' -- 回答者レコードのレコード ID
             *         'cur_question' -- 現在の質問レコードのレコード ID
             *         'last_question' -- このアンケートの最後の質問レコードのレコード ID
             *         'active_questionnaire_id' -- 現在のアンケートのレコード ID
             */
            
            echo '<input type="hidden" name="store_information" value="store_information"/>';
            echo '<input type="hidden" name="question_number" value="' . $question_number . '">';
            echo '<input type="hidden" name="question_type" value="' . $question_type . '">';              
            echo '<input type="hidden" name="respondent_id" value="' . $respondent_recid . '"/>';
            echo '<input type="hidden" name="cur_question" value="' . $cur_question . '"/>';
            echo '<input type="hidden" name="last_question" value="' . $last_question . '"/>';
            echo '<input type="hidden" name="active_questionnaire_id" value="' . $active_questionnaire_id . '"/>';
    
            
            /*
             * $handler_action 変数の値は、ハンドラページ (handle_form.php または finalSummary.php) に、
             * これが最後の質問かどうかを知らせます。
             */
            
            if ($cur_question != $last_question) {
                $handler_action = "Continue_to_Survey";
            
            } else {
                $handler_action="Questionnaire_Over";
            }
            
            echo '<input type="Submit" name="' . $handler_action . '" value="送信" />';
            
            //HTML フォームの終了タグを出力
            echo '</form>';
        }
    ?>

</body>
</html>

