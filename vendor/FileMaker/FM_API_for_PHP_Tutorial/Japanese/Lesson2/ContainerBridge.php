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
     
    //これは、指定された URL で FileMaker::getContainerData を呼び出すブリッジスクリプトです。
    require_once("dbaccess.php");
    
	if (isset($_GET['path'])){
		$url = $_GET['path'];
		$url = substr($url, 0, strpos($url, "?"));
		$url = substr($url, strrpos($url, ".") + 1);
		if($url == "jpg"){
			header('Content-type: image/jpeg');
		}
		else if($url == "gif"){
			header('Content-type: image/gif');
		}
		else{
			header('Content-type: application/octet-stream');
		}
		echo $fm->getContainerData($_GET['path']);
	}
?>
