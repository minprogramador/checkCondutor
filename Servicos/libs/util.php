<?php

// function curl($url,$cookies,$post,$referer=null,$header=1,$follow=false) {
// 	$ch = curl_init();
// 	curl_setopt($ch, CURLOPT_URL, $url);
// 	curl_setopt($ch, CURLOPT_HEADER, $header);
// 	if ($cookies) curl_setopt($ch, CURLOPT_COOKIE, $cookies);
// 	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
// 	if(isset($referer)){ curl_setopt($ch, CURLOPT_REFERER,$referer); }
// 	else{ curl_setopt($ch, CURLOPT_REFERER,$url); }
// 	if(strlen($post) > 5)
// 	{
// 		curl_setopt($ch, CURLOPT_POST, 1);
// 		curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
// 	}
// 	curl_setopt($ch,  CURLOPT_SSL_VERIFYHOST, FALSE); 
// 	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
// 	curl_setopt($ch,  CURLOPT_TIMEOUT, 30); 
// 	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 30);

// 	$res = curl_exec( $ch);

// 	curl_close($ch); 
// 	return $res;
// }


function curl($url, $cook=null, $post=null, $header=null, $ref=null, $proxy=null, $timeout=null, $token=null) {
	$useragent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)';

	if(!isset($url)) {
		return false;
	}

	if($ref != null) {
		$ref = $url;
	}

	if($timeout != null) {
		$timeout = 15;
	}

	if($header != null) {
		$header = false;
	}

	$options = array(
		CURLOPT_URL 	  => $url,
		CURLOPT_HEADER    => $header,
		CURLOPT_USERAGENT => $useragent,
		CURLOPT_REFERER   => $ref,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_CONNECTTIMEOUT => $timeout,
		CURLOPT_TIMEOUT => $timeout
	);

	if(isset($cook)) {
		$options[CURLOPT_COOKIE] = $cook;
	}

	if(isset($post)) {
		$options[CURLOPT_POST] = true;
		$options[CURLOPT_POSTFIELDS] = $post;
	}

	if(isset($proxy)) {
		$options[CURLOPT_PROXY] = $proxy;
	}

	if(isset($token)) {
		$options[CURLOPT_HTTPHEADER] = array("Authorization: Bearer ".$token);
	}

	$ch = curl_init();
	curl_setopt_array($ch, $options);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}

function corta($str, $left, $right) {
	$str     = substr ( stristr ( $str, $left ), strlen ( $left ) );
    $leftLen = strlen ( stristr ( $str, $right ) );
    $leftLen = $leftLen ? - ($leftLen) : strlen ( $str );  
    $str     = substr ( $str, 0, $leftLen );
    return $str;
}

function getCookies($get) {
	preg_match_all('/Set-Cookie: (.*);/U',$get,$temp);
	$cookie  = $temp[1];
	$cookies = implode('; ',$cookie);
	return $cookies;
}

function xss($data, $problem='') {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	$data = strip_tags($data);
	if ($problem && strlen($data) == 0){ return ($problem); }
	return $data;
}

function clean_string($value) {
	return str_replace(array("\n",'  ','	'), '', $value);
}









