<?php

require('config/init.php');

$doc = getDoc();

if($doc != false) {
	$dados = consultar($doc);
} else {
	$dados = ['msg' => 'doc_invalido'];
}

echo json_encode($dados);
