<?php
/**
 * @file
 * @ingroup HaloACL_Tests
 */

class TestSMWStoreSuite extends PHPUnit_Framework_TestSuite
{
	
	private $mOrderOfArticleCreation;
	
	public static function suite() {
		
		$suite = new TestSMWStoreSuite();
		$suite->addTestSuite('TestSMWStore');
		return $suite;
	}
	
	protected function setUp() {
    	HACLStorage::reset(HACL_STORE_SQL);
		HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
		
    	User::createNew("NormalUser", array("password" => User::crypt("test")));
    	User::createNew("RestrictedUser", array("password" => User::crypt("test")));
        User::idFromName("NormalUser");  
        User::idFromName("RestrictedUser");  
        
        $this->initArticleContent();
        $this->createArticles();
		
	}
	
	protected function tearDown() {
		$this->removeArticles();
    	HACLStorage::reset(HACL_STORE_SQL);
		HACLStorage::getDatabase()->dropDatabaseTables(false);
		HACLStorage::getDatabase()->initDatabaseTables(false);
		
	}

	private function createArticles() {
    	global $wgUser;
    	$wgUser = User::newFromId(User::idFromName("NormalUser"));
    	
    	$file = __FILE__;
    	try {
	    	foreach ($this->mOrderOfArticleCreation as $title) {
	    		$pf = HACLParserFunctions::getInstance();
	    		$pf->reset();
				self::createArticle($title, $this->mArticles[$title]);
	    	}
    	} catch (Exception $e) {
			echo "Unexpected exception while testing ".basename($file)."::createArticles():".$e->getMessage();
			throw $e;
		}
    	
    }
	
    private function createArticle($title, $content) {
	
    	if (!isset($content)) {
    		return;
    	}
    	global $wgTitle;
		$wgTitle = $title = Title::newFromText($title);
		$article = new Article($title);
		// Set the article's content
		$success = $article->doEdit($content, 'Created for test case', 
		                            $article->exists() ? EDIT_UPDATE : EDIT_NEW);
		if (!$success) {
			echo "Creating article ".$title->getFullText()." failed\n";
		}
	}
    
	private function removeArticles() {
		
		$wgUser = User::newFromId(User::idFromName("WikiSysop"));
		
	    foreach ($this->mOrderOfArticleCreation as $a) {
	    	$t = Title::newFromText($a);
	    	$article = new Article($t);
			$article->doDeleteArticle("Testing");
		}
		
	}
    
	
	private function initArticleContent() {
		$this->mOrderOfArticleCreation = array(
			'Property:ProtectedProperty',
			'Property:NormalProperty',
			'Category:ACL/ACL',
			'ACL:Property/ProtectedProperty',
			'ProtectedPage',
			'ACL:Page/ProtectedPage',
			'PageWithProtectedProperties',
			'NormalPage',
			'Property:PropWithDomainAndRange'
		);
		
		$this->mArticles = array(
//------------------------------------------------------------------------------		
			'Property:ProtectedProperty' =>
<<<ACL
This property is not accessible by RestrictedUser.

[[has domain and range::; | ]]

[[has type::Type:Page| ]]
ACL
,
//------------------------------------------------------------------------------		
			'Property:NormalProperty' =>
<<<ACL
There are no access restrictions for this property.

[[has domain and range::; | ]]

[[has type::Type:Page| ]]
ACL
,
//------------------------------------------------------------------------------		
			'Category:ACL/ACL' =>
<<<ACL
This is the category for security descriptors.
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Property/ProtectedProperty' =>
<<<ACL
{{#property access: assigned to=User:NormalUser
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=fullaccess for U:NormalUser
 |name=Right}}

{{#manage rights: assigned to=User:NormalUser}}
[[Category:ACL/ACL]]
ACL
,
//------------------------------------------------------------------------------		
			'ACL:Page/ProtectedPage' =>
<<<ACL
{{#access: assigned to=User:NormalUser
 |actions=read,edit,formedit,wysiwyg,create,move,delete,annotate
 |description=fullaccess for U:NormalUser
 |name=Right}}

{{#manage rights: assigned to=User:NormalUser}}
[[Category:ACL/ACL]]
ACL
,
//------------------------------------------------------------------------------		
			'ProtectedPage' =>
<<<ACL
	This page is not accessible for RestrictedUser.
	
	[[ProtectedProperty::ProtectedPage]]
	[[ProtectedProperty::NormalPage]]
	
	[[NormalProperty::NormalPage]]
	[[NormalProperty::ProtectedPage]]
	
{{#ask: [[HA::+]]
| ?HB
}}
	
ACL
,
//------------------------------------------------------------------------------		
			'PageWithProtectedProperties' =>
<<<ACL
	This page contains a protected and a normal property.
	
	[[ProtectedProperty::ProtectedPage]]
	[[ProtectedProperty::NormalPage]]
	
	[[NormalProperty::NormalPage]]
	[[NormalProperty::ProtectedPage]]

{{#ask: [[HA::+]]
| ?HB
}}
	
ACL
,
//------------------------------------------------------------------------------		
			'NormalPage' =>
<<<ACL
	This page contains no properties.
{{#ask: [[HA::+]]
| ?HB
}}
ACL
,
//------------------------------------------------------------------------------		
			'Property:PropWithDomainAndRange' =>
<<<ACL
	This is a property with domain and range definition.
	[[Has domain and range::Category:Person;Category:Dog]]
ACL
		);
	}
	
}


/**
 * This class tests the wrapper for SMW Stores that is used when semantic properties
 * are protected.
 * 
 * The protection of properties has to be switched on ($haclgProtectProperties = true)
 * 
 * @author thsc
 *
 */
class TestSMWStore extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	
	function setUp() {
		
	}

	function tearDown() {
		
	}
	
	/**
	 * Tests that the normal SMW store is replaced by the HACLSMWStore
	 */
	function testRegisteredStore() {
		
		$store = smwfGetStore();
		$isSMWStore = $store instanceof HACLSMWStore;
		$this->assertTrue($isSMWStore);
			
	}
	
	/**
	 * Tests that the SMWHalo base store is wrapped by the HACLSMWStore
	 */
	function testSmwBaseStore() {
		$store = smwfNewBaseStore();
		$isSMWStore = $store instanceof HACLSMWStore;
		$this->assertTrue($isSMWStore);
	}
	
	/**
	 * Tests the method HACLSMWStore::getSemanticData()
	 */
	function testGetSemanticData() {
		global $wgUser;
		
    	$wgUser = User::newFromId(User::idFromName("NormalUser"));
    	$this->doTestGetSemanticData(true);

    	$wgUser = User::newFromId(User::idFromName("RestrictedUser"));
    	$this->doTestGetSemanticData(false);
    	
	}
	
	/**
	 * Tests the method HACLSMWStore::getPropertyValues()
	 */
	function testGetPropertyValues() {
		
		// Get properties for all pages
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "NormalUser",
    		'page'		=> null,
    		'property'	=> 'NormalProperty',
    		'values'	=> array('NormalPage', 'ProtectedPage')
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "NormalUser",
    		'page'		=> null,
    		'property'	=> 'ProtectedProperty',
    		'values'	=> array('NormalPage', 'ProtectedPage')
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "RestrictedUser",
    		'page'		=> null,
    		'property'	=> 'NormalProperty',
    		'values'	=> array('NormalPage')
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "RestrictedUser",
    		'page'		=> null,
    		'property'	=> 'ProtectedProperty',
    		'values'	=> array()
    	));
    	
		// Get properties for page ProtectedPage
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "NormalUser",
    		'page'		=> 'ProtectedPage',
    		'property'	=> 'NormalProperty',
    		'values'	=> array('NormalPage', 'ProtectedPage')
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "NormalUser",
    		'page'		=> null,
    		'property'	=> 'ProtectedProperty',
    		'values'	=> array('NormalPage', 'ProtectedPage')
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "RestrictedUser",
    		'page'		=> 'ProtectedPage',
    		'property'	=> 'NormalProperty',
    		'values'	=> array()
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "RestrictedUser",
    		'page'		=> 'ProtectedPage',
    		'property'	=> 'ProtectedProperty',
    		'values'	=> array()
    	));
    	
		// Get properties for page PageWithProtectedProperties
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "NormalUser",
    		'page'		=> 'PageWithProtectedProperties',
    		'property'	=> 'NormalProperty',
    		'values'	=> array('NormalPage', 'ProtectedPage')
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "NormalUser",
    		'page'		=> 'PageWithProtectedProperties',
    		'property'	=> 'ProtectedProperty',
    		'values'	=> array('NormalPage', 'ProtectedPage')
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "RestrictedUser",
    		'page'		=> 'PageWithProtectedProperties',
    		'property'	=> 'NormalProperty',
    		'values'	=> array('NormalPage')
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "RestrictedUser",
    		'page'		=> 'PageWithProtectedProperties',
    		'property'	=> 'ProtectedProperty',
    		'values'	=> array()
    	));
    	
		// Get properties for page NormalPage
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "NormalUser",
    		'page'		=> 'NormalPage',
    		'property'	=> 'NormalProperty',
    		'values'	=> array()
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "NormalUser",
    		'page'		=> 'NormalPage',
    		'property'	=> 'ProtectedProperty',
    		'values'	=> array()
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "RestrictedUser",
    		'page'		=> 'NormalPage',
    		'property'	=> 'NormalProperty',
    		'values'	=> array()
    	));
    	$this->doTestGetPropertyValues(array(
    		'user'		=> "RestrictedUser",
    		'page'		=> 'NormalPage',
    		'property'	=> 'ProtectedProperty',
    		'values'	=> array()
    	));
    	
	}
	
	/**
	 * Tests the method HACLSMWStore::getPropertySubjects()
	 */
	function testGetPropertySubjects() {
		
		$this->doTestGetPropertySubjects(array(
    		'user'		=> "NormalUser",
    		'property'	=> 'NormalProperty',
    		'values'	=> array('ProtectedPage', 'PageWithProtectedProperties')
		
		));
		
		$this->doTestGetPropertySubjects(array(
    		'user'		=> "ProtectedUser",
    		'property'	=> 'NormalProperty',
    		'values'	=> array('PageWithProtectedProperties')
		
		));
		
		$this->doTestGetPropertySubjects(array(
    		'user'		=> "NormalUser",
    		'property'	=> 'ProtectedProperty',
    		'values'	=> array('ProtectedPage', 'PageWithProtectedProperties')
		
		));
		
		$this->doTestGetPropertySubjects(array(
    		'user'		=> "ProtectedUser",
    		'property'	=> 'ProtectedProperty',
    		'values'	=> array()
		
		));
	}
	
	/**
	 * Tests the method HACLSMWStore::getProperties()
	 */
	function testGetProperties() {
		
		// Get properties from page ProtectedPage
		$this->doTestGetProperties(array(
    		'user'		=> "NormalUser",
    		'page'		=> 'ProtectedPage',
    		'values'	=> array('ProtectedProperty', 'NormalProperty')
		
		));
		
		$this->doTestGetProperties(array(
    		'user'		=> "ProtectedUser",
    		'page'		=> 'ProtectedPage',
    		'values'	=> array()
		
		));
		
		// Get properties from page PageWithProtectedProperties
		$this->doTestGetProperties(array(
    		'user'		=> "NormalUser",
    		'page'		=> 'PageWithProtectedProperties',
    		'values'	=> array('ProtectedProperty', 'NormalProperty')
		
		));
		
		$this->doTestGetProperties(array(
    		'user'		=> "ProtectedUser",
    		'page'		=> 'PageWithProtectedProperties',
    		'values'	=> array('NormalProperty')
		
		));

		// Get properties from page NormalPage
		$this->doTestGetProperties(array(
    		'user'		=> "NormalUser",
    		'page'		=> 'NormalPage',
    		'values'	=> array()
		
		));
		
		$this->doTestGetProperties(array(
    		'user'		=> "ProtectedUser",
    		'page'		=> 'NormalPage',
    		'values'	=> array()
		
		));
		
	}
	
	/**
	 * Tests the method HACLSMWStore::getInProperties()
	 */
	function testGetInProperties() {
		// Get properties that link to NormalPage
		$this->doTestGetInProperties(array(
    		'user'		=> "NormalUser",
    		'object'	=> 'NormalPage',
			'values'	=> array('ProtectedProperty', 'NormalProperty')
		
		));
		
		$this->doTestGetInProperties(array(
    		'user'		=> "ProtectedUser",
    		'object'	=> 'NormalPage',
    		'values'	=> array('NormalProperty')
		
		));
		
		// Get properties that link to ProtectedPage
		$this->doTestGetInProperties(array(
    		'user'		=> "NormalUser",
    		'object'	=> 'ProtectedPage',
			'values'	=> array('ProtectedProperty', 'NormalProperty')
		
		));
		
		$this->doTestGetInProperties(array(
    		'user'		=> "ProtectedUser",
    		'object'	=> 'ProtectedPage',
    		'values'	=> array()
		
		));
		
	}
	
	
	/**
	 * Tests the method HACLSMWStore::getPropertiesSpecial()
	 */
	function testGetPropertiesSpecial() {
		
		$this->doTestGetPropertiesSpecial(array(
    		'user'		=> "NormalUser",
    		'mustContain'	=> array('NormalProperty', 'ProtectedProperty'),
    		'mustNotContain' => array(),
		));
		
		$this->doTestGetPropertiesSpecial(array(
    		'user'		=> "ProtectedUser",
    		'mustContain'	=> array('NormalProperty'),
    		'mustNotContain' => array('ProtectedProperty'),
		));
		
		
	}
	
	
	/**
	 * Tests the method HACLSMWStore::getQueryResult()
	 */
	function testGetQueryResult() {
		$this->doTestGetQueryResult(array(
		    'query'		=> "[[NormalProperty::+]]",
			'format'	=> "table",
    		'user'		=> "NormalUser",
			'printouts' => array('NormalProperty'),
    		'result'	=> array(
    			'ProtectedPage' 
					=> array('NormalProperty' => array('ProtectedPage', 'NormalPage')),
    			'PageWithProtectedProperties' 
					=> array('NormalProperty' => array('ProtectedPage', 'NormalPage')),
			)
		));
		
		$this->doTestGetQueryResult(array(
		    'query'		=> "[[NormalProperty::+]]",
			'format'	=> "table",
    		'user'		=> "RestrictedUser",
			'printouts' => array('NormalProperty'),
    		'result'	=> array(
    			'PageWithProtectedProperties' 
					=> array('NormalProperty' => array('NormalPage')),
			)
		));
		
		$this->doTestGetQueryResult(array(
		    'query'		=> "[[ProtectedProperty::+]]",
			'format'	=> "table",
    		'user'		=> "NormalUser",
			'printouts' => array('ProtectedProperty'),
    		'result'	=> array(
    			'ProtectedPage' 
					=> array('ProtectedProperty' => array('ProtectedPage', 'NormalPage')),
    			'PageWithProtectedProperties' 
					=> array('ProtectedProperty' => array('ProtectedPage', 'NormalPage')),
			)
		));
		
		$this->doTestGetQueryResult(array(
		    'query'		=> "[[ProtectedProperty::+]]",
			'format'	=> "table",
    		'user'		=> "RestrictedUser",
			'printouts' => array('ProtectedProperty'),
    		'result'	=> array()
		));
		
		
		$this->doTestGetQueryResult(array(
		    'query'		=> "[[NormalProperty::+]][[ProtectedProperty::+]]",
			'format'	=> "table",
    		'user'		=> "NormalUser",
			'printouts' => array('NormalProperty', 'ProtectedProperty'),
    		'result'	=> array(
    			'ProtectedPage' 
					=> array('NormalProperty' => array('ProtectedPage', 'NormalPage'),
							 'ProtectedProperty' => array('ProtectedPage', 'NormalPage')
						),
    			'PageWithProtectedProperties' 
					=> array('NormalProperty' => array('ProtectedPage', 'NormalPage'),
							 'ProtectedProperty' => array('ProtectedPage', 'NormalPage')
						)
			)
		));

		$this->doTestGetQueryResult(array(
		    'query'		=> "[[NormalProperty::+]][[ProtectedProperty::+]]",
			'format'	=> "count",
    		'user'		=> "NormalUser",
			'printouts' => array('NormalProperty', 'ProtectedProperty'),
    		'result'	=> 2
		));
		
		
		$this->doTestGetQueryResult(array(
    		'user'		=> "RestrictedUser",
		    'query'		=> "[[NormalProperty::+]][[ProtectedProperty::+]]",
			'format'	=> "table",
			'printouts' => array('NormalProperty', 'ProtectedProperty'),
			'result'	=> array(
    			'PageWithProtectedProperties' 
					=> array('NormalProperty' => array('NormalPage')
						)
			)
		));
		
		$this->doTestGetQueryResult(array(
		    'query'		=> "[[NormalProperty::+]] || [[ProtectedProperty::+]]",
			'format'	=> "table",
    		'user'		=> "NormalUser",
			'printouts' => array('NormalProperty', 'ProtectedProperty'),
    		'result'	=> array(
    			'ProtectedPage' 
					=> array('NormalProperty' => array('ProtectedPage', 'NormalPage'),
							 'ProtectedProperty' => array('ProtectedPage', 'NormalPage')
						),
    			'PageWithProtectedProperties' 
					=> array('NormalProperty' => array('ProtectedPage', 'NormalPage'),
							 'ProtectedProperty' => array('ProtectedPage', 'NormalPage')
						)
			)
		));

		$this->doTestGetQueryResult(array(
    		'user'		=> "RestrictedUser",
		    'query'		=> "[[NormalProperty::+]] || [[ProtectedProperty::+]]",
			'format'	=> "table",
			'printouts' => array('NormalProperty', 'ProtectedProperty'),
			'result'	=> array(
    			'PageWithProtectedProperties' 
					=> array('NormalProperty' => array('NormalPage')
						)
			)
		));
		
		
	}
	
	/**
	 * Tests the method HACLSMWStore::getQueryResult() via the SPARQL query
	 * processor.
	 */
	function testSPARQLGetQueryResults() {
		global $smwgTripleStoreGraph;
		
		$query = <<<QUERY
SELECT ?s ?o
  WHERE {
    GRAPH <$smwgTripleStoreGraph> {
      ?s prop:NormalProperty ?o .
    }
  }
QUERY;

 		$this->doTestSPARQLGetQueryResult(array(
		    'query'		=> $query,
			'format'	=> "table",
    		'user'		=> "NormalUser",
			'printouts' => array('s', 'o'),
    		'result'	=> array(
							array("s" => "PageWithProtectedProperties",
							      "o" => "ProtectedPage"),
							array("s" => "PageWithProtectedProperties",
							      "o" => "NormalPage"),
							array("s" => "ProtectedPage",
							      "o" => "ProtectedPage"),
							array("s" => "ProtectedPage",
							      "o" => "NormalPage")
						)
		));

		$this->doTestSPARQLGetQueryResult(array(
		    'query'		=> $query,
			'format'	=> "table",
    		'user'		=> "RestrictedUser",
			'printouts' => array('s', 'o'),
    		'result'	=> array(
							array("s" => "PageWithProtectedProperties",
							      "o" => "NormalPage"),
							array("s" => null,
							      "o" => "NormalPage"),
							array("s" => "PageWithProtectedProperties",
							      "o" => null)
						)
		));
		
		$this->doTestSPARQLGetQueryResult(array(
		    'query'		=> "[[NormalProperty::+]][[ProtectedProperty::+]]",
			'format'	=> "table",
    		'user'		=> "NormalUser",
			'printouts' => array('NormalProperty', 'ProtectedProperty'),
    		'result'	=> array(
							array("subject"	=> "PageWithProtectedProperties",
								  "NormalProperty" => "NormalPage",
							      "ProtectedProperty" => "ProtectedPage"),
							array("subject"	=> "PageWithProtectedProperties",
								  "NormalProperty" => "NormalPage",
							      "ProtectedProperty" => "NormalPage"),
							array("subject"	=> "PageWithProtectedProperties",
								  "NormalProperty" => "ProtectedPage",
							      "ProtectedProperty" => "NormalPage"),
							array("subject"	=> "PageWithProtectedProperties",
								  "NormalProperty" => "ProtectedPage",
							      "ProtectedProperty" => "ProtectedPage"),
							
							array("subject"	=> "ProtectedPage",
								  "NormalProperty" => "NormalPage",
							      "ProtectedProperty" => "ProtectedPage"),
							array("subject"	=> "ProtectedPage",
								  "NormalProperty" => "NormalPage",
							      "ProtectedProperty" => "NormalPage"),
							array("subject"	=> "ProtectedPage",
								  "NormalProperty" => "ProtectedPage",
							      "ProtectedProperty" => "NormalPage"),
							array("subject"	=> "ProtectedPage",
								  "NormalProperty" => "ProtectedPage",
							      "ProtectedProperty" => "ProtectedPage"),
							)
		));
		
		$query = <<<QUERY
SELECT ?s ?o
  WHERE {
    GRAPH <$smwgTripleStoreGraph> {
      ?s prop:ProtectedProperty ?o .
    }
  }
QUERY;

		$this->doTestSPARQLGetQueryResult(array(
		    'query'		=> $query,
			'format'	=> "table",
    		'user'		=> "NormalUser",
			'printouts' => array('s', 'o'),
    		'result'	=> array(
							array("s" => "PageWithProtectedProperties",
							      "o" => "ProtectedPage"),
							array("s" => "PageWithProtectedProperties",
							      "o" => "NormalPage"),
							array("s" => "ProtectedPage",
							      "o" => "ProtectedPage"),
							array("s" => "ProtectedPage",
							      "o" => "NormalPage")
						)
		));

		$this->doTestSPARQLGetQueryResult(array(
		    'query'		=> $query,
			'format'	=> "table",
    		'user'		=> "RestrictedUser",
			'printouts' => array('s', 'o'),
    		'result'	=> array()
		));
		
		$this->doTestSPARQLGetQueryResult(array(
		    'query'		=> "[[NormalProperty::+]][[ProtectedProperty::+]]",
			'format'	=> "table",
    		'user'		=> "NormalUser",
			'printouts' => array('NormalProperty', 'ProtectedProperty'),
    		'result'	=> array(
							array("subject"	=> "PageWithProtectedProperties",
								  "NormalProperty" => "NormalPage",
							      "ProtectedProperty" => "ProtectedPage"),
							array("subject"	=> "PageWithProtectedProperties",
								  "NormalProperty" => "NormalPage",
							      "ProtectedProperty" => "NormalPage"),
							array("subject"	=> "PageWithProtectedProperties",
								  "NormalProperty" => "ProtectedPage",
							      "ProtectedProperty" => "NormalPage"),
							array("subject"	=> "PageWithProtectedProperties",
								  "NormalProperty" => "ProtectedPage",
							      "ProtectedProperty" => "ProtectedPage"),
							
							array("subject"	=> "ProtectedPage",
								  "NormalProperty" => "NormalPage",
							      "ProtectedProperty" => "ProtectedPage"),
							array("subject"	=> "ProtectedPage",
								  "NormalProperty" => "NormalPage",
							      "ProtectedProperty" => "NormalPage"),
							array("subject"	=> "ProtectedPage",
								  "NormalProperty" => "ProtectedPage",
							      "ProtectedProperty" => "NormalPage"),
							array("subject"	=> "ProtectedPage",
								  "NormalProperty" => "ProtectedPage",
							      "ProtectedProperty" => "ProtectedPage"),
							)
		));
		

		$this->doTestSPARQLGetQueryResult(array(
		    'query'		=> "[[NormalProperty::+]][[ProtectedProperty::+]]",
			'format'	=> "table",
    		'user'		=> "RestrictedUser",
			'printouts' => array('NormalProperty', 'ProtectedProperty'),
    		'result'	=> array(
							array("subject"	=> "PageWithProtectedProperties",
								  "NormalProperty" => "NormalPage",
							),
							array("subject"	=> null,
								  "NormalProperty" => "NormalPage",
							),
							array("subject"	=> "PageWithProtectedProperties",
								  "NormalProperty" => null
							)
						)
			));
		
		$query = <<<QUERY
SELECT ?s ?o
  WHERE {
    GRAPH <$smwgTripleStoreGraph> {
      { ?s prop:NormalProperty ?o . }
      UNION
      { ?s prop:ProtectedProperty ?o . }
	}
  }
QUERY;

		$this->doTestSPARQLGetQueryResult(array(
		    'query'		=> $query,
			'format'	=> "table",
    		'user'		=> "NormalUser",
			'printouts' => array('s', 'o'),
    		'result'	=> array(
							array("s" => "PageWithProtectedProperties",
							      "o" => "ProtectedPage"),
							array("s" => "PageWithProtectedProperties",
							      "o" => "NormalPage"),
							array("s" => "ProtectedPage",
							      "o" => "ProtectedPage"),
							array("s" => "ProtectedPage",
							      "o" => "NormalPage")
						)
		));

		$this->doTestSPARQLGetQueryResult(array(
		    'query'		=> $query,
			'format'	=> "table",
    		'user'		=> "RestrictedUser",
			'printouts' => array('s', 'o'),
    		'result'	=> array(
							array("s" => "PageWithProtectedProperties",
							      "o" => "NormalPage"),
							array("s" => null,
							      "o" => "NormalPage"),
							array("s" => "PageWithProtectedProperties",
							      "o" => null)
						)
		));
			
				
	}
	
	/**
	 * This function checks if all protected properties are correctly hidden
	 * in an article. 
	 */
	function testPropertiesInPage() {
		$errMsg = wfMsgForContent('hacl_protected_property_error');
		
		$wikiTexts = array(
			"No properties in this text",
			"[[ProtectedProperty::NormalPage]]",
			"[[NormalProperty::NormalPage]]",
			"[[ProtectedProperty::ProtectedPage]]",
			"[[NormalProperty::ProtectedPage]]"
		);
		$this->doTestPropertiesInPage(array(
    		'user'		=> "NormalUser",
		    'wikitexts'	=> $wikiTexts,
			'values'	=> array(
				"No properties in this text",
				"NormalPage",
				"NormalPage",
				"ProtectedPage",
				"ProtectedPage"
						)
		));
		
		$this->doTestPropertiesInPage(array(
    		'user'		=> "RestrictedUser",
		    'wikitexts'	=> $wikiTexts,
			'values'	=> array(
				"No properties in this text",
				array("***", $errMsg),
				"NormalPage",
				array("***", $errMsg),
				array("***", $errMsg)
						)
		));
		
	}
	
	/**
	 * With the query management handler it is possible to find queries on pages.
	 * This test makes sure that no queries from protected pages are retrieved.
	 */
	function testQueryRetrieval() {
		global $wgUser;
		
		//--- Test as normal user ---
    	$wgUser = User::newFromId(User::idFromName('NormalUser'));
		
		$propertyPrintRequests = array('HB' => true);
		$queryMetadataPattern = new SMWQMQueryMetadata(true, $propertyPrintRequests);
		$qmr = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadataPattern);
		
		$this->assertEquals(3, count($qmr), "Expected to find two unprotected pages with queries.");
		$pages = array($qmr[0]->usedInArticle, $qmr[1]->usedInArticle, $qmr[2]->usedInArticle);
		$expected = array("PageWithProtectedProperties", "NormalPage", "ProtectedPage");
		$diff = array_diff($expected, $pages);
		$this->assertEquals(0, count($diff), "Expected to find the pages 'PageWithProtectedProperties' and 'NormalPage' with queries.");
		
		//--- Test as restricted user ---
    	$wgUser = User::newFromId(User::idFromName('RestrictedUser'));
		
		$propertyPrintRequests = array('HB' => true);
		$queryMetadataPattern = new SMWQMQueryMetadata(true, $propertyPrintRequests);
		$qmr = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadataPattern);
		
		$this->assertEquals(2, count($qmr), "Expected to find two unprotected pages with queries.");
		$pages = array($qmr[0]->usedInArticle, $qmr[1]->usedInArticle);
		$expected = array("PageWithProtectedProperties", "NormalPage");
		$diff = array_diff($expected, $pages);
		$this->assertEquals(0, count($diff), "Expected to find the pages 'PageWithProtectedProperties' and 'NormalPage' with queries.");
		
	}
	
	
    /**
     * Data provider for testGetRecordValues
     */
    function providerForGetRecordValues() {
    	return array(
    		// Article name, property, index of property value, expected
    		array("Property:PropWithDomainAndRange", "Has domain and range", 0, "Category:Person"),
    		array("Property:PropWithDomainAndRange", "Has domain and range", 1, "Category:Dog"),
    	);
    }
	
	/**
	 * Test if all values of a record are retrieved correctly.
	 * @dataProvider providerForGetRecordValues
	 */
	public function testGetRecordValues($name, $property, $index, $expected) {
		$store = smwfGetStore();
		$subject = Title::newFromText($name);
		$values = $store->getPropertyValues($subject,
											SMWPropertyValue::makeUserProperty($property));
		if (is_array($values)) {
			$idx = array_keys($values);
			$idx = $idx[0];
			if($values[$idx] instanceof SMWRecordValue){
				$dVs = $values[$idx]->getDVs();
				if(count($dVs) >= $index+1){
					$idx = array_keys($dVs);
					$idx = $idx[$index];
					$result = $dVs[$idx]->getShortWikiText();
					$this->assertEquals($expected, $result);
				}
			}
		}
	}
	
	/**
	 * Performs the actual tests of the method HACLSMWStore::getSemanticData().
	 * 
	 * @param bool $normalUser
	 * 		If <true>, the current user has no access restrictions.
	 */
	function doTestGetSemanticData($normalUser) {
		
		$store = smwfGetStore();
		
		// All properties in the page "ProtectedPage" are not accessible
		$title = Title::newFromText("ProtectedPage");
		$props = $store->getSemanticData(SMWWikiPageValue::makePageFromTitle($title));
		
		if ($normalUser) {
			$this->checkPropertiesAndValues($props, array(
				'NormalProperty'    => array('NormalPage', 'ProtectedPage'),
				'ProtectedProperty' => array('NormalPage', 'ProtectedPage')
				), "doTestGetSemanticData-1, NormalUser");
		} else {
			$this->checkPropertiesAndValues($props, array(), 
				"doTestGetSemanticData-1, RestrictedUser");
		}
		
		// Some properties in "PageWithProtectedProperties" are protected
		$title = Title::newFromText("PageWithProtectedProperties");
		$props = $store->getSemanticData(SMWWikiPageValue::makePageFromTitle($title));

		if ($normalUser) {
			$this->checkPropertiesAndValues($props, array(
				'NormalProperty'    => array('NormalPage', 'ProtectedPage'),
				'ProtectedProperty' => array('NormalPage', 'ProtectedPage')
				), "doTestGetSemanticData-2, NormalUser");
		} else {
			$this->checkPropertiesAndValues($props, array(
				'NormalProperty'    => array('NormalPage')
				), "doTestGetSemanticData-2, RestrictedUser");
		}
		
		// There are no properties in "NormalPage"
		$title = Title::newFromText("NormalPage");
		$props = $store->getSemanticData(SMWWikiPageValue::makePageFromTitle($title));

		$this->checkPropertiesAndValues($props, array(), "doTestGetSemanticData-3, any user");
		
	}
	
	/**
	 * Performs the actual tests of the method HACLSMWStore::getPropertyValues().
	 * 
	 * @param array $testConfig
	 * 		A test configuration with the keys 'user', 'page', 'property', 'values'
	 */
	function doTestGetPropertyValues($testConfig) {
		global $wgUser;
		
    	$wgUser = User::newFromId(User::idFromName($testConfig['user']));
		
		$store = smwfGetStore();
		$subject = Title::newFromText($testConfig['page']);
		$props = $store->getPropertyValues($subject, 
		                                   SMWPropertyValue::makeUserProperty($testConfig['property']));
		$this->checkPropertyValues($props, $testConfig['values'], 
								   "doTestGetPropertyValues:\n".
								   "\tUser: {$testConfig['user']}\n".
								   "\tPage: {$testConfig['page']}\n".
								   "\tProperty: {$testConfig['property']}\n");
	}
	
	/**
	 * Performs the actual tests of the method HACLSMWStore::getPropertySubjects().
	 * 
	 * @param array $testConfig
	 * 		A test configuration with the keys 'user', 'property', 'values'
	 */
	function doTestGetPropertySubjects($testConfig) {
		global $wgUser;
    	$wgUser = User::newFromId(User::idFromName($testConfig['user']));
		
		$store = smwfGetStore();
		$subjects = $store->getPropertySubjects(SMWPropertyValue::makeProperty($testConfig['property']), null);
		
		$subjectNames = array();
		foreach ($subjects as $s) {
			$subjectNames[] = $s->getTitle()->getText();
		}
		$expectedSubjects = $testConfig['values'];
		
		$errMsg = "doTestGetPropertySubjects:\n".
				   "\tUser: {$testConfig['user']}\n".
				   "\tProperty: {$testConfig['property']}\n";
		
		$this->assertEquals(count($subjectNames), count($expectedSubjects), $errMsg);
		foreach ($expectedSubjects as $s) {
			$this->assertContains($s, $subjectNames, $errMsg);
		}
		
	}
	
	/**
	 * Performs the actual tests of the method HACLSMWStore::getProperties().
	 * 
	 * @param array $testConfig
	 * 		A test configuration with the keys 'user', 'page', 'values'
	 */
	function doTestGetProperties($testConfig) {
		global $wgUser;
    	$wgUser = User::newFromId(User::idFromName($testConfig['user']));
		
		$store = smwfGetStore();
		$props = $store->getProperties(Title::newFromText($testConfig['page']), null);
		
		$propNames = array();
		foreach ($props as $p) {
			if ($p->isShown()) {
				$propNames[] = $p->getWikiPageValue()->getTitle()->getText();
			}
		}
		$expectedProps = $testConfig['values'];
		
		$errMsg = "doTestGetProperties:\n".
				   "\tUser: {$testConfig['user']}\n".
				   "\tSubject: {$testConfig['page']}\n";
		
		$this->assertEquals(count($propNames), count($expectedProps), $errMsg);
		foreach ($expectedProps as $p) {
			$this->assertContains($p, $propNames, $errMsg);
		}
		
	}
	
	/**
	 * Performs the actual tests of the method HACLSMWStore::getInProperties().
	 * 
	 * @param array $testConfig
	 * 		A test configuration with the keys 'user', 'object', 'values'
	 */
	function doTestGetInProperties($testConfig) {
		global $wgUser;
    	$wgUser = User::newFromId(User::idFromName($testConfig['user']));
		
		$store = smwfGetStore();
		
		$t = Title::newFromText($testConfig['object']);
		$obj = SMWWikiPageValue::makePageFromTitle($t);
		
		$props = $store->getInProperties($obj);
		
		$propNames = array();
		foreach ($props as $p) {
			if ($p->isShown()) {
				$propNames[] = $p->getWikiPageValue()->getTitle()->getText();
			}
		}
		$expectedProps = $testConfig['values'];
		
		$errMsg = "doTestGetInProperties:\n".
				   "\tUser: {$testConfig['user']}\n".
				   "\tObject: {$testConfig['object']}\n";
		
		$this->assertEquals(count($propNames), count($expectedProps), $errMsg);
		foreach ($expectedProps as $p) {
			$this->assertContains($p, $propNames, $errMsg);
		}
		
	}
	
	
	/**
	 * Performs the actual tests of the method HACLSMWStore::getPropertiesSpecial().
	 * 
	 * @param array $testConfig
	 * 		A test configuration with the keys 'user', 'mustContain', 'mustNotContain'
	 */
	function doTestGetPropertiesSpecial($testConfig) {
		global $wgUser;
		
		$store = smwfGetStore();
		
		// Get all used properties as NormalUser
    	$wgUser = User::newFromId(User::idFromName($testConfig['user']));
    	$propUsage = $store->getPropertiesSpecial();
    	
    	$propNames = array();
    	foreach ($propUsage as $propAndCount) {
    		$prop = $propAndCount[0];
    		if ($prop->isVisible()) {
    			$propNames[] = $prop->getText();
    		}
    	}
    	
		$errMsg = "doTestGetPropertiesSpecial:\n".
				   "\tUser: {$testConfig['user']}\n";
		
		$expectedProps = $testConfig['mustContain'];
		foreach ($expectedProps as $p) {
			$this->assertContains($p, $propNames, $errMsg);
		}
    	
		$unexpectedProps = $testConfig['mustNotContain'];
		foreach ($unexpectedProps as $p) {
			$this->assertNotContains($p, $propNames, $errMsg);
		}
    	
	}
	
	
	/**
	 * Performs the actual tests of the method HACLSMWStore::getQueryResult().
	 * 
	 * @param array $testConfig
	 * 		A test configuration with the keys 
	 * 		'user', 'query', 'format', 'printouts', 'result'
	 */
	function doTestSPARQLGetQueryResult($testConfig) {
				
		global $wgUser;
		
		$store = smwfGetStore();
		
		// Get all used properties as NormalUser
    	$wgUser = User::newFromId(User::idFromName($testConfig['user']));
    	
		$queryString = $testConfig['query'];
		$params = array("format" => $testConfig['format'], 
		                "merge" => "false", 
		                "mainlabel" => "subject");
		$printOuts = array();
		foreach ($testConfig['printouts'] as $po) {
			$printOuts[] = new SMWPrintRequest(SMWPrintRequest::PRINT_PROP, $po, 
								SMWPropertyValue::makeUserProperty($po)); 
		}
		$queryobj = SMWSPARQLQueryProcessor::createQuery(
						$queryString, $params, SMWQueryProcessor::INLINE_QUERY, 
						$params['format'], $printOuts);
		
		$res = smwfGetStore()->getQueryResult($queryobj);

		$errMsg =	"doTestSPARQLGetPropertiesSpecial:\n".
					"\tUser: {$testConfig['user']}\n".
					"\tQuery: {$testConfig['query']}\n";
    	
		
		if ($testConfig['format'] == 'count') {
			$this->assertEquals($testConfig['result'], $res);
			return;	
		}
		
		// Convert the result in an array that is easier to compare with the
		// expected result.
		$result = array();
		while ( $row = $res->getNext() ) {
			$rowContent = array();
			foreach ($row as $cell) {
				
				$pr = $cell->getPrintRequest();
				$prLabel = $pr->getLabel();
				if (!array_key_exists($prLabel, $result)) {
					$rowContent[$prLabel] = array();
				}
				
				$cont = array();
				while ($content = $cell->getNextText(SMW_OUTPUT_WIKI)) {
					$cont[] = $content;
				}
				$rowContent[$prLabel] = $cont;
			}
			$result[] = $rowContent;
		}
		
		$msg = "\nActual result:\n".print_r($result, true).
		       "\nExpected result:\n".print_r($testConfig['result'], true);
		
		// Compare the actual and expected result
		$expRows = $testConfig['result'];
		$this->assertEquals(count($expRows), count($result), $errMsg.$msg);
		foreach ($expRows as $expRow) {
			// find the expected row in the result
			$tempResult = $result;
			foreach ($expRow as $var => $value) {
				$tempResult = $this->findRowsWithExpectedValues($tempResult, $var, $value);
			}
			$this->assertEquals(count($tempResult), 1, $errMsg.$msg); 
		}
		
	}

	/**
	 * Performs the actual tests of the method HACLSMWStore::getQueryResult().
	 * 
	 * @param array $testConfig
	 * 		A test configuration with the keys 
	 * 		'user', 'query', 'format', 'printouts', 'result'
	 */
	function doTestGetQueryResult($testConfig) {
		global $wgUser;
		
		$store = smwfGetStore();
		
		// Get all used properties as NormalUser
    	$wgUser = User::newFromId(User::idFromName($testConfig['user']));
    	
		$queryString = $testConfig['query'];
		$params = array("format" => $testConfig['format']);
		$printOuts = array();
		foreach ($testConfig['printouts'] as $po) {
			$printOuts[] = new SMWPrintRequest(SMWPrintRequest::PRINT_PROP, $po, 
								SMWPropertyValue::makeUserProperty($po)); 
		}
		$queryobj = SMWQueryProcessor::createQuery($queryString, $params, 
													SMWQueryProcessor::INLINE_QUERY , 
													$params['format'],
													$printOuts);
		$res = smwfGetStore()->getQueryResult($queryobj);

		$errMsg = "doTestGetPropertiesSpecial:\n".
				   "\tUser: {$testConfig['user']}\n";
    	
		
		if ($testConfig['format'] == 'count') {
			$this->assertEquals($testConfig['result'], $res);
			return;	
		}
		
		$result = array();
		while ( $row = $res->getNext() ) {
			foreach ($row as $cell) {
				$subject = $cell->getResultSubject()->getText();
				if (!array_key_exists($subject, $result)) {
					$result[$subject] = array();
				}
				
				$pr = $cell->getPrintRequest();
				$prLabel = $pr->getLabel();
				if (empty($prLabel)) {
					// ignore the subject
					continue;
				}
				
				$cont = array();
				while ($content = $cell->getNextText(SMW_OUTPUT_WIKI)) {
					$cont[] = $content;
				}
				$result[$subject][$prLabel] = $cont;
			}
		}
		
		$expRows = $testConfig['result'];
		$this->assertEquals(count($result), count($expRows), $errMsg);
		foreach ($expRows as $subject => $expRow) {
			$this->assertArrayHasKey($subject, $result, $errMsg);
			$row = $result[$subject];
			
			$this->assertEquals(count($row), count($expRow), $errMsg);
			foreach ($expRow as $column => $expContent) {
				$this->assertArrayHasKey($column, $row, $errMsg);
				$content = $row[$column];
				$this->assertEquals(count($content), count($expContent), $errMsg);
				
				foreach ($expContent as $item) {
					$this->assertContains($item, $content, $errMsg);
				}
			}
		}
		
	}
	
	/**
	 * Performs the actual tests for checking if properties are hidden in rendered
	 * articles.
	 * 
	 * @param array $testConfig
	 * 		A test configuration with the keys 
	 * 		'user', 'wikitexts', 'values'
	 */
	/**
	 * @param unknown_type $testConfig
	 */
	/**
	 * @param unknown_type $testConfig
	 */
	public function doTestPropertiesInPage($testConfig) {
		global $wgUser;
		
		// Get all used properties as NormalUser
    	$wgUser = User::newFromId(User::idFromName($testConfig['user']));

    	
		$popts = new ParserOptions();
		$parser = new Parser();
		$title = Title::newFromText("NormalPage");
    	foreach ($testConfig['wikitexts'] as $k => $wikiText) {
			$po = $parser->parse($wikiText, $title, $popts );
			$html = $po->getText();
			
			$expValue = $testConfig['values'][$k];
			if (!is_array($expValue)) {
				$expValue = array($expValue);
			}

			foreach ($expValue as $ev) {
				$errMsg = "doTestPropertiesInPage:\n".
					"\tUser: {$testConfig['user']}\n".
					"\tParsing: $wikiText\n".
					"\tExpected: $ev\n".
					"\tActual HTML: $html\n";
				$this->assertGreaterThanOrEqual(0, strpos($html, $ev), $errMsg);
			}
 
    	}
	}
	
	/**
	 * Returns all property names in a SMW_SemanticData object.
	 * @param $semData
	 */
	private function getPropertyNames($semData) {
		$propNames = array();
		foreach($semData->getProperties() as $p) {
			if ($p->isShown()) {
				$wpv = $p->getWikiPageValue();
				$propNames[] = $wpv->getTitle()->getText();
			}
		}
		return $propNames;
		
	}
	
	
	/**
	 * Checks if the semantic data object contains the expected properties and
	 * values and no other.
	 * 
	 * @param SMWSemanticData $semData
	 * @param array(string => array(string)) $expected
	 * 		Map from expected property names to their expected values.
	 * @param string $testDescr
	 * 		A short description of the test
	 */
	private function checkPropertiesAndValues($semData, array $expected, $testDescr) {
		$propNames = $this->getPropertyNames($semData);
		$this->assertEquals(count($propNames), count($expected), "*** Assertion failed for $testDescr ***");
		
		foreach ($expected as $expProp => $expValues) {
			$this->assertContains($expProp, $propNames, "*** Assertion failed for $testDescr ***");
			
			$vals = $semData->getPropertyValues(SMWPropertyValue::makeUserProperty($expProp));
			$this->assertEquals(count($vals), count($expValues), "*** Assertion failed for $testDescr ***");
			
			// The values are of type SMWWikiPageValue => get their names
			$stringVals = array();
			foreach ($vals as $value) {
				$stringVals[] = $value->getText();
			}
			foreach ($expValues as $expVal) {
				$this->assertContains($expVal, $stringVals, "*** Assertion failed for $testDescr ***");
			}
			
		}
		
	}
	
	/**
	 * Checks if the expected property values are available.
	 * 
	 * @param array<SMWDataValue> $values
	 * 		The actual values.
	 * @param array<string> $expectedValues
	 * 		The expected values
	 * @param $testDescr
	 * 		Short description of the test context
	 */
	private function checkPropertyValues(array $values, array $expectedValues, $testDescr) {
		
		$this->assertEquals(count($values), count($expectedValues), "*** Assertion failed for $testDescr ***");
		
		$strValues = array();
		foreach ($values as $v) {
			$strValues[] = $v->getWikiValue();
		}
		foreach ($expectedValues as $v) {
			$this->assertContains($v, $strValues, "*** Expected value missing in $testDescr ***");
		}
		
	}
	
	private function findRowsWithExpectedValues($rows, $var, $value) {
		foreach ($rows as $k => $row) {
			$rowValues = $row[$var];
			if (count($rowValues) == 0 && $value == null) {
				continue;
			}
			if (!$rowValues) {
				unset($rows[$k]);
				continue;
			}
			if (count($rowValues) != 1) {
				// The can not be more than one value in a cell as the query
				// parameter merge is false.
				unset($rows[$k]);
				continue;
			}
			if ($rowValues[0] != $value) {
				unset($rows[$k]);
				continue;
			}
		}
		return $rows;
	}
}
