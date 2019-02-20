<?php

$tokenOk = 'beb99570f3a3f81cf289a586e5abd89b';

require('functions.php');

header("Content-type:application/json");

if(isset($_REQUEST['dados'])) {
	$doc = xss($_REQUEST['dados']);
	$doc = str_replace(['.', ',', '-', ' '], '', $doc);
	
	if(!preg_match("#^([0-9]){3}([0-9]){3}([0-9]){3}([0-9]){2}$#i", $doc)) {
		$error = ['msg' => 'doc_invalido'];
        echo json_encode($error);
        die;
	}
} else {
    $error = ['msg' => false];
    echo json_encode($error);
    die;
}

if($_REQUEST['token'] != $tokenOk) {
	$dados = ['msg' => 'acesso invalido'];
} else {
	$dados = consultar($doc);
}

echo json_encode($dados);
die;