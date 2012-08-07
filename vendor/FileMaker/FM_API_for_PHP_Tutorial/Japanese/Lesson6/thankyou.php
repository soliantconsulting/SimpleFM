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
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
    <?php include ("dbaccess.php");?>

    <div id="container">

        <h1>FileMaker アンケートシステム</h1>
        <hr />
        <h2>概要</h2>
        <hr />

        <?php
            //回答者レコードのレコード ID を保存
            $respondent_recid = $_POST['respondent_id'];
    
            /*
             * ユーザが handle_form.php ページから来た場合には、'store_information' が設定されます。
             * If set, we need to store the user's response to the final question (stored in $_POST)
             * 概要を表示する前です。
             */
            
            if (isset($_POST['store_information'])) {
                
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
    
            //active_questionnaire_id を保存
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
            
            //'questionnaire' レイアウト上で、アクティブなアンケートを検索
            $findCommand =& $fm->newFindCommand('questionnaire');
            $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);
            $result = $findCommand->execute();
    
            //エラーをチェック
            if (FileMaker::isError($result)) {
                echo "<p>エラー:  " . $result->getMessage() . "<p>";
                exit;
            }
    
            $records = $result->getRecords();
            $record = $records[0];
    
            
            //ContainerBridge.php を使用して、レコードから 'Graphic' フィールドを取得し、表示
            echo '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Graphic')) . '">';
            
            //レコードから 'Questionnaire Name' フィールドを取得して表示
            echo '<p>アンケート名: ' . $record->getField('Questionnaire Name') . '</p>';
            
            //このユーザの回答者レコードを取得
            $respondent_record = getRespondentRecordFromRespondentID($respondent_recid);
            
            //回答者の敬称、名、姓を取得して表示
            echo '<p>名前: ' . $respondent_record->getField('Prefix') . ' ' . $respondent_record->getField('First Name') . ' ' . $respondent_record->getField('Last Name') . '</p>';
        ?>

        <p>アンケートにすべてお答えいただき、ありがとうございました。お答えの概要は以下の通りです:</p> 
        <table>
            <tr> 
                <th>質問</th>
                <th>回答</th>
            </tr>

            <?php
            
                /*
                 * このセクションは、それぞれが質問と答えのペアを表す、概要テーブルの行を書き込みます。
                 */
                //'Responses' ポータルを取得
    
                $response_related_set = $respondent_record->getRelatedSet('Responses');
                //エラーをチェック
                
                if (FileMaker::isError($response_related_set)) {
                    echo "<tr><td>エラー:  " . $response_related_set->getMessage() . "</td></tr>";
                    exit;
                }
                
                //それぞれの質問と答えのペアを取得して、新しいテーブルの行として表示
                foreach ($response_related_set as $response_related_row) {
    
                    $question = $response_related_row->getField('Questions 2::question');
                    $answer = $response_related_row->getField('Responses::Response');
                    
                    //回答内の改行をコンマに変換
                    $answer = str_replace("\n",", ",$answer);
                    
                    echo '<tr><td>' . $question . '</td>';
                    echo '<td>' . $answer . '</td></tr>';
                }
            ?>
        </table>
    </div>
</body>
</html>