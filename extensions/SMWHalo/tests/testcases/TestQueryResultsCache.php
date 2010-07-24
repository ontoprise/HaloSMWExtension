<?php

global $smwgHaloIP;
require_once( "$smwgHaloIP/includes/QueryResultsCache/SMW_QRC_Store.php" );

class TestQueryResultsCache extends PHPUnit_Framework_TestCase {

	private $dataArticle1 = "[[HasValue::1]] [[Category:DataArticle]]";
	private $dataArticle2 = '[[HasValue::2]] [[Category:DataArticle]]';
	private $dataArticle3 = '[[HasValue::3]] [[Category:AnotherDataArticle]]';
	private $dataArticle4 = '[[Category:AnotherDataArticle]]';
	private $dataArticle5 = '[[Category:DataArticle]]';
	
	private $queryArticle1 = '{{#ask: [[HasValue::+]] [[Category:DataArticle]] }} {{#ask: [[HasValue::+]] [[Category:AnotherDataArticle]] }}';
	private $queryArticle1Version2 = '{{#ask: [[HasValue::+]] [[Category:DataArticle]] }}';
	
	private $queryArticle2 = '{{#sparql: SELECT ?x WHERE { ?x prop:HasValue ?y .  ?x rdf:type cat:DataArticle . } }} {{#sparql: SELECT ?x WHERE { ?x prop:HasValue ?y .  ?x rdf:type cat:AnotherDataArticle . } }}';
	private $queryArticle2Version2 = '{{#sparql: SELECT ?x WHERE { ?x prop:HasValue ?y .  ?x rdf:type cat:DataArticle . } }}';
	
	private $queryArticle3 = '{{#ask: [[HasValue::+]] }}';
	
	function setup(){
		$articles = array($this->dataArticle1, $this->dataArticle2, $this->dataArticle3);
		$count = 0;
		foreach($articles as $article){
			$count++;
			smwf_om_EditArticle('QRCDataArticle'.$count, 'PHPUnit', $article, '');
		}
		
		$articles = array($this->queryArticle1, $this->queryArticle2, $this->queryArticle3);
		$count = 0;
		global $wgTitle;
		foreach($articles as $article){
			$count++;
			$wgTitle = Title::newFromText('QRCQueryArticle'.$count);
			smwf_om_DeleteArticle('QRCQueryArticle'.$count, 'PHPUnit', '');
		}
		
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		$qrcStore = SMWQRCStore::getInstance()->getDB();
		foreach($response->queryIds as $qId){
			$qrcStore->deleteQueryData($qId);
		}
	}
	
	/*
	 * Test if the cache is empty, so that we can really start testing
	 */
	function testEmptyCacheBOTH(){
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		//first check whether the cache is empty
		$this->assertEquals(0, count($response->queryIds));
	}
	
	/*
	 * Adds five queries, uses the API to get the query Ids which have to
	 * be updated next and verifies that they are sorted due to their priority
	 */
	public function tdoNotTestGetQueryIdsByAPIOrder(){
		//fill the cache
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1, '');
		sleep(2);
		smwf_om_EditArticle('QRCQueryArticle2', 'PHPUnit', $this->queryArticle2, '');
		sleep(2);
		smwf_om_EditArticle('QRCQueryArticle3', 'PHPUnit', $this->queryArticle3, '');
		
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		$this->assertEquals(5, count($response->queryIds));
		
		$qrcStore = SMWQRCStore::getInstance()->getDB();
		$lastPriority = 0; 
		foreach($response->queryIds as $qId){
			$queryData = $qrcStore->getQueryData($qId);
			
			$this->assertEquals(true, $queryData['priority'] >= $lastPriority);
			
			$lastPriority = $queryData['priority'];
		}
	}
	
	/*
	 * Adds some results to the cache and verifies whether the
	 * results are stored correctly, respectively, whether they are
	 * deserializable
	 */
	function testCacheEntriesAddedASK(){
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1, '');
		
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		//check whether two new cache entries have been added
		$this->assertEquals(2, count($response->queryIds));
		
		$qrcStore = SMWQRCStore::getInstance()->getDB();
		$resultCount = 2; //todo: this is ugly
		foreach($response->queryIds as $qId){
			$queryData = $qrcStore->getQueryData($qId);
			$queryResult = unserialize($queryData['queryResult']);
			
			//check whether unserialize works like expected
			$unserializedCorrectly = ($queryResult instanceof SMWQueryResult) ? true : false;
			$this->assertEquals(true, $unserializedCorrectly);
			
			//check number of retrieved results
			$this->assertEquals($resultCount, count($queryResult->getResults()));
			$resultCount--;
		}
	}
	
	/*
	 * Adds some results to the cache and verifies whether the
	 * results are stored correctly, respectively, whether they are
	 * deserializable
	 */
	function testCacheEntriesAddedSPARQL(){
		smwf_om_EditArticle('QRCQueryArticle2', 'PHPUnit', $this->queryArticle1, '');
		
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		//check whether two new cache entries have been added
		$this->assertEquals(2, count($response->queryIds));
		
		$qrcStore = SMWQRCStore::getInstance()->getDB();
		$resultCount = 2; //todo: this is ugly
		foreach($response->queryIds as $qId){
			$queryData = $qrcStore->getQueryData($qId);
			$queryResult = unserialize($queryData['queryResult']);
			
			//check whether unserialize works like expected
			$unserializedCorrectly = ($queryResult instanceof SMWQueryResult) ? true : false;
			$this->assertEquals(true, $unserializedCorrectly);
			
			//check number of retrieved results
			$this->assertEquals($resultCount, count($queryResult->getResults()));
			$resultCount--;
		}
	}
	
	/*
	 * Checks whether cache entries are deleted if a query is updated via the API
	 * and if this query is not used anymore
	 */
	public function testDeleteCacheEntryBOTH(){
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1, '');
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1Version2, '');
		smwf_om_EditArticle('QRCQueryArticle2', 'PHPUnit', $this->queryArticle2, '');
		smwf_om_EditArticle('QRCQueryArticle2', 'PHPUnit', $this->queryArticle2Version2, '');
		
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		foreach($response->queryIds as $qId){
			$request = json_encode(array('debug' => true, 'queryId' => $qId));
			$response = smwf_qc_updateQuery($request);
			$response = json_decode($response);
			
			$this->assertEquals(true, $response->success);
		}
		
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		$this->assertEquals(2, count($response->queryIds));
	}
	
	/*
	 * Adds query, modifies the data and then checks whether the modified data
	 * is used
	 */
	public function testCacheEntryUsedASK(){
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1, '');
		
		smwf_om_DeleteArticle('QRCDataArticle1', 'PHPUnit', '');
		
		$article = Article::newFromID(Title::newFromText('QRCQueryArticle1')->getArticleID());
		$content = $article->getContent();
		
		global $wgParser;
		$pOpts = new ParserOptions();
		$result = $wgParser->parse($content, Title::newFromText('QRCQueryArticle1'), $pOpts)->getText();
		
		$cacheEntryUsed = false;
		if(strpos($result, 'QRCDataArticle1') > 0) $cacheEntryUsed = true;
		$this->assertEquals(true, $cacheEntryUsed);
	}
	
	/*
	 * Adds query, modifies the data and then checks whether the modified data
	 * is used if read action is performed
	 */
	public function testCacheEntryUsedSPARQL(){
		smwf_om_EditArticle('QRCQueryArticle2', 'PHPUnit', $this->queryArticle2, '');
		
			global $wgTitle;
			$wgTitle = Title::newFromText('QRCDataArticle1');
			smwf_om_EditArticle('QRCDataArticle1', 'PHPUnit','' , '');
		
		$article = Article::newFromID(Title::newFromText('QRCQueryArticle2')->getArticleID());
		$content = $article->getContent();
		
		global $wgParser;
		$pOpts = new ParserOptions();
		$result = $wgParser->parse($content, Title::newFromText('QRCQueryArticle1'), $pOpts)->getText();
		
		$cacheEntryUsed = false;
		if(strpos($result, 'QRCDataArticle1') > 0) $cacheEntryUsed = true;
		$this->assertEquals(true, $cacheEntryUsed);
	}
	
	public function testShowInvalidatedQueriesSwitch(){
		smwf_om_EditArticle('QRCQueryArticle2', 'PHPUnit', $this->queryArticle2, '');
		
		global $showInvalidatedCacheEntries;
		$showInvalidatedCacheEntries = false;
		
		global $wgTitle;
		$wgTitle = Title::newFromText('QRCDataArticle1');
		smwf_om_EditArticle('QRCDataArticle1', 'PHPUnit', ' ', '');
		
		$article = Article::newFromID(Title::newFromText('QRCQueryArticle2')->getArticleID());
		$content = $article->getContent();
		
		global $wgParser;
		$pOpts = new ParserOptions();
		$result = $wgParser->parse($content, Title::newFromText('QRCQueryArticle1'), $pOpts)->getText();
		
		$cacheEntryUsed = false;
		if(strpos($result, 'QRCDataArticle1') > 0) $cacheEntryUsed = true;
		
		$this->assertEquals(false, $cacheEntryUsed);
	}
	
	/*
	 * Adds query, modifies the data and then checks whether the modified data
	 * is used if purge action is performed
	 */
	public function testCacheEntryNotUsedASK(){
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1, '');
		
		global $wgTitle;
		$wgTitle = Title::newFromText('QRCDataArticle1');
		smwf_om_EditArticle('QRCDataArticle1', 'PHPUnit', ' ', '');
		
		$article = Article::newFromID(Title::newFromText('QRCQueryArticle1')->getArticleID());
		$content = $article->getContent();
		
		global $wgRequest;
		$wgRequest->setVal('action', 'purge');
		
		global $wgParser;
		$pOpts = new ParserOptions();
		$result = $wgParser->parse($content, Title::newFromText('QRCQueryArticle1'), $pOpts)->getText();
		
		$article = Article::newFromID(Title::newFromText(QRCQueryArticle1));
		
		$cacheEntryUsed = false;
		if(strpos($result, 'QRCDataArticle1') > 0) $cacheEntryUsed = true;
		$this->assertEquals(false, $cacheEntryUsed);
	}
	
	/*
	 * Adds query, modifies the data and then checks whether the modified data
	 * is used if purge action is performed
	 */
	public function testCacheEntryNotUsedSPARQL(){
		smwf_om_EditArticle('QRCQueryArticle2', 'PHPUnit', $this->queryArticle2, '');
		
		global $wgTitle;
		$wgTitle = Title::newFromText('QRCDataArticle1');
		smwf_om_EditArticle('QRCDataArticle1', 'PHPUnit', ' ', '');
		
		$article = Article::newFromID(Title::newFromText('QRCQueryArticle2')->getArticleID());
		$content = $article->getContent();
		
		global $wgRequest;
		$wgRequest->setVal('action', 'purge');
		
		global $wgParser;
		$pOpts = new ParserOptions();
		$result = $wgParser->parse($content, Title::newFromText('QRCQueryArticle2'), $pOpts)->getText();
		
		$article = Article::newFromID(Title::newFromText(QRCQueryArticle2));
		
		$cacheEntryUsed = false;
		if(strpos($result, 'QRCDataArticle1') > 0) $cacheEntryUsed = true;
		$this->assertEquals(false, $cacheEntryUsed);
	}
	
	/*
	 * Creates a query, modifies the test data, uses API update and checks whether 
	 * the updated result is displayed when performing read action.
	 */
	public function testArticleUpdatedByAPIBOTH(){
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1, '');
		
		global $wgTitle;
		$wgTitle = Title::newFromText('QRCDataArticle1');
		smwf_om_EditArticle('QRCDataArticle1', 'PHPUnit', ' ', '');
		
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		foreach($response->queryIds as $qId){
			$request = json_encode(array('debug' => true, 'queryId' => $qId));
			$response = smwf_qc_updateQuery($request);
			$response = json_decode($response);
		}

		global $wgOut; 
		$wgOut = new OutputPage();;
		
		$article = new Article(Title::newFromText('QRCQueryArticle1'));
		$article->view();
		
		$html = print_r($wgOut->getHTML(), true); 
		$found = false;
		if(strpos($html, 'QRCDataArticle1') > 0) $found = true;
		
		$this->assertEquals(false, $found);
	}
	
	public function testCacheEntryInvalidatedASK(){
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1Version2, '');
		
		global $wgTitle;
		//todo: delete action does not lead to query invalidation
		$wgTitle = Title::newFromText('QRCDataArticle1');
		smwf_om_DeleteArticle('QRCDataArticle1', 'PHPUnit', '');
		smwf_om_EditArticle('QRCDataArticle1', 'PHPUnit', $this->dataArticle1, '');
		
		$wgTitle = Title::newFromText('QRCDataArticle2');
		smwf_om_EditArticle('QRCDataArticle2', 'PHPUnit', $this->dataArticle1, '');
		smwf_om_EditArticle('QRCDataArticle2', 'PHPUnit', $this->dataArticle2, '');
		
		smwf_om_EditArticle('QRCDataArticle2', 'PHPUnit', $this->dataArticle4, '');
		smwf_om_EditArticle('QRCDataArticle2', 'PHPUnit', $this->dataArticle5, '');
		smwf_om_EditArticle('QRCDataArticle2', 'PHPUnit', $this->dataArticle5, '');
		
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		$qrcStore = SMWQRCStore::getInstance()->getDB();
		$qId = $response->queryIds[0];
		
		$queryData = $qrcStore->getQueryData($qId);
			
		$this->assertEquals(5, $queryData['invalidationFrequency']);
		$this->assertEquals('1', $queryData['dirty']);
		
		$request = json_encode(array('debug' => true, 'queryId' => $qId));
		$response = smwf_qc_updateQuery($request);
		$response = json_decode($response);
		
		$queryData = $qrcStore->getQueryData($qId);
			
		$this->assertEquals(3, $queryData['invalidationFrequency']);
		$this->assertEquals('0', $queryData['dirty']);
	
	}
//	
	public function testCacheEntryInvalidatedSPARQL(){
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle2Version2, '');
		
		global $wgTitle;
		//todo: delete action does not lead to query invalidation
		$wgTitle = Title::newFromText('QRCDataArticle1');
		smwf_om_DeleteArticle('QRCDataArticle1', 'PHPUnit', '');
		smwf_om_EditArticle('QRCDataArticle1', 'PHPUnit', $this->dataArticle1, '');
		
		$wgTitle = Title::newFromText('QRCDataArticle2');
		smwf_om_EditArticle('QRCDataArticle2', 'PHPUnit', $this->dataArticle1, '');
		smwf_om_EditArticle('QRCDataArticle2', 'PHPUnit', $this->dataArticle2, '');
		
		smwf_om_EditArticle('QRCDataArticle2', 'PHPUnit', $this->dataArticle4, '');
		smwf_om_EditArticle('QRCDataArticle2', 'PHPUnit', $this->dataArticle5, '');
		smwf_om_EditArticle('QRCDataArticle2', 'PHPUnit', $this->dataArticle5, '');
		
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		$qrcStore = SMWQRCStore::getInstance()->getDB();
		$qId = $response->queryIds[0];
		
		$queryData = $qrcStore->getQueryData($qId);
			
		$this->assertEquals(5, $queryData['invalidationFrequency']);
		$this->assertEquals('1', $queryData['dirty']);
		
		$request = json_encode(array('debug' => true, 'queryId' => $qId));
		$response = smwf_qc_updateQuery($request);
		$response = json_decode($response);
		
		$queryData = $qrcStore->getQueryData($qId);
			
		$this->assertEquals(3, $queryData['invalidationFrequency']);
		$this->assertEquals('0', $queryData['dirty']);
	
	}
	
	public function testAccessFrequencyBOTH(){
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1Version2, '');
		
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1Version2, '');
		
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1Version2, '');
		
		$article = Article::newFromID(Title::newFromText('QRCQueryArticle1')->getArticleID());
		$content = $article->getContent();
		
		global $wgParser;
		$pOpts = new ParserOptions();
		$result = $wgParser->parse($content, Title::newFromText('QRCQueryArticle1'), $pOpts)->getText();		
		
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		$qrcStore = SMWQRCStore::getInstance()->getDB();
		$qId = $response->queryIds[0];
		
		$queryData = $qrcStore->getQueryData($qId);

		$this->assertEquals(4, $queryData['accessFrequency']);
		$this->assertEquals('0', $queryData['dirty']);
		
		$request = json_encode(array('debug' => true, 'queryId' => $qId));
		$response = smwf_qc_updateQuery($request);
		$response = json_decode($response);
		
		$queryData = $qrcStore->getQueryData($qId);
			
		$this->assertEquals(2, $queryData['accessFrequency']);
		$this->assertEquals('0', $queryData['dirty']);
	}
	
	public function doNotTestPriorityComputation(){
		smwf_om_EditArticle('QRCQueryArticle1', 'PHPUnit', $this->queryArticle1Version2, '');
		
		$request = json_encode(array('debug' => true));
		$response = smwf_qc_getQueryIds($request);
		$response = json_decode($response);
		
		$qrcStore = SMWQRCStore::getInstance()->getDB();
		$qId = $response->queryIds[0];
		
		$queryData = $qrcStore->getQueryData($qId);
		
		$this->assertEquals($queryData['priority'], 
			SMWQRCPriorityCalculator::getInstance()->computeQueryUpdatePriority(
				$queryData['lastUpdate'], $queryData['accessFrequency'], $queryData['invalidationFrequency']));

		$rememberedPriority = $queryData['priority'];

		$article = Article::newFromID(Title::newFromText('QRCQueryArticle1')->getArticleID());
		$content = $article->getContent();
		
		global $wgParser;
		$pOpts = new ParserOptions();
		$result = $wgParser->parse($content, Title::newFromText('QRCQueryArticle1'), $pOpts)->getText();
		
		$queryData = $qrcStore->getQueryData($qId);

		$this->assertEquals($queryData['priority'], 
			SMWQRCPriorityCalculator::getInstance()->computeQueryUpdatePriority(
				$queryData['lastUpdate'], $queryData['accessFrequency'], $queryData['invalidationFrequency']));
		
		$this->assertNotEquals($rememberedPriority, $queryData['priority']);
				
		$rememberedPriority = $queryData['priority']; 		
				
		global $wgTitle;
		$wgTitle = Title::newFromText('QRCDataArticle1');
		smwf_om_EditArticle('QRCDataArticle1', 'PHPUnit', '', '');
		
		$queryData = $qrcStore->getQueryData($qId);
		
		$this->assertEquals($queryData['priority'], 
			SMWQRCPriorityCalculator::getInstance()->computeQueryUpdatePriority(
				$queryData['lastUpdate'], $queryData['accessFrequency'], $queryData['invalidationFrequency']));
				
		$this->assertNotEquals($rememberedPriority, $queryData['priority']);
		
		$request = json_encode(array('debug' => true, 'queryId' => $qId));
		$response = smwf_qc_updateQuery($request);
		$response = json_decode($response);
		
		$queryData = $qrcStore->getQueryData($qId);
		
		$this->assertEquals($queryData['priority'], 
			SMWQRCPriorityCalculator::getInstance()->computeQueryUpdatePriority(
				$queryData['lastUpdate'], $queryData['accessFrequency'], $queryData['invalidationFrequency']));
				
		$this->assertNotEquals($rememberedPriority, $queryData['priority']);
	}
}