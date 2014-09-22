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
    
    /**
     * このファイルは、FileMaker オブジェクトの作成および初期化を行います。
     * このオブジェクトを使えば、データベースのデータを操作することができます。そのためには、 
     * このファイルを、FileMaker データベースにアクセスする必要がある PHP ファイルに含めてください。
     */

    //FileMaker PHP API を含める
    require_once ('FileMaker.php');
    
    
    //FileMaker オブジェクトの作成
    $fm = new FileMaker();
    
    
    //FileMaker データベースの指定
    $fm->setProperty('database', 'questionnaire');
    
    //ホストの指定
    $fm->setProperty('hostspec', 'http://localhost'); //ローカルサーバーで一時的にホスト
    
    /**
     * アンケートデータベースにアクセスするには、デフォルトの管理者アカウントを使用します。
     * これにはパスワードはありません。認証設定を変更するには、FileMaker Pro でデータベースを開いて、
     * [ファイル] メニューの [管理] サブメニューから [アカウントとアクセス権] を選択します。
    */
    
    $fm->setProperty('username', 'web');
    $fm->setProperty('password', 'web');
    
    /**
     * この関数は、$respondent_id 経由で渡された値を使用して、'Respondent' レイアウト上で検索を実行します。
     * 見つかった場合には、最初に一致したレコードを返します。その他の場合は、null を返します。
     */
    
    function getRespondentRecordFromRespondentID($respondent_id) {
        global $fm;
        
        //レイアウトを指定
        $find = $fm->newFindCommand('Respondent');
        
        //フィールドおよび一致する値を指定します。 この場合は、'Respondent ID' がフィールドで、
        // $respondent_id が値です。
        $find->addFindCriterion('Respondent ID', $respondent_id);
        
        //検索の実行
        $results = $find->execute();
        
        //エラーをチェック
        if (!FileMaker::isError($results)) {#
            //エラーなし、最初に一致した結果を返す
            $records = $results->getRecords();
            return $records[0];
        } else {
            //エラーがあった。null を返す (つまり、一致は見つからなかった)
            return null;
        }
    }

?>
