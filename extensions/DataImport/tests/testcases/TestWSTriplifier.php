<?php

require_once 'Util.php';
require_once 'DI_Utils.php';

global $IP;
require_once($IP."/extensions/SMWHalo/includes/storage/SMW_TSConnection.php");
require_once($IP."/extensions/DataImport/specials/WebServices/SMW_WSTriplifier.php");
require_once($IP."/extensions/LinkedData/storage/TripleStore/LOD_TripleStoreAccess.php");

class TestWSTriplifier extends PHPUnit_Framework_TestCase {
	
	private $companySubjectHTML='href="/mediawiki/index.php?title=Company&amp;action=edit';
	private $otherCompanySubjectHTML='href="/mediawiki/index.php?title=OtherCompany&amp;action=edit';
	private $subjectColumnTitle="<th>SubjectColumn</th><th>HasAbstract</th>";
	private $subjectColumnTitleDefault="<th>Triple subjects</th><th>HasAbstract</th>";
	private $missingSubjectCreationPatternNote = "Triplifying the web service result is not possible.";
	
	private $wikiNS;
	
	function setUp(){
		$titles = array('TestTriplification', 'TestTriplification2', 'TestTriplification3', 'TestTriplification4');
		di_utils_setupWebServices($titles);
	}
	
	function tearDown() {
		di_utils_truncateWSTables();
	}
	
	/*
	 * Tests if the Data Source Information Graph is updated
	 * correctly if one creates a WWSD
	 */
	function testWWSDCreation(){
		$client = TSConnection::getConnector();
		$client->connect();
		
		$prefixes = LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes();
		$graphsURI = LODAdministrationStore::getInstance()->getSMWGraphsURI(); 
		
		$query = $prefixes.' SELECT ?s ?o WHERE { GRAPH <'.$graphsURI.'DataSourceInformationGraph> {?s smw-lde:label ?o . } } LIMIT 100'; 
		$response = $client->query($query, 'merge=false', LODAdministrationStore::getInstance()->getSMWGraphsURI()."DataSourceInformationGraph");
		
		$this->assertGreaterThan(0, strpos($response, "WebService:TestTriplification<"));
		$this->assertGreaterThan(0, strpos($response, "WebService:TestTriplification2"));
		$this->assertGreaterThan(0, strpos($response, "WebService:TestTriplification3"));
		$this->assertGreaterThan(0, strpos($response, "WebService:TestTriplification4"));
		
		$this->assertGreaterThan(0, strpos($response, "WS_58"));
		$this->assertGreaterThan(0, strpos($response, "WS_59"));
		$this->assertGreaterThan(0, strpos($response, "WS_60"));
		$this->assertGreaterThan(0, strpos($response, "WS_61"));
	}
	
	/*
	 * test if graphs and triples are updated correctly when adding ws calls
	 */
	function testRegularTriplification(){
		$titles = array('TestTriplification1');
		di_utils_setupWSUsages($titles);
		
		$html = $this->getHTML("TestTriplification1");
		
		//check whether the subject column is displayed correctly and whether the subject
		//creation pattern is evaluated correctly.
		$this->assertGreaterThan(0, strpos($html, $this->companySubjectHTML));
		$this->assertGreaterThan(0, strpos($html, $this->otherCompanySubjectHTML));
		$this->assertGreaterThan(0, strpos($html, $this->subjectColumnTitle));
		$this->assertGreaterThan(0, strpos($html, $this->subjectColumnTitleDefault));
		
		//verify triples
		$graphsURI = LODAdministrationStore::getInstance()->getSMWGraphsURI();
		$allPrefixes = TSNamespaces::getAllPrefixes();
		$allPrefixes .= ' '.LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes();
		$allPrefixes .= ' '.LODAdministrationStore::getInstance()->getProvenanceGraphPrefixes();
		
		$client = TSConnection::getConnector();
		$client->connect();
		
		$query = $allPrefixes.' SELECT ?p ?o WHERE { a:Company ?p ?o . } LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."WS_58_62");
		
		$this->assertGreaterThan(0, strpos($response, "property#HasAbstract"));
		$this->assertGreaterThan(0, strpos($response, '<literal datatype="http://www.w3.org/2001/XMLSchema#string">This is'));
		$this->assertGreaterThan(0, strpos($response, 'index.php/Product1'));
		$this->assertGreaterThan(0, strpos($response, 'index.php/Product2'));
		
		$query = $allPrefixes.' SELECT ?p ?o WHERE { a:OtherCompany ?p ?o . } LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."WS_58_62");
		
		$this->assertGreaterThan(0, strpos($response, 'Das ist der deutsche'));
		
		$query = $sllPrefixes.' SELECT ?p ?o WHERE { a:OtherCompany ?p ?o . } LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."WS_59_62");
		
		$this->assertGreaterThan(0, strpos($response, 'Das ist der deutsche'));
		
		$query = $allPrefixes.' '.' SELECT ?p ?o WHERE { smwGraphs:WS_58_62 ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."ProvenanceGraph");
		
		$this->assertGreaterThan(0, strpos($response, 'assertedBy'));
		
		$query = $allPrefixes.' '.' SELECT ?warrant ?p ?o WHERE { smwGraphs:WS_58_62 swp:assertedBy ?warrant . ?warrant ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."ProvenanceGraph");
		
		$this->assertGreaterThan(0, strpos($response, 'Warrant<'));
		$this->assertGreaterThan(0, strpos($response, 'authority'));
		
		$query = $allPrefixes.' SELECT ?p ?o WHERE { smwGraphs:WS_59_62 ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."ProvenanceGraph");
		
		$this->assertGreaterThan(0, strpos($response, 'assertedBy'));
		
		$query = $allPrefixes.' '.' SELECT ?warrant ?p ?o WHERE { smwGraphs:WS_59_62 swp:assertedBy ?warrant . ?warrant ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."ProvenanceGraph");
		
		$this->assertGreaterThan(0, strpos($response, 'Warrant<'));
		$this->assertGreaterThan(0, strpos($response, 'authority'));
	}
	
	public function testDataSpaceSPARQLQuery(){
		$graphsURI = LODAdministrationStore::getInstance()->getSMWGraphsURI();
		$allPrefixes = TSNamespaces::getAllPrefixes();
		$allPrefixes .= ' '.LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes();
		$allPrefixes .= ' '.LODAdministrationStore::getInstance()->getProvenanceGraphPrefixes();
		
		$client = TSConnection::getConnector();
		$client->connect();
		
		$query = $allPrefixes.' SELECT ?s ?p ?o WHERE { ?s ?p ?o . } LIMIT 200'; 
		$response = $client->query($query, 'merge=false| dataspace=WebService:TestTriplification');
		
		$this->assertGreaterThan(0, strpos($response, 'OtherCompany'));
	}

	/*
	 * Test if triples and graphs are updated correctly when
	 * editing an article
	 */
	function testEditArticleWithWSTriplifications(){
		$titles = array('TestTriplification1');
		di_utils_setupWSUsages($titles);
		
		$text = smwf_om_GetWikiText("TestTriplification2");
		smwf_om_EditArticle("TestTriplification1", 'PHPUnit', $text, '');
		
		//verify triples
		$graphsURI = LODAdministrationStore::getInstance()->getSMWGraphsURI();
		$allPrefixes = TSNamespaces::getAllPrefixes();
		$allPrefixes .= ' '.LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes();
		$allPrefixes .= ' '.LODAdministrationStore::getInstance()->getProvenanceGraphPrefixes();
		
		$client = TSConnection::getConnector();
		$client->connect();
		
		$query = $allPrefixes.' SELECT ?p ?o WHERE { a:Company ?p ?o . } LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."WS_58_62");
		
		$this->assertGreaterThan(0, strpos($response, "property#HasAbstract"));
		$this->assertGreaterThan(0, strpos($response, '<literal datatype="http://www.w3.org/2001/XMLSchema#string">This is'));
		$this->assertEquals(false, strpos($response, 'index.php/Product1'));
		$this->assertEquals(false, strpos($response, 'index.php/Product2'));
		
		$query = $allPrefixes.' SELECT ?p ?o WHERE { a:OtherCompany ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."WS_58_62");
		
		$this->assertEquals(false, strpos($response, 'Das ist der deutsche'));
		
		$query = $allPrefixes.' SELECT ?p ?o WHERE { a:OtherCompany ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."WS_59_62");
		
		$this->assertEquals(false, strpos($response, 'Das ist der deutsche'));
		
		$query = $allPrefixes.' '.TSNamespaces::getAllPrefixes().' SELECT ?p ?o WHERE { smwGraphs:WS_58_62 ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."ProvenanceGraph");
		
		$this->assertGreaterThan(0, strpos($response, 'assertedBy'));
		
		$query = $allPrefixes.' '.' SELECT ?warrant ?p ?o WHERE { smwGraphs:WS_58_62 swp:assertedBy ?warrant . ?warrant ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."ProvenanceGraph");
		
		$this->assertGreaterThan(0, strpos($response, 'Warrant<'));
		$this->assertGreaterThan(0, strpos($response, 'authority'));
		
		$query = $allPrefixes.' SELECT ?p ?o WHERE { smw-lde:WS_59_62 ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."ProvenanceGraph");
		
		$this->assertEquals(false, strpos($response, 'assertedBy'));
		
		$query = $allPrefixes.' '.' SELECT ?warrant ?p ?o WHERE { smwGraphs:WS_59_62 swp:assertedBy ?warrant . ?warrant ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."ProvenanceGraph");
		
		$this->assertEquals(false, strpos($response, 'Warrant<'));
		$this->assertEquals(false, strpos($response, 'authority'));
		$this->assertEquals(false, strpos($response, 'WS_59<'));
	}

	/*
	 * Test triple creation if empty subject is computed
	 */
	function testEmptySubjects(){
		$titles = array('TestTriplification3');
		di_utils_setupWSUsages($titles);
		
		$client = TSConnection::getConnector();
		$client->connect();
		
		$graphsURI = LODAdministrationStore::getInstance()->getSMWGraphsURI();
		$allPrefixes = TSNamespaces::getAllPrefixes();
		$allPrefixes .= ' '.LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes();
		
		$query = $allPrefixes.' SELECT ?p ?o WHERE { a:Company ?p ?o . } LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."WS_60_64");
		
		$this->assertEquals(false, strpos($response, "property#HasAbstract"));
		$this->assertEquals(false, strpos($response, "This is the"));
		$this->assertEquals(false, strpos($response, "property#HasProduct"));
		$this->assertEquals(false, strpos($response, "Product1"));
		$this->assertEquals(false, strpos($response, "Product2"));
		$this->assertEquals(false, strpos($response, "Product3"));
	}
	
	/*
	 * test notification if no subject creation pattern was defined
	 */
	function testMissingSubjectCreationPattern(){
		$titles = array('TestTriplification4');
		di_utils_setupWSUsages($titles);
		$html = $this->getHTML("TestTriplification4");
		
		$this->assertGreaterThan(0, strpos($html, $this->missingSubjectCreationPatternNote));
	}
	
	/*
	 * Check if triples and graphs are removed if updating a WWSD
	 */
	function testEditWWSD(){
		//necessary in order to initially triplify a result of the WWSD
		$titles = array('TestTriplification1');
		di_utils_setupWSUsages($titles);
		
		//Overwrite WWSD in order to check whether the graphs are updated
		$text = smwf_om_GetWikiText("WebService:TestTriplification2");
		smwf_om_EditArticle("WebService:TestTriplification", 'PHPUnit', $text, '');
		
		$graphsURI = LODAdministrationStore::getInstance()->getSMWGraphsURI();
		$allPrefixes = TSNamespaces::getAllPrefixes();
		$allPrefixes .= ' '.LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes();
		
		$client = TSConnection::getConnector();
		$client->connect();
		
		$query = $allPrefixes.' SELECT ?s ?o WHERE { ?s smw-lde:label ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."DataSourceInformationGraph");
		
		$this->assertGreaterThan(0, strpos($response, "WebService:TestTriplification<"));
		$this->assertGreaterThan(0, strpos($response, "WS_58"));
		
		$query = $allPrefixes.' SELECT ?p ?o WHERE { a:Company ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."WS_58_62");
		
		$this->assertEquals(false, strpos($response, "property#HasAbstract"));
		$this->assertEquals(false, strpos($response, '<literal datatype="http://www.w3.org/2001/XMLSchema#string">This is'));
		$this->assertEquals(false, strpos($response, 'index.php/Product1'));
		$this->assertEquals(false, strpos($response, 'index.php/Product2'));
		
		$query = $allPrefixes.' SELECT ?p ?o WHERE { smw-lde:WS_58_62 ?p ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."ProvenanceGraph");
		
		$this->assertEquals(false, strpos($response, 'assertedBy'));
	}
	
	/*
	 * Check if triples are delted from DataSourceInformation graph
	 * when deleting a WWSD
	 */
	function testDeleteWWSD(){
		try {
			smwf_om_DeleteArticle('WebService:TestTriplification', 'PHPUnit', '');
		} catch (Exception $e){
			//ignore strange exception
		}
		
		$client = TSConnection::getConnector();
		$client->connect();
		
		$graphsURI = LODAdministrationStore::getInstance()->getSMWGraphsURI();
		$allPrefixes = TSNamespaces::getAllPrefixes();
		$allPrefixes .= ' '.LODAdministrationStore::getInstance()->getSourceDefinitionPrefixes();
		
		$query = $allPrefixes.' SELECT ?s ?o WHERE { ?s smw-lde:label ?o . }  LIMIT 200'; 
		$response = $client->query($query, 'merge=false', $graphsURI."DataSourceInformationGraph");
		
		$this->assertEquals(false, strpos($response, "WebService:TestTriplification<"));
		$this->assertEquals(false, strpos($response, "WS_58"));
	}
	
	private function getHTML($title){
		$url = 'http://localhost/mediawiki/index.php/'.$title;
		$ctx = stream_context_create(array('http' => array('method' => 'GET')));
		
		$fp = @ fopen($url, 'rb', true, $ctx);

		$html = stream_get_contents($fp);
		
		return $html;
	}
}