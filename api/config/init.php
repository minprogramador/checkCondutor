<?php

require('lib/functions.php');

header("Content-type:application/json");

$tokenOk = 'beb99570f3a3f81cf289a586e5abd89b';
$modulo  = 'condutor';

$token = getBearer();

if($token != $tokenOk) {
	$dados = ['msg' => 'acesso invalido'];
}

if(!checkUrl('condutor')) {
	$dados = ['msg' => 'acesso invalido'];
}

if(isset($dados)) {
	echo json_encode($dados);
	die;
}