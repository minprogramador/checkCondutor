<?php

#mock condutor.

function curl($url, $post, $cookies, $header=true, $token=null) {
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $header);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_REFERER, $url); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

    if ($post) {
    	curl_setopt($ch, CURLOPT_POST, 1);curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    if (isset($cookies)) {
		curl_setopt($ch, CURLOPT_COOKIE, $cookies);
	}

    if(isset($token)) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, [ "Authorization: Bearer {$token}" ]);
	}

    $page = curl_exec($ch);
    curl_close($ch); 
    return $page;
}

function geraToken() {

	$url  = 'http://integracao.detran.savecred.com.br/token';
	$post = 'grant_type=password&username=gold&password=sssf3f23fasssdjkfljkxxxAxjsdfgyxklkwerfxKsFFFeX3vX';

}

function saveToken($token) {
	//salvar token em txt?
}

function getToken() {
	//return txt token...
}

function consultar($doc) {

	$url = 'http://integracao.detran.savecred.com.br/api/condutor/produto/60/cpf/' . $doc;
	$auth = getToken();

    $dados = file_get_contents('dados.json');
    return $dados;
	//set token em headers: = Authorization: bear..
}

function filtroConsulta($dados) {

    $retorno = json_decode($dados);

    $dadosCondutor = $retorno->DadosCondutor;

    if(strlen($dadosCondutor->Cpf) < 10) {
        return ['msg' => 'nada encontrado'];
    }
    $dadosCnh = $retorno->DadosCnh;
    $endereco = $retorno->Endereco;

    $dados = [];
    $dados['dados'] = [
        'cpf'  => $dadosCondutor->Cpf,
        'nome' => $dadosCondutor->Nome,
        'nascimento' => $dadosCondutor->DataNascimento,
        'mae' => $dadosCondutor->Mae,
        'pai' => $dadosCondutor->pai,
        'rg'  => $dadosCondutor->Rg,
        'rgOrgao' => $dadosCondutor->OrgaoExpeditor,
        'ufOrgao' => $dadosCondutor->UfExp,
        'renach'  => $dadosCnh->NumeroRenach,
        'registro'    => $dadosCnh->NumeroRegistro,
        'espelho'     => $dadosCnh->NumeroEspelho,
        'categoria'   => $dadosCnh->Categoria,
        'emissao'     => $dadosCnh->DataEmissao,
        'validade'    => $dadosCnh->Validade,
        'primeiraHab' => $dadosCnh->DataPrimeiraHabilitacao,
        'obs' => $dadosCnh->Observacao
    ];

    $dados['endereco'] = $endereco;
    return $dados;
}

header("Content-type:application/json");

if(isset($_POST['doc'])) {
	$doc = $_POST['doc'];
	if(!preg_match("#^([0-9]){3}([0-9]){3}([0-9]){3}([0-9]){2}$#i", $doc)) {
		$error = ['msg' => 'doc invalido.'];
        echo json_encode($error);
        die;
	}
} else {
    $error = ['msg' => false];
    echo json_encode($error);
    die;
}

$dados = consultar($doc);
$dados = filtroConsulta($dados);
//DadosCondutor
echo json_encode($dados);
die;





/*

    "DadosCondutor": {
        "Nome": "ADEMILSON DOS SANTOS COSTA",
        "Cpf": "08707559780",
        "Mae": "MARIA DE FATIMA F DOS SANTOS",
        "Pai": "ADECIO ARAUJO DA COSTA",
        "Uf": null,
        "DataNascimento": "22/08/1981",
        "Rg": "125698324 IFP/RJ",
        "OrgaoExpeditor": null,
        "UfExp": null
    },
    "DadosCnh": {
        "NumeroRenach": "RJ229465250",
        "NumeroRegistro": "4006123123",
        "NumeroEspelho": "1803252642",
        "Categoria": "D",
        "DataEmissao": "29/01/2019",
        "Validade": "28/01/2024",
        "DataPrimeiraHabilitacao": "26/12/2006",
        "Observacao": [
            "11 - Hab. em Curso Transporte Produtos Perigosos",
            "13 - Hab. em Curso Transporte Coletivo de Passageiro",
            "15 - Apto para Transporte Remunerado ao veículo"
        ]
    },
    "Endereco": {
        "Cep": "25231280",
        "Logradouro": "AVE REPUBLICA",
        "Complemento": "CASA",
        "Bairro": "CHACARAS RIO-PETROPOLIS",
        "Numero": "315",
        "Municipio": "DUQUE DE CAXIAS",
        "Uf": "RJ"
    },


*/
















