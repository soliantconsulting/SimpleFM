<?php
    /**
    * Esempio FileMaker PHP
    *
    *
    * Copyright 2006, FileMaker, Inc.  Tutti i diritti riservati.
    * NOTA: L'uso di questo codice sorgente è soggetto ai termini della
    * Licenza software FileMaker fornita con il codice. Utilizzando questo codice
    * sorgente l'utente dichiara di aver accettato i termini e le condizioni della
    * licenza. Salvo diversamente concesso espressamente nella Licenza software, 
    * FileMaker non concede altre licenze o diritti di copyright, brevetto o altri 
    * diritti di proprietà intellettuale, né espressi né impliciti.
    *
    */
     
    //È uno script ponte che richiama FileMaker::getContainerData con l'url fornito.
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
