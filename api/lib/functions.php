<?php

function Mask($mask,$str) {
    $str = str_replace(" ","",$str);
    for($i=0;$i<strlen($str);$i++) {
        $mask[strpos($mask,"#")] = $str[$i];
    }
    return $mask;
}

function getBearer() {
    $token = xss(apache_request_headers()["Authorization"]);
    if(stristr($token, 'Bearer')) {
        $token = str_replace(['Bearer', ' ', '$', '{', '}', '/', "\/", '.', ',', '>', '<'], '', $token);
        if(checkMd5($token)) {
            return $token;
        }
    }
    return false;    
}


function checkMd5($md5 ='') {
  return strlen($md5) == 32 && ctype_xdigit($md5);
}

function getDoc() {
    $url = xss($_SERVER['REQUEST_URI']);
    $url = explode('/', $url);
    $doc = $url[2] ?? false;
    $doc = str_replace(['.', '-', ' ', '/'], '', $doc);
    if(isset($doc)) {
        if(!preg_match("#^([0-9]){3}([0-9]){3}([0-9]){3}([0-9]){2}$#i", $doc)) {
            return false;
        }else{
            return xss($doc);
        }
    }

    return false;
}

function checkUrl($modulo) {
    $url = xss($_SERVER['REQUEST_URI']);
    $url = explode('/', $url);
    $modulov = $url[1] ?? false;
    if($modulo != $modulov) {
        return false;
    }
    return true;
}

function xss($data, $problem='') {
    if($data == null OR $data == false) {
        return false;
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strip_tags($data);
    if ($problem && strlen($data) == 0){ return ($problem); }

    $ban = ['>', '<', '?'];
    $datan = str_replace($ban, '', $data);
    return $datan;
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
    $dir = __DIR__.'/../config/.token';    
    $fp = fopen($dir, "w");
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
    $dir = __DIR__.'/../config/.token';
    $token = file_get_contents($dir);

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
	$dir = __DIR__.'/../.cache';

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

        if(is_array($dadosCnh->Observacao)){
            $obsnovs = [];
            foreach($dadosCnh->Observacao as $nv) {
                $obsnovs[] = xss($nv);
            }
        }else{
            $obsnovs = [];
        }

        $dados = [];
        $dados['dados'] = [
            'cpf'  => Mask("###.###.###-##",xss($dadosCondutor->Cpf)),
            'nome' => xss($dadosCondutor->Nome) ?? '-',
            'nascimento' => xss($dadosCondutor->DataNascimento) ?? '-',
            'mae' => xss($dadosCondutor->Mae) ?? '-',
            'pai' => xss($dadosCondutor->Pai) ?? '-',
            'rg'  => xss($dadosCondutor->Rg) ?? '-',
            'rgOrgao' => xss($dadosCondutor->OrgaoExpeditor) ?? '-',
            'ufOrgao' => xss($dadosCondutor->UfExp) ?? '-',
            'renach'  => xss($dadosCnh->NumeroRenach) ?? '-',
            'registro'    => xss($dadosCnh->NumeroRegistro) ?? '-',
            'categoria'   => xss($dadosCnh->Categoria) ?? '-',
            'emissao'     => xss($dadosCnh->DataEmissao) ?? '-',
            'validade'    => xss($dadosCnh->Validade) ?? '-',
            'primeiraHab' => xss($dadosCnh->DataPrimeiraHabilitacao) ?? '-',
            'obscnh' => $obsnovs
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
