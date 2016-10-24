<?php
    /**
    * Exemple FileMaker PHP
    *
    *
    * Copyright 2006, FileMaker, Inc.  Tous droits réservés.
    * REMARQUE : Toute utilisation de ce code est soumise aux termes de
    * la licence de FileMaker fourni avec le code. L'utilisation de ce code source
    * implique l'acceptation des termes et conditions de cette licence. Sauf
    * explicitement autorisé par la licence, aucun copyright, brevet ou
    * licence ou droit de propriété intellectuelle n'est accordé, explicitement ou
    * implicitement par FileMaker.
    *
    */
     
    //Script passerelle qui appelle FileMaker::getContainerData avec l'URL fournie.
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
