<?php

$url = 'http://localhost/mediawiki/';
$user = 'WikiSysop';
$user = 'root';
$title = 'WUMDemo';

$argv = array_flip($argv);

if(array_key_exists('init', $argv) || array_key_exists('i', $argv)){
	echo("\r\n\r\ninitializing article $title....\r\n");
	
	$et = getEditToken();

	$text = urlencode(file_get_contents('original-version.txt'));

	writeArticles($text, $et);

	echo("done\r\n\r\n");
} else {
	echo("\r\n\r\nupdating article $title....\r\n");
	
	$et = getEditToken();

	$text = urlencode(file_get_contents('new-version.txt'));

	writeArticles($text, $et);

	echo("done\r\n\r\n");
}



function getEditToken(){
		global $url, $user, $pw, $title;
	
		$cc = new cURL();
		
		$loginXML = $cc->post($url."api.php","action=login&lgname=".$user."&lgpassword=".$pw."&format=xml");
		
		$editToken = $cc->post($url."api.php","action=query&prop=info|revisions&intoken=edit&titles=Main%20Page&format=xml");
		$editToken = substr($editToken, strpos($editToken, "<?xml"));
		
		$domDocument = new DOMDocument();

		$cookies = array();
		$success = $domDocument->loadXML($editToken);
		$domXPath = new DOMXPath($domDocument);

		$nodes = $domXPath->query('//page/@edittoken');
		$et = "";
		foreach ($nodes AS $node) {
			$et = $node->nodeValue;
		}
		$et = urlencode($et);
		
		return $et;
}

function writeArticles($text, $et){
	global $url, $user, $pw, $title;
	
	$cc = new cURL();
		
	$param = "action=edit&title=$title&summary=Hello%20World&text=$text&token=$et";
	$editArticle = $cc->post($url."api.php", $param);
}

class cURL {
	var $headers;
	var $user_agent;
	var $compression;
	var $cookie_file;
	var $proxy;
	
	function cURL($cookies=TRUE,$cookie='cookies.txt',$compression='gzip',$proxy='') {
		$this->headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
		$this->headers[] = 'Connection: Keep-Alive';
		$this->headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
		$this->user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)';
		$this->compression=$compression;
		$this->proxy=$proxy;
		$this->cookies=$cookies;
		if ($this->cookies == TRUE) $this->cookie($cookie);
	}
	
	function cookie($cookie_file) {
		if (file_exists($cookie_file)) {
			$this->cookie_file=$cookie_file;
		} else {
			fopen($cookie_file,'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
			$this->cookie_file=$cookie_file;
			fclose($this->cookie_file);
		}
	}
	
	function get($url) {
		$process = curl_init($url);
		curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($process, CURLOPT_HEADER, 0);
		curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
		if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
		if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
		curl_setopt($process,CURLOPT_ENCODING , $this->compression);
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		if ($this->proxy) curl_setopt($cUrl, CURLOPT_PROXY, 'proxy_ip:proxy_port');
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
		$return = curl_exec($process);
		curl_close($process);
		return $return;
	}
	
	function post($url,$data) {
		$process = curl_init($url);
		curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($process, CURLOPT_HEADER, 1);
		curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
		if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
		if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
		//curl_setopt($process, CURLOPT_ENCODING , $this->compression);
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
		curl_setopt($process, CURLOPT_POSTFIELDS, $data);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($process, CURLOPT_POST, 1);
		$return = curl_exec($process);
		curl_close($process);
		return $return;
	}
	
	function error($error) {
		echo "<center><div style='width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px'><b>cURL Error</b><br>$error</div></center>";
		die;
	}
}

?>