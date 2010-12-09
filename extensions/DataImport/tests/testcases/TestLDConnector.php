<?php

require_once 'Util.php';
require_once 'DI_Utils.php';

class TestLDConnector extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = TRUE;
	
	private $hasTypeLink = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
	private $hasProductLink = 'http://localhost/mediawiki/index.php/Property:HasProduct';
	private $companyLink = 'http://localhost/mediawiki/index.php/Company';
	private $otherCompanyLink = 'http://localhost/mediawiki/index.php/OtherCompany';
	private $productLink1 = 'http://localhost/mediawiki/index.php/Product1';
	private $productLink2 = 'http://localhost/mediawiki/index.php/Product2';
	private $productLink3 = 'http://localhost/mediawiki/index.php/Product3';
	private $englishAbstract = "This is the abstract of the company";
	private $germanAbstract = 'Das ist der deutsche abstract.';
	
	//private $germanAbstractRow = '<tr><td><a href="http://localhost/mediawiki/index.php/OtherCompany" class="external free" title="http://localhost/mediawiki/index.php/OtherCompany" rel="nofollow">http://localhost/mediawiki/index.php/OtherCompany</a></td><td><a href="http://localhost/mediawiki/index.php/Property:HasAbstract" class="external free" title="http://localhost/mediawiki/index.php/Property:HasAbstract" rel="nofollow">http://localhost/mediawiki/index.php/Property:HasAbstract</a></td><td></td></tr>';
	private $germanAbstractRow = '<tr><td><a href="http://localhost/mediawiki/index.php/OtherCompany" class="external free" rel="nofollow">http://localhost/mediawiki/index.php/OtherCompany</a></td><td><a href="http://localhost/mediawiki/index.php/Property:HasAbstract" class="external free" rel="nofollow">http://localhost/mediawiki/index.php/Property:HasAbstract</a></td><td></td></tr>';
	
	//private $subjectAbstractTypeProductRow = '<tr><td><a href="http://localhost/mediawiki/index.php/Company" class="external free" title="http://localhost/mediawiki/index.php/Company" rel="nofollow">http://localhost/mediawiki/index.php/Company</a></td><td>This is the abstract of the company.</td><td><a href="http://localhost/mediawiki/index.php/Category:Company" class="external free" title="http://localhost/mediawiki/index.php/Category:Company" rel="nofollow">http://localhost/mediawiki/index.php/Category:Company</a></td><td><a href="http://localhost/mediawiki/index.php/Product1" class="external free" title="http://localhost/mediawiki/index.php/Product1" rel="nofollow">http://localhost/mediawiki/index.php/Product1</a></td></tr>';
	private $subjectAbstractTypeProductRow = '<tr><td><a href="http://localhost/mediawiki/index.php/Company" class="external free" rel="nofollow">http://localhost/mediawiki/index.php/Company</a></td><td>This is the abstract of the company.</td><td><a href="http://localhost/mediawiki/index.php/Category:Company" class="external free" rel="nofollow">http://localhost/mediawiki/index.php/Category:Company</a></td><td><a href="http://localhost/mediawiki/index.php/Product1" class="external free" rel="nofollow">http://localhost/mediawiki/index.php/Product1</a></td></tr>';
	
	//private $subjectProductRow = 'tr><td><a href="http://localhost/mediawiki/index.php/Company" class="external free" title="http://localhost/mediawiki/index.php/Company" rel="nofollow">http://localhost/mediawiki/index.php/Company</a></td><td></td><td></td><td><a href="http://localhost/mediawiki/index.php/Product2" class="external free" title="http://localhost/mediawiki/index.php/Product2" rel="nofollow">http://localhost/mediawiki/index.php/Product2</a></td></tr>';
	private $subjectProductRow = '<tr><td><a href="http://localhost/mediawiki/index.php/Company" class="external free" rel="nofollow">http://localhost/mediawiki/index.php/Company</a></td><td></td><td></td><td><a href="http://localhost/mediawiki/index.php/Product2" class="external free" rel="nofollow">http://localhost/mediawiki/index.php/Product2</a></td></tr>';
	
	//private $subjectTypeProductRow = '<tr><td><a href="http://localhost/mediawiki/index.php/OtherCompany" class="external free" title="http://localhost/mediawiki/index.php/OtherCompany" rel="nofollow">http://localhost/mediawiki/index.php/OtherCompany</a></td><td></td><td><a href="http://localhost/mediawiki/index.php/Category:Company" class="external free" title="http://localhost/mediawiki/index.php/Category:Company" rel="nofollow">http://localhost/mediawiki/index.php/Category:Company</a></td><td><a href="http://localhost/mediawiki/index.php/Product3" class="external free" title="http://localhost/mediawiki/index.php/Product3" rel="nofollow">http://localhost/mediawiki/index.php/Product3</a></td></tr></table>';
	private $subjectTypeProductRow = '<tr><td><a href="http://localhost/mediawiki/index.php/OtherCompany" class="external free" rel="nofollow">http://localhost/mediawiki/index.php/OtherCompany</a></td><td></td><td><a href="http://localhost/mediawiki/index.php/Category:Company" class="external free" rel="nofollow">http://localhost/mediawiki/index.php/Category:Company</a></td><td><a href="http://localhost/mediawiki/index.php/Product3" class="external free" rel="nofollow">http://localhost/mediawiki/index.php/Product3</a></td></tr></table>';
	
	
	private static $wgValidSkinNames;


	function setUp(){
		$titles = array('LDTest');
		if(is_null(self::$wgValidSkinNames)){
			self::$wgValidSkinNames = Skin::getSkinNames();
		} else {
			global $wgValidSkinNames;
			$wgValidSkinNames = self::$wgValidSkinNames;
		}
		di_utils_setupWebServices($titles);
	}
	
	function tearDown() {
		di_utils_truncateWSTables();
	}
	
	function testAllSubjectsAllPredicatesAllObjects(){
		$titles = array('TestLD1');
		di_utils_setupWSUsages($titles);
		$html = $this->getHTML("TestLD1");
		$html = str_replace("\n", "", $html);
		$html = str_replace("\t", "", $html);
		
		$this->assertGreaterThan(0, strpos($html, $this->germanAbstractRow));
		$this->assertGreaterThan(0, strpos($html, $this->hasTypeLink));
		$this->assertGreaterThan(0, strpos($html, $this->hasProductLink));
		$this->assertGreaterThan(0, strpos($html, $this->otherCompanyLink));
		$this->assertGreaterThan(0, strpos($html, $this->companyLink));
		$this->assertGreaterThan(0, strpos($html, $this->productLink1));
		$this->assertGreaterThan(0, strpos($html, $this->productLink2));
		$this->assertGreaterThan(0, strpos($html, $this->productLink3));
		$this->assertGreaterThan(0, strpos($html, $this->englishAbstract));
		$this->assertEquals(false, strpos($html, $this->germanAbstract));
	}
	
	function testAllSubjectsAllPredicatesAllObjectsSpecialSubject(){
		$titles = array('TestLD2');
		di_utils_setupWSUsages($titles);
		
		$html = $this->getHTML("TestLD2");
		$html = str_replace("\n", "", $html);
		$html = str_replace("\t", "", $html);
		
		$this->assertGreaterThan(0, strpos($html, $this->germanAbstractRow));
		$this->assertGreaterThan(0, strpos($html, $this->hasTypeLink));
		$this->assertGreaterThan(0, strpos($html, $this->hasProductLink));
		$this->assertGreaterThan(0, strpos($html, $this->otherCompanyLink));
		$this->assertGreaterThan(0, strpos($html, $this->productLink3));
		
		$this->assertEquals(false, strpos($html, $this->companyLink));
		$this->assertEquals(false, strpos($html, $this->productLink1));
		$this->assertEquals(false, strpos($html, $this->productLink2));
		$this->assertEquals(false, strpos($html, $this->englishAbstract));
		$this->assertEquals(false, strpos($html, $this->germanAbstract));
	}
	
	function testAllSubjectsAbstractTypeProduct(){
		$titles = array('TestLD3');
		di_utils_setupWSUsages($titles);
	
		$html = $this->getHTML("TestLD3");
		$html = str_replace("\n", "", $html);
		$html = str_replace("\t", "", $html);
		
		$this->assertGreaterThan(0, strpos($html, $this->subjectAbstractTypeProductRow));
		$this->assertGreaterThan(0, strpos($html, $this->subjectProductRow));
		$this->assertGreaterThan(0, strpos($html, $this->subjectTypeProductRow));
	}
	
	function testPredicatesAndLanguageParams(){
		$titles = array('TestLD4');
		di_utils_setupWSUsages($titles);
	
 		$html = strtolower($this->getHTML("TestLD4"));
		$html = str_replace("\n", "", $html);
		$html = str_replace("\t", "", $html);
		
		$this->assertGreaterThan(0, strpos($html, strtolower($this->subjectAbstractTypeProductRow)));
		$this->assertGreaterThan(0, strpos($html, strtolower($this->subjectProductRow)));
		$this->assertGreaterThan(0, strpos($html, strtolower($this->subjectTypeProductRow)));
	}
	
	private function getHTML($title){
		$url = 'http://localhost/mediawiki/index.php/'.$title;
		$ctx = stream_context_create(array('http' => array('method' => 'GET')));
		
		$fp = @ fopen($url, 'rb', true, $ctx);

		$html = stream_get_contents($fp);
		
		return $html;
	}
}