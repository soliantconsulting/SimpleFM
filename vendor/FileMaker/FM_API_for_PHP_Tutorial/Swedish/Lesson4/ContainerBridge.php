<?php
    /**
    * FileMaker PHP-exempel
    *
    *
    * Copyright 2006, FileMaker, Inc. Med ensamrätt.
    * OBS! Denna källkod får endast användas i enlighet med villkoren i FileMakers 
    * programvarulicens som följer med koden. Om du använder denna källkod
    * innebär det att du accepterar dessa licensvillkor. Utom för det
    * som uttryckligen medges i programvarulicensen görs inga övriga åtaganden från
    * FileMaker gällande copyright, patent eller annan intellektuell egendom, varken 
    * uttryckligen eller underförstått.
    *
    */
     
    //Detta är ett överbryggande manus som anropar FileMaker::getContainerData med angiven url.
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
