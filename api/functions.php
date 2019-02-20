<?php

function xss($data, $problem='') {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strip_tags($data);
    if ($problem && strlen($data) == 0){ return ($problem); }
    return $data;
}

function curl($url, $payload=null, $tipo=null, $cookies=null, $header=true, $token=null) {

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

    if ($payload) {
        if($tipo == 'GET') {
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
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
    $user = 'gold';
    $pass = 'sssf3f23fasssdjkfljkxxxAxjsdfgyxklkwerfxKsFFFeX3vX';
	$url  = 'http://integracao.detran.savecred.com.br/token';
	$post = 'grant_type=password&username='.$user.'&password='. $pass;

    $dados = curl($url, $post, 'GET', null, null);
    if(stristr($dados, 'access_token')) {
        $dados  = json_decode($dados);
        $ntoken = $dados->access_token ?? null;
        if(isset($ntoken)) {
            return $ntoken;
        }
    }
    return false;
}

function saveToken($token) {
    $arquivo = ".token";
    $fp = fopen($arquivo, "w");
    fwrite($fp, $token);
    fclose($fp);
}

function saveLogs($doc, $dados) {
    $file = "{$doc}.json";
    $arquivo = ".cache/$file";
    $fp = fopen($arquivo, "w");
    fwrite($fp, $dados);
    fclose($fp);
}

function getToken() {
    $token = file_get_contents('.token');

    if(strlen($token) < 5) {
        $tnovo = geraToken();
        if(isset($tnovo) AND strlen($tnovo) > 10) {
            saveToken($tnovo);
            return $tnovo;
        } else {
            return false;
        }
    } else {
        return $token;
    }
}

function checkCache($doc) {
	$dir = '/var/www/html/condutor/.cache';
	$file = "{$dir}/$doc.json";
	if(file_exists($file)) {

		$file = file_get_contents($file);
		return $file;
	}
	return false;
}

function consultar($doc) {

	$dCache = checkCache($doc);

	if($dCache !== false) {
		$dados = $dCache;
		$dados = filtroConsulta($dados, 2);
		return $dados;
	} else {
		$url = 'http://integracao.detran.savecred.com.br/api/condutor/produto/60/cpf/' . $doc;
		$auth = getToken();

	    if(isset($auth) AND strlen($auth) > 10) {
	        $dados = curl($url, null, null, null, false, $auth);
	        if(stristr($dados, 'Authorization has been')) {
	        	saveToken('');
	        	return consultar($doc);
	        }

	        if(!stristr($dados, 'DadosCondutor')) {
	            return ['msg' => 'indisponivel'];
	        } else {
	        	$dados = filtroConsulta($dados, 1);
	            return $dados;
	        }
	    } else {
	        return ['msg' => 'indisponivel'];
	    }
	}
}

function filtroConsulta($dados, $tipo=1) {

    if(is_array($dados)) {
        return $dados;
    }

    $retorno = json_decode($dados);

    $dadosCondutor = $retorno->DadosCondutor;

    if(strlen($dadosCondutor->Cpf) < 10) {
        return ['msg' => 'nada_encontrado'];
    } else {
    	
    	if($tipo == 1) {
        	$doc = xss($dadosCondutor->Cpf);
        	saveLogs($doc, json_encode($retorno));
    	}

        $dadosCnh = $retorno->DadosCnh;
        $endereco = $retorno->Endereco;

        $dados = [];
        $dados['dados'] = [
            'cpf'  => $dadosCondutor->Cpf,
            'nome' => $dadosCondutor->Nome ?? '-',
            'nascimento' => $dadosCondutor->DataNascimento ?? '-',
            'mae' => $dadosCondutor->Mae ?? '-',
            'pai' => $dadosCondutor->pai ?? '-',
            'rg'  => $dadosCondutor->Rg ?? '-',
            'rgOrgao' => $dadosCondutor->OrgaoExpeditor ?? '-',
            'ufOrgao' => $dadosCondutor->UfExp ?? '-',
            'renach'  => $dadosCnh->NumeroRenach ?? '-',
            'registro'    => $dadosCnh->NumeroRegistro ?? '-',
            'categoria'   => $dadosCnh->Categoria ?? '-',
            'emissao'     => $dadosCnh->DataEmissao ?? '-',
            'validade'    => $dadosCnh->Validade ?? '-',
            'primeiraHab' => $dadosCnh->DataPrimeiraHabilitacao ?? '-',
        ];

        $dados['endereco'] = [
            'bairro' => $endereco->Bairro ?? '-',
            'cep' => $endereco->Cep ?? '-',
            'complemento' => $endereco->Complemento ?? '-',
            'logradouro' => $endereco->Logradouro ?? '-',
            'municipio' => $endereco->Municipio ?? '-',
            'numero' => $endereco->Numero ?? '-',
            'uf' => $endereco->Uf ?? '-'
        ];
        return $dados;
    }
}
