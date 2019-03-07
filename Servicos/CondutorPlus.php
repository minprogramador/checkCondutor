<?php

require_once('config.php');
require_once("checkAuth.php");

if(isset($_SESSION['TrocarSenha'])) { die('Acesso negado'); }
if(!isset($_SESSION['liberado'])){ die('Acesso negado'); }

$servicoNome = 'CondutorPlus';
$idUsuario = $util->xss($_SESSION['getId']);

$servico = new Sistema_Servicos();
$servico->setServico($servicoNome);

$control = new Sistema_Control();
$control->setServico($servicoNome);
$control->setIdUser($idUsuario);

if($servico->Permissao() != true) {
    $msg = file_get_contents('../tpls/outros/pagina_servicos_negado.html');
    $msg = str_replace('{{msg}}', $servicoNome, $msg);
    echo $msg;
    die;
} else {
	$control->setServico($servicoNome);
	$a = $control->getLimites();

    if($a['status'] != '1'){ $erroy = true; }
    if($a['usado'] >= $a['limite']){ $erroy = true; }
}

if(isset($erroy)) {
    $msg = file_get_contents('../tpls/outros/pagina_servicos_pontos.html');
    $msg = str_replace('{{msg}}', $servicoNome, $msg);
    echo $msg;
    die;
}
session_write_close();

require('libs/util.php');

define('URLAPI', 'http://191.96.139.176/condutor/');
define('TOKENAPI', 'beb99570f3a3f81cf289a586e5abd89b');

function consultar($doc) {
	$url   = URLAPI . $doc;
	$dados = curl($url, null, null, null, null, null, null, TOKENAPI);

	if(stristr($dados, '{')) {
		$dados = json_decode($dados, true);
		return $dados;
	}else{
		return false;
	}
} 

if(isset($_POST['dados'])) {
	header("Content-type:application/json");
	$doc = xss($_POST['dados']);
	if(!preg_match("#^([0-9]){3}([0-9]){3}([0-9]){3}([0-9]){2}$#i", $doc)) {
		$dados = ['msg' => 'doc_invalido'];
	}else{

		$dados = consultar($doc);
		if(!is_array($dados)) {
			$dados = ['msg' => 'indisponivel'];
		}else{
			$control->saveConsulta();
		}
	}
	echo json_encode($dados);
} else {
	$find  = '<p>Limite: <strong>xx</strong> - Usado: <strong>xx</strong></p>';
	$reple = "<p>Limite: <strong>{$a['limite']}</strong> - Usado: <strong>{$a['usado']}</strong></p>";
	$tpl   = file_get_contents('./tpls/CondutorPlus/index.html');
	$tpl   = str_replace( $find, $reple, $tpl );
	$tpl = str_replace(array("\t", "\n", "\r", "	", "  "), '', $tpl);
	echo $tpl;
}
