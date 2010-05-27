<?php

class TestWikipediaUltrapediaMerger extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = false;

	private $url = "http://localhost/mediawiki/";
	private $user = "WikiSysop";
	private $pw = "root";
	
	function testWUMGeneral(){
		$et = $this->getEditToken();
		list($originalWPText, $newWPText, $currentUPText) = $this->getArticleContent();
		$this->writeArticles(array($originalWPText, $currentUPText, $newWPText), $et);
		
		$result = $this->getWikiArticleContent("Talk:Main_Page");
		
		//check whether all of our contributions have been merged somewoh
		$this->assertGreaterThan(0, strpos($result,'Results 1 ====='.WUM_TAG_CLOSE));
		$this->assertGreaterThan(0, strpos($result,'Results 2 ====='.WUM_TAG_CLOSE));
		$this->assertGreaterThan(0, strpos($result,'Results 3 ====='.WUM_TAG_CLOSE));
		$this->assertGreaterThan(0, strpos($result,'Results 4 ====='.WUM_TAG_CLOSE));
		
		
		//assert correct positions
		$result = str_replace("\n", "#", $result);
		$this->assertGreaterThan(0, 
			strpos($result,'====Diagrams====#<upc>===== Additional Query Results 4'));
		$this->assertGreaterThan(0, 
			strpos($result,'4 =====</upc>#====Summary====##<upc>===== Additional Query Results 1'));
		$this->assertGreaterThan(0, 
			strpos($result,'2b===#Some text#====Overview====#====Summary====##<upc>===== Additional Query Results 2'));
		$this->assertGreaterThan(0, 
			strpos($result,'esults 2 =====</upc>##<upc>===== Additional Query Results 3'));
	}
	
	function testWUMGeneralEmptyOriginalWP(){
		$et = $this->getEditToken();
		list($originalWPText, $newWPText, $currentUPText) = $this->getArticleContent();
		$this->writeArticles(array('##__WUM_Overwrite__', $currentUPText, $newWPText), $et);
		
		$result = $this->getWikiArticleContent("Talk:Main_Page");
		
		//check whether all of our contributions have been merged somewoh
		$this->assertGreaterThan(0, strpos($result,'Results 1 ====='.WUM_TAG_CLOSE));
		$this->assertGreaterThan(0, strpos($result,'Results 2 ====='.WUM_TAG_CLOSE));
		$this->assertGreaterThan(0, strpos($result,'Results 3 ====='.WUM_TAG_CLOSE));
		$this->assertGreaterThan(0, strpos($result,'Results 4 ====='.WUM_TAG_CLOSE));
		
		
		//assert correct positions
		$result = str_replace("\n", "#", $result);
		$this->assertGreaterThan(0, 
			strpos($result,'about.#<upc>===== Additional Query Results 1 =====</upc>#==Use Cases=='));
	}
	
	function testWUMGeneralEmptyCurrentUP(){
		$et = $this->getEditToken();
		list($originalWPText, $newWPText, $currentUPText) = $this->getArticleContent();
		$this->writeArticles(array($originalWPText, '__WUM_Overwrite__', $newWPText), $et);
		
		$result = $this->getWikiArticleContent("Talk:Main_Page");
		
		//check whether all of our contributions have been merged somewoh
		$this->assertEquals(false, strpos($result,'Results 1 ====='.WUM_TAG_CLOSE));
		$this->assertEquals(false, strpos($result,'Results 2 ====='.WUM_TAG_CLOSE));
		$this->assertEquals(false, strpos($result,'Results 3 ====='.WUM_TAG_CLOSE));
		$this->assertEquals(false, strpos($result,'Results 4 ====='.WUM_TAG_CLOSE));
	}

	function testWUMGeneralEmptyNewWP(){
		$et = $this->getEditToken();
		list($originalWPText, $newWPText, $currentUPText) = $this->getArticleContent();
		$this->writeArticles(array($originalWPText, $currentUPText, ''), $et);
		
		$result = $this->getWikiArticleContent("Talk:Main_Page");
		
		//check whether all of our contributions have been merged somewoh
		$this->assertGreaterThan(0, strpos($result,'Results 1 ====='.WUM_TAG_CLOSE));
		$this->assertGreaterThan(0, strpos($result,'Results 2 ====='.WUM_TAG_CLOSE));
		$this->assertGreaterThan(0, strpos($result,'Results 3 ====='.WUM_TAG_CLOSE));
		$this->assertGreaterThan(0, strpos($result,'Results 4 ====='.WUM_TAG_CLOSE));
		
		
		//assert correct positions
		$result = str_replace("\n", "#", $result);
		$this->assertGreaterThan(0, 
			strpos($result,'<upc>===== Additional Query Results 1 =====</upc>####<upc>'));
		$this->assertGreaterThan(0, 
			strpos($result,'3 =====</upc>##<upc>===== Additional Query Results 4 =====</upc>'));
	}
	
	function testTableBasedMerger(){
		return;
		
		$wumUseTableBasedMerger = true;
		
		$et = $this->getEditToken();
		list($currentUPText, $newWPText) = $this->getArticleContentForTBM();
		$this->writeArticles(array($currentUPText."__WUM_Overwrite__", $newWPText), $et);
		
		$result = $this->getWikiArticleContent("Talk:Main_Page");
		
		$this->assertGreaterThan(0, 
			strpos($result,'{{#tab'));
	}
	
	
	private function writeArticles($texts, $et){
		$cc = new cURL();
		
		foreach($texts as $text){
			$param = "action=edit&title=Talk:Main_Page&summary=Hello%20World&text=$text&token=$et";
			$editArticle = $cc->post($this->url."api.php", $param);
			//print_r($editArticle);
		}
	}
	
	private function getArticleContent(){
		$text = array();
		
		$cd = isWindows() ? "" : "./";
		
		$text[] = urlencode(file_get_contents($cd."testcases/wum-test-articles/owptext.txt"));
		$text[] = urlencode(file_get_contents($cd."testcases/wum-test-articles/nwptext.txt"));
		$text[] = urlencode(file_get_contents($cd."testcases/wum-test-articles/cuptext.txt"));
		
		return $text;
	}
	
	private function getArticleContentForTBM(){
		$text = array();
		
		$cd = isWindows() ? "" : "./";
		
		$text[] = urlencode(file_get_contents($cd."testcases/wum-test-articles/uptexttbm.txt"));
		$text[] = urlencode(file_get_contents($cd."testcases/wum-test-articles/wptexttbm.txt"));
		
		return $text;
	}
	
	private function getWikiArticleContent($title){
		$title = Title::newFromText($title);
		$article = Article::newFromID($title->getArticleID());
		return $article->getContent();
	}
	
	private function getEditToken(){
		$cc = new cURL();
		
		$loginXML = $cc->post($this->url."api.php","action=login&lgname=".$this->user."&lgpassword=".$this->pw."&format=xml");
		
		$editToken = $cc->post($this->url."api.php","action=query&prop=info|revisions&intoken=edit&titles=Main%20Page&format=xml");
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



